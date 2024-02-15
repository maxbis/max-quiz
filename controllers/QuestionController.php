<?php

namespace app\controllers;

use app\models\Question;
use app\models\QuestionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\helpers\ArrayHelper;;

use yii\filters\AccessControl;

use Yii;
use app\models\Quizquestion;


/**
 * QuestionController implements the CRUD actions for Question model!
 */
class QuestionController extends Controller
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
                            'allow' => true,
                            'roles' => ['@'], // '@' represents authenticated users
                        ],
                        // You can add more rules here
                    ],
                ],
            ]
        );
    }

    public function actionIndexRaw()
    {
        $searchModel = new QuestionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('indexraw', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Question models.
     *
     * @return string
     */
    public function actionIndex($quiz_id = 0, $show = 1)
    {

        if ($quiz_id == 0) {
            $sql = "SELECT max(id) id FROM quiz WHERE active = 1";
            $quiz_id = Yii::$app->db->createCommand($sql)->queryOne()['id'];
            if ($quiz_id == "") {
                $sql = "SELECT max(id) id FROM quiz";
                $quiz_id = Yii::$app->db->createCommand($sql)->queryOne()['id'];
                if ($quiz_id == "") {
                    return $this->redirect(['quiz']);
                }
            }
        }

        $searchModel = new QuestionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $quiz_id, $show);

        $keysShown = $dataProvider->getKeys();

        $sql = "SELECT question_id FROM quizquestion WHERE quiz_id = $quiz_id AND active = 1";
        $quizQuestions = Yii::$app->db->createCommand($sql)->queryAll();
        $questionIds = ArrayHelper::getColumn($quizQuestions, 'question_id');

        $sql = "SELECT * FROM quiz WHERE id = $quiz_id";
        $quiz = Yii::$app->db->createCommand($sql)->queryOne();

        Yii::$app->session->set('selectedQuestionIds', $questionIds);
        // Yii::$app->user->returnUrl = Yii::$app->request->referrer;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'questionIds' => $questionIds,
            'quiz_id' => $quiz_id,
            'quiz' => $quiz,
            'show' => $show,
            'keysShown' => $keysShown,
        ]);
    }

    /**
     * Displays a single Question model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewOld($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionView($id)
    {
        $submission = [
            'id' => 0, 'token' => '', 'first_name' => '', 'last_name' => '', 'class' => '',
            'quiz_id' => '', 'no_answered' => 0, 'no_questions' => 1
        ];
        $sql = "select * from question where id=" . $id;
        $question = Yii::$app->db->createCommand($sql)->queryOne();

        $returnUrl = Yii::$app->request->referrer;
        if (strpos($returnUrl, 'index') !== false) { // if the referrer is the view itself, back should not refer to the prev view  
            Yii::$app->session->set('viewReturnUrl', $returnUrl);
        }

        if (!$question) {
            return $this->render('/site/error', ['message' => "Question $id does not exist."]);
        }

        $this->layout = false;
        return $this->render('/site/question', ['title' => 'Quiz [expl-adm]', 'question' => $question, 'submission' => $submission]);
    }

    /**
     * Creates a new Question model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($quiz_id = null)
    {
        $model = new Question();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                if ($quiz_id) {
                    $quizquestion = new Quizquestion();
                    $quizquestion->question_id = $model->id;
                    $quizquestion->quiz_id = $quiz_id;
                    $quizquestion->active = 1;
                    $quizquestion->save();
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionCopy($id)
    {
        $model = $this->findModel($id);

        $newModel = new Question();
        $newModel->attributes = $model->attributes;
        $newModel->question = "(Copy)\n" . $model->question;

        if ($newModel->save()) {
            Yii::$app->session->setFlash('success', 'Question copied successfully.');
            return $this->redirect(['update', 'id' => $newModel->primaryKey]);
        } else {
            Yii::$app->session->setFlash('error', 'There was an error copying the question.');
            return $this->redirect(['view', 'id' => $id]);
        }
    }

    /**
     * Updates an existing Question model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $sql = "select q.id, q.name, qq.active from quiz q
            left join quizquestion qq on qq.quiz_id = q.id 
            and qq.active = 1 and qq.question_id=$id";
        $questionLinks = Yii::$app->db->createCommand($sql)->queryAll();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $questionLinks = Yii::$app->request->post('questionLinks', []);
            $updateSql = "";
            foreach ($questionLinks as $quiz_id => $active) {
                if ($active == 'on') {
                    $active = 1;
                }
                $sql = "select count(*) count from quizquestion where question_id=$id and quiz_id=$quiz_id";
                $count = Yii::$app->db->createCommand($sql)->queryOne()['count'];
                if ($count == 0 && $active == 1) { // only insert if relation record does not exists and the question becomes active for this quiz
                    $updateSql .= "insert into quizquestion (question_id, quiz_id, active) values ($id, $quiz_id, $active);\n";
                } elseif ($count == 1) { // if relation record exist, always update
                    $updateSql .= "update quizquestion set active =$active where question_id=$id and quiz_id=$quiz_id;\n";
                }
            }

            Yii::$app->db->createCommand($updateSql)->execute();

            if ($model->save()) {
                Yii::$app->session->setFlash('success', ' Question updated');
            } else {
                Yii::$app->session->setFlash('error', ' Question not updated');
            }

            // get return URL from session
            $returnUrl = Yii::$app->user->returnUrl ?: ['/question/view', 'id' => $id];            
            return $this->redirect($returnUrl);
        }

        // save refererer in session
        Yii::$app->user->returnUrl = Yii::$app->request->referrer."#q".$id;
        // dd(Yii::$app->user->returnUrl);

        return $this->render('update', [
            'model' => $model,
            'questionLinks' => $questionLinks,
        ]);
    }

    /**
     * Deletes an existing Question model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id, $show = null)
    {

        // $sql = "delete from quizquestion where question_id=$id";
        // Yii::$app->db->createCommand($sql)->execute();

        $sql = "SELECT count(*) count FROM quizquestion where active = 1 and question_id = $id";
        $result = Yii::$app->db->createCommand($sql)->queryOne();

        if ($result['count'] > 0) {
            Yii::$app->session->setFlash('error', 'Question cannot be deleted because it is linked to a quiz.');
            return $this->redirect(Yii::$app->request->referrer);
        }

        $this->findModel($id)->delete();

        $sql = "delete FROM quizquestion
                    WHERE question_id not in  (
                    SELECT id
                    FROM question
                )";
        Yii::$app->db->createCommand($sql)->execute();

        Yii::$app->session->setFlash('success', 'Question deleted.');
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Finds the Question model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Question the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Question::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionList($quiz_id, $view = 'list')
    {
        $sql = "select
                q.id id, question question, a1, a2, a3, a4, a5, a6, correct, label
                from question q
                join quizquestion qq on qq.question_id = q.id
                where qq.quiz_id=$quiz_id and qq.active=1
                order by id DESC";
        $questions = Yii::$app->db->createCommand($sql)->queryAll();

        // $sql = "select question_id id, sum(answer_no) answer, sum(correct) correct from log where quiz_id = $quiz_id group by 1";

        $sql = "select l.question_id id, sum(1) answer, sum(l.correct) correct
                from log l
                join submission s on s.id = l.submission_id
                where l.quiz_id = $quiz_id
                group by 1";
        $log = Yii::$app->db->createCommand($sql)->queryAll();

        $logItems = [];

        foreach ($log as $item) {
            $logItems[$item['id']] = [
                'answer' => $item['answer'],
                'correct' => $item['correct']
            ];
        }

        $sql = "select name from quiz where id=$quiz_id";
        $quiz = Yii::$app->db->createCommand($sql)->queryOne();

        return $this->render($view, [
            'questions' => $questions,
            'quiz' => $quiz,
            'logItems' => $logItems,
        ]);
    }

    public function actionImport($quiz_id, $input = "")
    {      
        $sql = "select * from quiz where id=$quiz_id";
        $quiz = Yii::$app->db->createCommand($sql)->queryOne();
        return $this->render('import', ['input' => $input, 'quiz' => $quiz ]);
    }

    private function parseBulkInput($input)
    {
        $lines = explode("\n", $input);

        $questions = [];
        $thisQuestion = [];
        $currentData = "";
        $currentKey = "";
        $answerIndex = 1;

        $trimEmptyLines = function ($text) {
            $trimmedText = preg_replace('/^\h*\v+/m', '', $text); // Remove empty lines from the beginning of the text
            $trimmedText = preg_replace('/\v+\h*$/m', '', $trimmedText); // Remove empty lines from the end of the text
            return $trimmedText;
        };

        foreach ($lines as $line) {
            # $line = chop($line);
            $token = substr($line, 0, 2);
            // _d(['Newline token, line, currentQuestion, curretnData, currentKey',$token, $line, $thisQuestion, $currentData, $currentKey]);
            if ($token == 'QQ') {
                if ($currentKey && $currentData) {
                    $thisQuestion[$currentKey] = $trimEmptyLines($currentData);
                }
                // QQ is the beginning of a new question so save previous question
                if (count($thisQuestion) > 2) { // but only if the curretn question is not empty,, which it will be the first time
                    array_push($questions, $thisQuestion);
                }
                $thisQuestion = [];
                $currentKey = "question";
                $currentData = "";
                $answerIndex = 1;
            } elseif ($token == 'AA' or $token == 'AC') {
                if ($currentKey && $currentData) {
                    $thisQuestion[$currentKey] = $trimEmptyLines($currentData);
                }
                $currentKey = "a" . $answerIndex;
                $currentData = "";
                if ($token == 'AC') {
                    $thisQuestion['correct'] = $answerIndex;
                }
                $answerIndex++;
            } elseif ($token == 'LL') {
                if ($currentKey && $currentData) {
                    $thisQuestion[$currentKey] = $trimEmptyLines($currentData);
                }
                $currentKey = "label";
                $currentData = "";
            } elseif ($token == 'ID') {
                if ($currentKey && $currentData) {
                    $thisQuestion[$currentKey] = $trimEmptyLines($currentData);
                }
                $currentKey = "id";
                $currentData = "";
            } else {
                // remove empty lines if token <> QQ
                if ($currentKey <> "question") {
                    $currentData .= rtrim($line, "\n\r");
                } else {
                    $currentData .= $line;
                }
            }
        }
        if ($currentData) {
            $thisQuestion[$currentKey] = $trimEmptyLines($currentData);;
        }
        array_push($questions, $thisQuestion); // save the last question

        // dd($questions);
        return $questions;
    }

    public function actionBulkImport()
    {
        $no_succes = 0;
        if (Yii::$app->request->isPost) {
            $bulkInput = Yii::$app->request->post('bulkInput');
            $action = Yii::$app->request->post('action', null);
            $quiz_id = Yii::$app->request->post('quiz_id');
            $parsedQuestions = $this->parseBulkInput($bulkInput);
            $label = Yii::$app->request->post('label');

            foreach ($parsedQuestions as $questionData) {
                $no_succes += $this->insertQuestion($questionData, $action, $quiz_id, $label);
            }
        }
        Yii::$app->session->setFlash('success', ' Question(s) imported: ' . $no_succes);

        return $this->redirect(['/quiz/index']);
    }


    private function insertQuestion($questionData, $mode, $quiz_id = null, $label = null)
    {
        $succes = 0;
        $question = null;

        // if mode is update and id give, retrieve existing question
        if ($mode == 'update' && isset($questionData['id'])) {
            $question = Question::findOne($questionData['id']);
        }
        if ($question === null) { // nothing to update
            $questionText = rtrim($questionData['question'], "\r\n");

            // Check for duplicate
            $existingQuestion = Question::find()->where(['like', 'question', $questionText . '%', false])->one();
            if ($existingQuestion !== null) {
                return $succes;
            }
            $question = new Question();
        }

        $question->question = $questionData['question'];
        $question->a1 = $questionData['a1'] ?? '-';
        $question->a2 = $questionData['a2'] ?? '-';
        $question->a3 = $questionData['a3'] ?? null;
        $question->a4 = $questionData['a4'] ?? null;
        $question->a5 = $questionData['a5'] ?? null;
        $question->a6 = $questionData['a6'] ?? null;
        $question->correct = $questionData['correct'] ?? 0;
        if ($label <> "") {
            $question->label = $label;
        } else {
            $question->label =  $questionData['label'] ?? 'Imported';
        }

        if ($question->save()) {
            $succes++;
            if ($quiz_id) {
                // connect question to quiz
                $exists = Quizquestion::findOne(['quiz_id' => $quiz_id, 'question_id' => $question->id]);
                if ($exists === null) {
                    $quizQuestion = new Quizquestion();
                    $quizQuestion->quiz_id = $quiz_id;
                    $quizQuestion->question_id = $question->id;
                    $quizQuestion->active = 1;
                    $quizQuestion->save();
                }
            }
        }
        return $succes;
    }

    public function actionExport()
    {
        $request = Yii::$app->request;
        $quiz_id = $request->get('quiz_id');

        if ($quiz_id == "") {
            $sql = "SELECT max(id) id FROM quiz WHERE active = 1";
            $quiz_id = Yii::$app->db->createCommand($sql)->queryOne()['id'];
            if ($quiz_id == "") {
                $sql = "SELECT max(id) id FROM quiz";
                $quiz_id = Yii::$app->db->createCommand($sql)->queryOne()['id'];
                if ($quiz_id == "") {
                    return $this->redirect(['/question']);
                }
            }
        }


        // ToDO select only quetions for this quiz, join with quizquestion
        // $sql = "select * from question";
        // where qq.quiz_id=$quiz_id and qq.active=1

        $sql = "select
                q.id id, question question, a1, a2, a3, a4, a5, a6, correct, label
                from question q
                join quizquestion qq on qq.question_id = q.id
                where qq.quiz_id=$quiz_id and qq.active=1
                order by id DESC";

        $questions = Yii::$app->db->createCommand($sql)->queryAll();
        $output = "";
        foreach ($questions as $question) {
            $output .= "QQ\n" . $question['question'] . "\n";
            $output .= "ID\n" . $question['id'] . "\n";
            for ($i = 1; $i < 7; $i++) {
                if ($question['correct'] == $i) {
                    $output .= "AC\n" . $question['a' . $i] . "\n";
                } else {
                    $output .= "AA\n" . $question['a' . $i] . "\n";
                }
            }
            $output .= "LL\n" . $question['label'] . "\n";
        }

        return $this->render('export', ['output' => $output]);
    }

    public function actionBulkDelete($quiz_id)
    {
        _dd('Not available, only for testing');
        $sql = "delete from question where id in (
                    select q.id from question q
                    join quizquestion qq on qq.question_id = q.id
                    where qq.active = 1
                    and qq.quiz_id=$quiz_id
                )";
        Yii::$app->db->createCommand($sql)->execute();

        // delete connections from questions that do not exists anymore
        $sql = "delete from quizquestion
                where id not in (
                    select qq.id from question q
                    join quizquestion qq on qq.question_id = q.id
                )";
        Yii::$app->db->createCommand($sql)->execute();

        return $this->redirect(['/quiz']);
    }

    public function actionDeleteMultiple()
    {
        $ids = Yii::$app->request->post('selection');
        if (!empty($ids)) {
            Question::deleteAll(['id' => $ids]);
            Yii::$app->session->setFlash('success', 'The selected items have been deleted.');
        }

        return $this->redirect(['index-raw']);
    }

    public function actionMultipleUpdate($quiz_id)
    {
        // Example: Load multiple models (adjust the query as needed)
        $models =   Question::find()->joinWith('quizquestions')
            ->where(['quizquestion.quiz_id' => $quiz_id, 'quizquestion.active' => 1])
            ->all();

        if (Yii::$app->request->isPost) {
            if (Question::loadMultiple($models, Yii::$app->request->post()) && Question::validateMultiple($models)) {
                foreach ($models as $model) {
                    $model->save(false); // Save without validation as it's already done
                }
                
                $returnUrl = Yii::$app->user->returnUrl ?: ['/question/index', 'quiz_id' => $quiz_id];            
                return $this->redirect($returnUrl);
            }
        }

        Yii::$app->user->returnUrl = Yii::$app->request->referrer;
        return $this->render('multipleUpdate', ['models' => $models]);
    }

}
