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

    public function actionIndex()
    {
        $sql = "select quiz_id, count(*) as count from quizquestion where active = 1 group by quiz_id";
        $results = Yii::$app->db->createCommand($sql)->queryAll();

        $quizCounts = [];
        foreach ($results as $result) {
            $quizCounts[$result['quiz_id']] = (int) $result['count'];
        }

        $searchModel = new QuizSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'quizCounts' => $quizCounts,
        ]);
    }


    public function actionCreate()
    {
        $model = new Quiz();

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
            if ($model->save()) {
                return $this->redirect(['index', 'id' => $model->id]);
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

}
