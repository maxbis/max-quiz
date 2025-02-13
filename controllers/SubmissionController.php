<?php

namespace app\controllers;

use app\models\Submission;
use app\models\SubmissionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;
use Yii;
use yii\web\Cookie;

use app\models\Quiz;
use yii\helpers\ArrayHelper;

/**
 * SubmissionController implements the CRUD actions for Submission model.
 */
class SubmissionController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                // VerbFilter
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
                // Access Control Filter (ACF)
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'actions' => ['create', 'start', 'restart'],
                            'allow' => true,
                            'roles' => ['?'], // '?' represents guest users
                        ],
                        [
                            'allow' => true,
                            'roles' => ['@'], // '@' represents authenticated users
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Submission models.
     *
     * @return string
     */
    public function actionIndex($quiz_id = null)
    {
        $searchModel = new SubmissionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $quiz_id);

        $quizName = 'All Quizes';
        if ($quiz_id !== null) {
            $quiz = Quiz::findOne($quiz_id);
            if ($quiz !== null) {
                $quizName = $quiz->name;
                $quizActive = $quiz->active;
            } else {
                $quizName = "";
                $quizActive = false;
            }
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'quizName' => $quizName,
            'quizActive' => $quizActive,
        ]);
    }

    /**
     * Displays a single Submission model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Submission model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {

        $model = new Submission();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        $this->layout = false;
        return $this->render('start', [
            'model' => $model,
        ]);
    }

    function generateRandomToken($length = 32)
    {
        // Generate a random string of bytes
        $randomBytes = random_bytes($length);

        // Convert the binary data into hexadecimal representation
        $token = bin2hex($randomBytes);

        return $token;
    }

    public function actionStart() // start a new quiz
    {
        usleep(500000); // wait 0.5 seconds to prevent (re)post-DOS-attack
        $request = Yii::$app->request;
        $user_agent = Yii::$app->request->userAgent;
        $user_agent = substr($user_agent, 0, 200); // make sure $user_agent is no longer than 200 chars

        if ($request->isPost) {
            $first_name = ucfirst($request->post('first_name'));
            $last_name = ucfirst($request->post('last_name'));
            $class = $request->post('class');
            $password = $request->post('password');
        } else {
            return $this->redirect(['/submission/create']);
        }

        if (strlen($first_name) < 2 || strlen($last_name) < 2) {
            return $this->redirect(Yii::$app->request->referrer);
        }

        if (strtolower($user_agent) == "max-quiz" && $password == "") { // if user_agent is quiz client (max-quiz) and there''s only one quiz active, start that quiz.
            $sql = "select * from quiz where active = 1";
            $quiz = Yii::$app->db->createCommand($sql)->queryAll();
            if (count($quiz) != 1) {
                return $this->redirect(Yii::$app->request->referrer);
            }
            $password = $quiz[0]['password'];
        }

        $sql = "select * from quiz where password='$password' and active = 1"; // password is same as quiz code
        $quiz = Yii::$app->db->createCommand($sql)->queryOne();
        if (!$quiz) {
            return $this->redirect(Yii::$app->request->referrer);
        }

        if ($quiz['ip_check']) {
            MyHelpers::CheckIP();
        }

        $quiz_name = $quiz['name'];
        $quiz_id = $quiz['id'];
        $token = $this->generateRandomToken();

        // Get all questions connected, shuffle and create space seprated string
        $sql = "select qq.question_id 
                from quizquestion qq 
                join question q on qq.question_id = q.id 
                where qq.quiz_id = $quiz_id and qq.active = 1 
                order by qq.id DESC";
        $result = Yii::$app->db->createCommand($sql)->queryAll();
        $questionIds = array_column($result, 'question_id');

        // if no questions, quiz cannot be started
        if (count($questionIds) == 0) {
            return $this->redirect(Yii::$app->request->referrer);
        }

        if ($quiz['random']==1) {
            shuffle($questionIds);
        }
        if ($quiz['no_questions']) { // if quiz had question number limit, take first only
            $limitArrayToN = fn(array $array, int $N) => ($N >= count($array)) ? $array : array_slice($array, 0, $N);
            $questionIds = $limitArrayToN($questionIds, $quiz['no_questions']);
        }
        $no_questions = count($questionIds);
        $question_order = implode(" ", $questionIds); // serialize questions (questin ids seperated by spaces)

        $ip_address = Yii::$app->request->userIP;

        $sql = "insert into submission (token, first_name, last_name, class, question_order, no_questions, no_answered, no_correct, quiz_id, ip_address, user_agent, answer_order)
                values ('$token', '$first_name', '$last_name', '$class', '$question_order', $no_questions, 0, 0, $quiz_id, '$ip_address', '$user_agent', '')";
        $result = Yii::$app->db->createCommand($sql)->execute();

        $cookie = new Cookie([
            'name' => 'token',
            'value' => $token,
            'expire' => time() + 3600 * 6, // 6 hours
        ]);
        Yii::$app->response->cookies->add($cookie);

        writeLog($msg = "Quiz Started for $first_name $last_name $class with token: $token");

        // redirect to question page
        return $this->redirect(['site/question']);

    }

    public function actionRestart($token)
    {
        $cookie = new Cookie([
            'name' => 'token',
            'value' => $token,
            'expire' => time() + 3600 * 6, // 6 hours
        ]);
        Yii::$app->response->cookies->add($cookie);

        return $this->redirect(['site/question']);
    }


    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            $returnUrl = Yii::$app->user->returnUrl ?: ['update', 'id' => $model->id]; // if not saved go to default      
            return $this->redirect($returnUrl);
            // return $this->redirect(['update', 'id' => $model->id]);
        }

        Yii::$app->user->returnUrl = Yii::$app->request->referrer;

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Submission model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => true];
        }

        return $this->redirect(['index']);
    }

    public function actionDeleteUnfinished($quiz_id)
    {
        $sql = "DELETE FROM submission
                WHERE last_updated < NOW() - INTERVAL 2 HOUR
                and (finished is null or finished = 0)
                and quiz_id = $quiz_id";
        Yii::$app->db->createCommand($sql)->execute();

        return $this->redirect(['/submission', 'quiz_id' => $quiz_id]);
    }

    /**
     * Finds the Submission model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Submission the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Submission::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionMonitor()
    {
        $sql = "select id from quiz where active = 1";
        $quiz = Yii::$app->db->createCommand($sql)->queryOne();


        $sql = "select * from submission where quiz_id = ${quiz['id']} order by first_name, last_name ASC";
        $submissions = Yii::$app->db->createCommand($sql)->queryAll();

        return $this->render('monitor', [
            'submissions' => $submissions,
        ]);
    }

    public function actionExport($quiz_id)
    {
        $sql = "select q.name, student_nr, first_name, last_name, class,
                CASE 
                    WHEN no_correct = 0 THEN 0
                    ELSE ROUND((no_correct / CAST(s.no_questions AS DECIMAL)) * 100, 0)
                END AS score,
                s.no_questions, no_answered, no_correct,
                start_time, end_time, TIMESTAMPDIFF(minute, start_time, end_time) duration,
                question_order, answer_order
                from submission s
                join quiz q on q.id = s.quiz_id
                where q.id = $quiz_id";
        $submissions = Yii::$app->db->createCommand($sql)->queryAll();

        $sql = "select q.id, q.correct from question q
                join quizquestion qq on qq.question_id = q.id and qq.active=1 and qq.quiz_id = $quiz_id";
        $correctAnswers = Yii::$app->db->createCommand($sql)->queryAll();
        $correctAnswersIndexed = ArrayHelper::map($correctAnswers, 'id', 'correct');
        ksort($correctAnswersIndexed);

        // _d($correctAnswersIndexed);

        // merge the two (serialized) list to one ass. array (f.e. 252 253 312 and 1 2 1 will become ['252'=>'1','253'=>'2','312'=>'1'])
        foreach ($submissions as &$submission) {
            $resultArray = [];
            $ids = $this->list2Array($submission['question_order']);
            $numbers = $this->list2Array($submission['answer_order']);
            foreach ($ids as $index => $id) {
                $number = isset($numbers[$index]) ? $numbers[$index] : 0;
                if (isset($correctAnswersIndexed[$id])) {
                    if ($correctAnswersIndexed[$id] == $number) {
                        $resultArray[$id] = [-1, (int) $number, 1];
                    } else {
                        $resultArray[$id] = [-1, (int) $number, 0];
                    }
                } else {
                    $resultArray[$id] = [-1, (int) $number, ''];
                }
            }

            ksort($resultArray);

            // _d($resultArray);

            $counter = 1; // Initialize the counter
            foreach ($resultArray as $key => &$subArray) {
                // array_unshift($subArray, $counter); // Add the counter at the beginning of each sub-array
                $subArray[0] = $counter;
                $counter++; // Increment the counter for the next sub-array
            }
            unset($subArray); // Break the reference with the last element

            $submission['questions-answers'] = $resultArray;
        }

        // _dd($submissions);

        if ($submissions)
            $columns = [
                'Cursus' => 'name',
                'Student_nr' => 'student_nr',
                'Student' => ['first_name', 'last_name'], // Concatenate first_name and last_name
                'Klas' => 'class',
                'Score' => 'score',
                'Aantal Vragen' => 'no_questions',
                'Aantal Antwoorden' => 'no_answered',
                'Aantal Correct' => 'no_correct',
                'Start Tijd' => 'start_time',
                'Eind Tijd' => 'end_time',
                'Aantal minuten' => 'duration'
            ];
            return $this->exportExcel($submissions, $columns);
    }

    // WIP: stats over this quiz
    public function actionExportStats($quiz_id)
    {
        $sql = "
            SELECT
                CAST(timestamp AS DATE) datum, 
                question_id question_id,
                q.question question,
                sum(1) aantal,
                sum(l.correct) correct,
                ROUND(SUM(l.correct) * 100 / SUM(1), 1) AS perc
            FROM `log`l
            join question q on q.id = l.question_id
            WHERE quiz_id=$quiz_id
            group by 1,2,3
            order by 6 desc
        ";
        $submissions = Yii::$app->db->createCommand($sql)->queryAll();

        if ($submissions)
            $columns = [
                'Datum' => 'datum',
                'Vraag ID' => 'question_id',
                'Vraag' => 'question',
                'Aantal' => 'aantal',
                'Correct' => 'correct',
                'Percentag Correct' => 'perc',
            ];
            return $this->exportExcel($submissions, $columns);

    }


    private function exportExcel($data, $columns)
    {
        header('Content-type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="max-quiz-export' . date('YmdHi') . '.csv"');
        header("Pragma: no-cache");
        header("Expires: 0");
        header('Content-Transfer-Encoding: binary');
        echo "\xEF\xBB\xBF";

        $output = "";

        $separator = ";"; // NL version, use ',' for EN

        // Output the headers
        foreach ($columns as $header => $field) {
            $output .= $header . $separator;
        }
        $output .= "\n";

        // Output the data
        foreach ($data as $line) {
            foreach ($columns as $field) {
                if (is_array($field)) {
                    // Concatenate multiple fields (e.g., first_name and last_name)
                    $value = '';
                    foreach ($field as $subfield) {
                        $value .= isset($line[$subfield]) ? $line[$subfield] . ' ' : '';
                    }
                    $output .= trim($value) . $separator;
                } else {
                    $value = isset($line[$field]) ? $line[$field] : '';
                    $value = preg_replace('/[\r\n]+/', ' ', $value); // Replace newlines with spaces
                    $value = str_replace(";", " ", $value);
                    $output .= $value . $separator;
                }
            }
            // If there's nested data like 'questions-answers', handle it here
            if (isset($line['questions-answers']) && is_array($line['questions-answers'])) {
                foreach ($line['questions-answers'] as $key => $value) {
                    $output .= $value[0] . '-' . $key . $separator . $value[1] . $separator . $value[2] . $separator;
                }
            }
            $output .= "\n";
        }

        return $output;
    }

    private function exportExcelOldWeg($data)
    {
        header('Content-type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="max-quiz-export' . date('YmdHi') . '.csv"');
        header("Pragma: no-cache");
        header("Expires: 0");
        header('Content-Transfer-Encoding: binary');
        echo "\xEF\xBB\xBF";

        $output = "";

        $seperator = ";"; // NL version, use , for EN

        $output .= 'Cursus' . $seperator;
        $output .= 'Student' . $seperator;
        $output .= 'Klas' . $seperator;
        $output .= 'Score' . $seperator;
        $output .= 'Aantal Vraegn' . $seperator;
        $output .= 'Aantal Antwoorden' . $seperator;
        $output .= 'Aantal Correct' . $seperator;
        $output .= 'Start Tijd' . $seperator;
        $output .= 'Eind Tijd' . $seperator;
        $output .= 'Aantal minuten' . $seperator;
        // foreach ( $data[0]['questions-answers'] as  $index => $value ) {
        //     $output .= $index.$seperator.'Goed'.$seperator;
        // }
        $output .= "\n";

        foreach ($data as $line) {
            // foreach ($line as $key => $value) {
            //     $output .= preg_replace('/[\s+,;]/', ' ', $value) . $seperator;
            //     // echo "\"" . $value . "\"" . $seperator;
            // }
            $output .= $line['name'] . $seperator;
            $output .= $line['first_name'] . ' ' . $line['last_name'] . $seperator;
            $output .= $line['class'] . $seperator;
            $output .= $line['score'] . $seperator;
            $output .= $line['no_questions'] . $seperator;
            $output .= $line['no_answered'] . $seperator;
            $output .= $line['no_correct'] . $seperator;
            $output .= $line['start_time'] . $seperator;
            $output .= $line['end_time'] . $seperator;
            $output .= $line['duration'] . $seperator;
            foreach ($line['questions-answers'] as $key => $value) {
                $output .= $value[0] . '-' . $key . $seperator . $value[1] . $seperator . $value[2] . $seperator;
            }
            $output .= "\n";
        }

        // _dd($output);
        return $output;
    }

    // used in export function to convert question_order and answer_order to array
    // and remove space from front of each item in array (if any)
    private function list2Array($list)
    {
        if (substr($list, 0, 1) === ' ') {
            $list = ltrim($list, ' ');
        }

        return explode(" ", $list);
    }

}
