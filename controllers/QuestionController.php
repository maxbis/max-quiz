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

/**
 * QuestionController implements the CRUD actions for Question model.
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

    public function actionIndex2()
    {
        $searchModel = new QuestionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index3', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Question models.
     *
     * @return string
     */
    public function actionIndex($quiz_id=0)
    {
        $searchModel = new QuestionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        if ( $quiz_id == 0 ) {
            $sql = "SELECT max(id) id FROM quiz WHERE active = 1";
            $quiz_id = Yii::$app->db->createCommand($sql)->queryOne()['id'];
            if ( $quiz_id == "" ) {
                $sql = "SELECT max(id) id FROM quiz";
                $quiz_id = Yii::$app->db->createCommand($sql)->queryOne()['id'];
                if ( $quiz_id == "" ) {
                    return $this->redirect(['quiz']);
                }
            }
        }

        $sql = "SELECT question_id FROM quizquestion WHERE quiz_id = $quiz_id AND active = 1";
        $quizQuestions = Yii::$app->db->createCommand($sql)->queryAll();
        $questionIds = ArrayHelper::getColumn($quizQuestions, 'question_id');

        $sql = "SELECT * FROM quiz WHERE id = $quiz_id";
        $quiz = Yii::$app->db->createCommand($sql)->queryOne();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'questionIds' => $questionIds,
            'quiz_id' => $quiz_id,
            'quiz' => $quiz,
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
        $submission = ['id' => 0, 'token'=>'', 'first_name' => '', 'last_name' => '', 'class' => '',
                        'quiz_id' => '', 'no_answered' => 0, 'no_questions' => 1 ];
        $sql = "select * from question where id=".$id;
        $question = Yii::$app->db->createCommand($sql)->queryOne();

        return $this->render('/site/question', [ 'title' => 'Question', 'question' => $question, 'submission' => $submission ]);
    }

    /**
     * Creates a new Question model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Question();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
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

        if ($newModel->save()) {
            Yii::$app->session->setFlash('success', 'Question copied successfully.');
            return $this->redirect(['view', 'id' => $newModel->primaryKey]);
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

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['/question/view', 'id' => $id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Question model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
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

    public function actionList($quiz_id) {
        $sql = "select
                q.id id, question question, a1, a2, a3, a4, a5, a6, correct, label
                from question q
                join quizquestion qq on qq.question_id = q.id
                where qq.quiz_id=$quiz_id and qq.active=1";
        $questions = Yii::$app->db->createCommand($sql)->queryAll();

        $sql = "select name from quiz where id=$quiz_id";
        $quiz = Yii::$app->db->createCommand($sql)->queryOne();

        return $this->render('list', [
            'questions' => $questions,
            'quiz' => $quiz,
        ]);
    }

    public function actionImport($input="")
    {
        return $this->render('import', [ 'input' => $input ]);
    }

    private function parseBulkInput($input)
    {
        $input .= "\nQQ\n";
        $lines = explode("\n", $input);

        $questions = [];
        $thisQuestion = [];
        $currentData = "";
        $currentKey = "";
        $answerIndex = 1;

        foreach ($lines as $line) {
            # $line = chop($line);
            $token = strtoupper( substr($line,0,2) );
            if ( $token == 'QQ' ) {
                if ( $currentKey && $currentData ) {
                    $thisQuestion[$currentKey] = $currentData;
                    array_push($questions, $thisQuestion);
                    $thisQuestion = [];
                }
                $currentKey = "question";
                $currentData = "";
                $answerIndex = 1;
            } elseif ( $token == 'AA' or $token == 'AC' ) {
                if ( $currentKey && $currentData ) {
                    $thisQuestion[$currentKey] = $currentData;
                }
                $currentKey = "a".$answerIndex;
                $currentData = "";
                if ( $token == 'AC' ) {
                    $thisQuestion['correct'] = $answerIndex;
                }
                $answerIndex++;
            } elseif ( $token == 'LL' ) {
                if ( $currentKey && $currentData ) {
                    $thisQuestion[$currentKey] = $currentData;
                }
                $currentKey = "label";
                $currentData = "";
            }elseif ( $token == 'ID' ) {
                if ( $currentKey && $currentData ) {
                    $thisQuestion[$currentKey] = $currentData;
                }
                $currentKey = "id";
                $currentData = "";
            } else {
                $currentData .= $line;
            }
        }

        return $questions;
    }

    public function actionBulkImport()
    {
        if (Yii::$app->request->isPost) {
            $bulkInput = Yii::$app->request->post('bulkInput');
            $parsedQuestions = $this->parseBulkInput($bulkInput);

            foreach ($parsedQuestions as $questionData) {
                $this->insertQuestion($questionData);
            }
        }

        return $this->redirect(['/question/index']);
    }

    private function insertQuestion($questionData)
    {
        if ( isset($questionData['id']) ) {
            $sql = "delete from question where id=".$questionData['id'];
            Yii::$app->db->createCommand($sql)->execute();
        }

        $connection = Yii::$app->db;
        $sql = "INSERT INTO question (id, question, a1, a2, a3, a4, a5, a6, correct, label) VALUES (:id, :question, :a1, :a2, :a3, :a4, :a5, :a6, :correct, :label)";

        $command = $connection->createCommand($sql);
        $command->bindValue(':question', $questionData['question']);
        if ( isset($questionData['id']) ) {
            $command->bindValue(':id', $questionData['id']);
        } else {
            $command->bindValue(':id', null);
        }
        for ($i = 1; $i <= 6; $i++) {
            $command->bindValue(":a$i", $questionData['a'.$i] ?? null);
        }
        $command->bindValue(':label', $questionData['label'] ?? null);

        if ( ! isset($questionData['correct']) ) {
            $questionData['correct'] = 1;
            Yii::$app->session->setFlash('error', "A correct answer is missing, answer 1 is randomly added as correct.");
        }
        $command->bindValue(':correct', $questionData['correct']);

        $command->execute();
    }

    public function actionExport($quiz_id=0) {
        
        if ( $quiz_id == 0 ) {
            $sql = "SELECT max(id) id FROM quiz WHERE active = 1";
            $quiz_id = Yii::$app->db->createCommand($sql)->queryOne()['id'];
            if ( $quiz_id == "" ) {
                $sql = "SELECT max(id) id FROM quiz";
                $quiz_id = Yii::$app->db->createCommand($sql)->queryOne()['id'];
                if ( $quiz_id == "" ) {
                    return $this->redirect(['/question']);
                }
            }
        }

        // ToDO select only quetions for this quiz, join with quizquestion

        // $sql = "select * from question";

        $sql = "select
                q.id id, question question, a1, a2, a3, a4, a5, a6, correct, label
                from question q
                join quizquestion qq on qq.question_id = q.id
                where qq.quiz_id=$quiz_id and qq.active=1";
       
        $questions = Yii::$app->db->createCommand($sql)->queryAll();
        $output = "";
        foreach($questions as $question) {
            $output .= "QQ\n".$question['question']."\n";
            for ($i=1; $i<7; $i++) {
                if ( $question['correct'] == $i ) {
                    $output .= "AC\n". $question['a'.$i]."\n";
                } else {
                    $output .= "AA\n". $question['a'.$i]."\n";
                }
            }
            $output .= "LL\n".$question['label']."\n";
            $output .= "ID\n".$question['id']."\n";
        }

        return $this->render('export', [ 'output' => $output ]);
    }

    public function actionBulkDelete($quiz_id) {
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

}
