<?php

namespace app\controllers;

use app\models\Quiz;
use app\models\QuizSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use app\models\question;
use app\models\quizquestion;
use yii\helpers\ArrayHelper;

use yii\filters\AccessControl;

use Yii;

/**
 * QuizController implements the CRUD actions for Quiz model.
 */
class QuizController extends Controller
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

    /**
     * Lists all Quiz models.
     *
     * @return string
     */
    // public function actionIndex2()
    // {
    //     $searchModel = new QuizSearch();
    //     $dataProvider = $searchModel->search($this->request->queryParams);

    //     return $this->render('index', [
    //         'searchModel' => $searchModel,
    //         'dataProvider' => $dataProvider,
    //     ]);
    // }

    private function updateQuestionNumbers()
    {
        $sql = "UPDATE quiz
                    SET no_questions = (
                    SELECT COUNT(*)
                    FROM quizquestion
                    WHERE quizquestion.quiz_id = quiz.id
                )
                WHERE EXISTS (
                    SELECT 1
                    FROM quizquestion
                    WHERE quizquestion.quiz_id = quiz.id
                );";
        Yii::$app->db->createCommand($sql)->execute();
    }

    public function actionIndex($reset=false)
    {
        if ( $reset) {
            $sql="update quiz set active=0";
            $results = Yii::$app->db->createCommand($sql)->execute();
        }

        $sql = "select quiz_id, count(*) as count from quizquestion where active = 1 group by quiz_id";
        $results = Yii::$app->db->createCommand($sql)->queryAll();

        $quizCounts = [];
        foreach ($results as $result) {
            $quizCounts[$result['quiz_id']] = (int) $result['count'];
        }

        // Fetch how many times each quiz has been taken (number of submissions per quiz)
        $sql = "select quiz_id, count(*) as taken_count from submission group by quiz_id";
        $results = Yii::$app->db->createCommand($sql)->queryAll();
        $quizTakenCounts = [];
        foreach ($results as $result) {
            $quizTakenCounts[$result['quiz_id']] = (int) $result['taken_count'];
        }

        // sort on names containing a . first and second on name
        $sql = "select * from quiz order by CASE WHEN name LIKE '%.%' THEN 1 ELSE 2 END, name";
        $quizes = Yii::$app->db->createCommand($sql)->queryAll();

        return $this->render('index2', [
            'quizCounts' => $quizCounts,
            'quizes' => $quizes,
            'quizTakenCounts' => $quizTakenCounts,
        ]);
    }


    public function actionCreate()
    {
        $model = new Quiz();
        $model->blind = 0;
        $model->ip_check = 1;
        $model->review = 1;
        $model->active = 0;
        
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                if ($model->active == 1) {
                    Quiz::updateAll(['active' => 0], ['!=', 'id', $model->id]);
                }
                if ($model->save()) {
                    return $this->redirect(['index', 'id' => $model->id]);
                }
            } else {
                $model->loadDefaultValues();
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }


    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            // if ( $model->active == 1 ) {
            //     Quiz::updateAll(['active' => 0], ['!=', 'id', $model->id]);
            // }
            try {
                if ($model->save()) {
                    return $this->redirect(['index', 'id' => $model->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'There was a problem saving your data.');
                    return $this->render('update', [
                        'model' => $model,
                    ]);
                }
            } catch (\Throwable $e) {
                // Catch any exceptions thrown during the save process
                Yii::error("Error saving model: " . $e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('error', explode( "\n", $e->getMessage() )[0]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    public function actionDelete($id, $show = 1)
    {
        $sql = "delete from quizquestion where quiz_id=$id";
        Yii::$app->db->createCommand($sql)->execute();

        $sql = "delete from submission where quiz_id=$id";
        Yii::$app->db->createCommand($sql)->execute();

        $this->findModel($id)->delete();

        return $this->redirect(['index', 'show' => $show]);
    }

    /**
     * Finds the Quiz model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Quiz the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Quiz::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionView($id, $search = null)
    {
        $query = Question::find();
        if ($search !== null) {
            $query->where(['like', 'label', $search]);
        }
        $questions = $query->all();

        $quizQuestions = QuizQuestion::find()
            ->select('question_id')
            ->where(['quiz_id' => $id])
            ->andWhere(['active' => 1])
            ->all();
        $questionIds = ArrayHelper::getColumn($quizQuestions, 'question_id');

        return $this->render('questions', [
            'quiz' => $this->findModel($id),
            'questions' => $questions,
            'questionIds' => $questionIds,
        ]);
    }

    public function actionCopy($id)
    {
        $sql = "insert into quiz ( name, password ) select concat('copy of ',name), concat('copy_',password) from quiz where id = $id";
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "select max(id) id from quiz;";
        $newId = Yii::$app->db->createCommand($sql)->queryOne()['id'];

        $sql = "select question_id from quizquestion where quiz_id = $id and active=1";
        $questionIds = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($questionIds as $thisQuestionId) {
            $sql = "insert into quizquestion (quiz_id, question_id, active) values ($newId, ${thisQuestionId['question_id']}, 1)";
            Yii::$app->db->createCommand($sql)->execute();
        }

        return $this->redirect(['question/index', 'quiz_id' => $newId]);
    }

    public function actionEditLabels($id)
    {
        $quiz = $this->findModel($id);
        
        // Get all active questions for this quiz
        $sql = "SELECT q.* FROM question q 
                INNER JOIN quizquestion qq ON q.id = qq.question_id 
                WHERE qq.quiz_id = :quiz_id AND qq.active = 1 
                ORDER BY COALESCE(q.sort_order, 0) ASC, q.id ASC";
        $questions = Yii::$app->db->createCommand($sql)
            ->bindValue(':quiz_id', $id)
            ->queryAll();

        if ($this->request->isPost) {
            $labels = $this->request->post('labels', []);
            $success = true;
            
            foreach ($labels as $questionId => $label) {
                $question = Question::findOne($questionId);
                if ($question) {
                    $question->label = $label;
                    if (!$question->save(false)) {
                        $success = false;
                    }
                }
            }
            
            if ($success) {
                Yii::$app->session->setFlash('success', 'Labels updated successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'There was a problem updating some labels.');
            }
            
            return $this->redirect(['edit-labels', 'id' => $id]);
        }

        return $this->render('edit-labels', [
            'quiz' => $quiz,
            'questions' => $questions,
        ]);
    }

}
