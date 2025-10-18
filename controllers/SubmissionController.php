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
        $delay = random_int(200_000, 30_00_000); // wait 0.2-3.0 seconds to prevent (re)post-DOS-attack and avoi overloading
        usleep($delay);

        $request = Yii::$app->request;
        $user_agent = Yii::$app->request->userAgent;
        $user_agent = substr($user_agent, 0, 200); // make sure $user_agent is no longer than 200 chars

        if ($request->isPost) {
            $first_name = ucfirst($request->post('first_name'));
            $last_name = ucfirst($request->post('last_name'));
            $student_nr = $request->post('student_nr');
            $class = $request->post('class');
            $password = $request->post('password');
        } else {
            return $this->redirect(['/submission/create']);
        }

        if (strtolower($user_agent) == "max-quiz" && $password == "") {
            $sql = "select * from quiz where active = 1";
            $quiz = Yii::$app->db->createCommand($sql)->queryAll();
            if (count($quiz) != 1) {
                return $this->redirect(Yii::$app->request->referrer);
            }
            $password = $quiz[0]['password'];
        }

        $sql = "select * from quiz where password='$password' and active = 1";
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

        $sql = "select qq.question_id 
                from quizquestion qq 
                join question q on qq.question_id = q.id 
                where qq.quiz_id = $quiz_id and qq.active = 1 
                order by COALESCE(qq.order, 0) ASC, q.id ASC";
        $result = Yii::$app->db->createCommand($sql)->queryAll();
        $questionIds = array_column($result, 'question_id');

        if (count($questionIds) == 0) {
            return $this->redirect(Yii::$app->request->referrer);
        }

        if ($quiz['random']==1) {
            shuffle($questionIds);
        }
        if ($quiz['no_questions']) {
            $limitArrayToN = fn(array $array, int $N) => ($N >= count($array)) ? $array : array_slice($array, 0, $N);
            $questionIds = $limitArrayToN($questionIds, $quiz['no_questions']);
        }
        $no_questions = count($questionIds);
        $question_order = implode(" ", $questionIds);

        $ip_address = Yii::$app->request->userIP;

        $sql = "insert into submission (token, first_name, last_name, student_nr, class, question_order, no_questions, no_answered, no_correct, quiz_id, ip_address, user_agent, answer_order)
                values ('$token', '$first_name', '$last_name', '$student_nr', '$class', '$question_order', $no_questions, 0, 0, $quiz_id, '$ip_address', '$user_agent', '')";
        $result = Yii::$app->db->createCommand($sql)->execute();

        $cookie = new Cookie([
            'name' => 'token',
            'value' => $token,
            'expire' => time() + 3600 * 6, // 6 hours
        ]);
        Yii::$app->response->cookies->add($cookie);

        writeLog($msg = "Quiz Started for $first_name $last_name $class with token: $token");

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
            $returnUrl = Yii::$app->user->returnUrl ?: ['update', 'id' => $model->id];
            return $this->redirect($returnUrl);
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
            return $this->asJson(['success' => true]);
        }

        return $this->redirect(['index']);
    }

    public function actionDeleteUnfinished($quiz_id)
    {
        // Only allow POST requests for security
        if (!Yii::$app->request->isPost) {
            throw new \yii\web\MethodNotAllowedHttpException('Only POST requests are allowed.');
        }

        $sql = "DELETE FROM submission
                WHERE last_updated < NOW() - INTERVAL 2 HOUR
                and (finished is null or finished = 0)
                and quiz_id = $quiz_id";
        $deletedCount = Yii::$app->db->createCommand($sql)->execute();

        // Set a flash message to show the result
        Yii::$app->session->setFlash('success', "Cleaned $deletedCount unfinished submissions.");

        // Handle AJAX requests differently
        if (Yii::$app->request->isAjax) {
            return $this->asJson(['success' => true, 'deletedCount' => $deletedCount]);
        }

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

    public function actionExport($quiz_id, $filename = null)
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

            $counter = 1;
            foreach ($resultArray as $key => &$subArray) {
                $subArray[0] = $counter;
                $counter++;
            }
            unset($subArray);

            $submission['questions-answers'] = $resultArray;
        }

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
            return $this->exportExcel($submissions, $columns, $filename);
    }

    // WIP: stats over this quiz
    public function actionExportStats($quiz_id, $filename = null)
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
            return $this->exportExcel($submissions, $columns, $filename);

    }


    private function exportExcel($data, $columns, $filename = null)
    {
        // Generate default filename if none provided
        if ($filename === null) {
            $filename = 'max-quiz-export' . date('YmdHi');
        }
        
        // Sanitize filename (remove invalid characters)
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        
        header('Content-type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
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
            if (isset($line['questions-answers']) && is_array($line['questions-answers'])) {
                foreach ($line['questions-answers'] as $key => $value) {
                    $output .= $value[0] . '-' . $key . $separator . $value[1] . $separator . $value[2] . $separator;
                }
            }
            $output .= "\n";
        }

        return $output;
    }

    private function list2Array($list)
    {
        if (substr($list, 0, 1) === ' ') {
            $list = ltrim($list, ' ');
        }

        return explode(" ", $list);
    }

}
