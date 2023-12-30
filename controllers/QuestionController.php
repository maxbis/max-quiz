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
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
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
            return $this->redirect(['view', 'id' => $model->id]);
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
}
