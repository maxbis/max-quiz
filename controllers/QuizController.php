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
    public function actionIndex()
    {
        $searchModel = new QuizSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionList()
    {
        $searchModel = new QuizSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Quiz model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Quiz();

        if ($this->request->isPost) {
            if ( $model->load($this->request->post()) ) {
                if ( $model->active == 1 ) {
                    Quiz::updateAll(['active' => 0], ['!=', 'id', $model->id]);
                }
                if ($model->save()) {
                    return $this->redirect(['index', 'id' => $model->id]);
                }
            } else {
                $model->loadDefaultValues();
            }
        }
        return $this->render('start', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Quiz model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ( $this->request->isPost && $model->load($this->request->post()) ) {
            // if ( $model->active == 1 ) {
            //     Quiz::updateAll(['active' => 0], ['!=', 'id', $model->id]);
            // }
            if ($model->save()) {
                return $this->redirect(['index', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    public function actionDelete($id)
    {
        $sql = "delete from quizquestion where quiz_id=$id";
        Yii::$app->db->createCommand($sql)->execute();

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
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

    public function actionView($id, $search=null)
    {
        $query = Question::find();
        if ($search !== null) {
            $query->where(['like', 'label', $search]);
        }
        $questions = $query->all();

        $quizQuestions = QuizQuestion::find()
            ->select('question_id')
            ->where(['quiz_id' => $id])
            ->andWhere(['active' => 1 ])
            ->all();
        $questionIds = ArrayHelper::getColumn($quizQuestions, 'question_id');

        return $this->render('questions', [
            'quiz' => $this->findModel($id),
            'questions' => $questions,
            'questionIds' => $questionIds,
        ]);
    }

    public function actionCopy($id) {
        $sql = "insert into quiz ( name ) select concat('copy of ',name) from quiz where id = $id";
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "select max(id) id from quiz;";
        $newId = Yii::$app->db->createCommand($sql)->queryOne()['id'];
        
        $sql = "select question_id from quizquestion where quiz_id = $id and active=1";
        $questionIds = Yii::$app->db->createCommand($sql)->queryAll();

        foreach($questionIds as $thisQuestionId) {
            $sql = "insert into quizquestion (quiz_id, question_id, active) values ($newId, ${thisQuestionId['question_id']}, 1)";
            Yii::$app->db->createCommand($sql)->execute();
        }

        return $this->redirect(['quiz/view', 'id' => $newId]);
    }
}
