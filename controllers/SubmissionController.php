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
    public function actionIndex($quiz_id=null)
    {
        $searchModel = new SubmissionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $quiz_id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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

    function generateRandomToken($length = 32) {
        // Generate a random string of bytes
        $randomBytes = random_bytes($length);

        // Convert the binary data into hexadecimal representation
        $token = bin2hex($randomBytes);

        return $token;
    }

    public function actionStart()
    {
        $request = Yii::$app->request;

        if ($request->isPost) {
            $first_name = $request->post('first_name');
            $last_name = $request->post('last_name');
            $class = $request->post('class');
            $password = $request->post('password');
        } else {
            return $this->redirect(['/submission/create']);
        }

        $sql = "select id, name from quiz where password='$password' and active = 1";
        $result = Yii::$app->db->createCommand($sql)->queryOne();
        if ( ! $result ) {
            return $this->redirect(Yii::$app->request->referrer);
        }

        $quiz_name = $result['name'];
        $quiz_id = $result['id'];
        $token = $this->generateRandomToken();

        // Get all questions connected, shuffle and create space seprated string
        $sql = "select question_id from quizquestion where quiz_id = $quiz_id and active = 1";
        $result = Yii::$app->db->createCommand($sql)->queryAll();
        $questionIds = array_column( $result, 'question_id' );
        
        $no_questions = count($questionIds);
        shuffle( $questionIds );
        $question_order = implode(" ",$questionIds);

        $ip_address = Yii::$app->request->userIP;

        $sql = "insert into submission (token, first_name, last_name, class, question_order, no_questions, no_answered, no_correct, quiz_id, ip_address)
                values ('$token', '$first_name', '$last_name', '$class', '$question_order', $no_questions, 0, 0, $quiz_id, '$ip_address')";
        $result = Yii::$app->db->createCommand($sql)->execute();
        
        $cookie = new Cookie([
            'name' => 'token',
            'value' => $token,
            'expire' => time() + 3600 * 6, // 6 hours
        ]);
        Yii::$app->response->cookies->add($cookie);

        // redirect to question page
        return $this->redirect(['site/question']);
        
    }

    public function actionRestart($token) {
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
            return $this->redirect(['index', 'id' => $model->id]);
        }

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

        return $this->redirect(['index']);
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

    public function actionMonitor() {
        $sql = "select id from quiz where active = 1";
        $quiz = Yii::$app->db->createCommand($sql)->queryOne();


        $sql = "select * from submission where quiz_id = ${quiz['id']} order by first_name, last_name ASC";
        $submissions = Yii::$app->db->createCommand($sql)->queryAll();

        return $this->render('monitor', [
            'submissions' => $submissions,
        ]);
    }

    public function actionExport()
    {
        $sql = "select q.name, first_name, last_name, class,
                CASE 
                    WHEN no_correct = 0 THEN 0
                    ELSE ROUND((no_correct / CAST(s.no_questions AS DECIMAL)) * 100, 0)
                END AS score,
                s.no_questions, no_answered, no_correct,
                start_time, end_time, TIMESTAMPDIFF(minute, start_time, end_time) duration
                from submission s
                join quiz q on q.id = s.quiz_id
                where active = 1";
        $submissions = Yii::$app->db->createCommand($sql)->queryAll();

        if ($submissions) $this->exportExcel($submissions);
    }

    private function exportExcel($data) {
        header('Content-type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="canvas-export' . date('YmdHi') . '.csv"');
        // header("Pragma: no-cache");
        // header("Expires: 0");
        header('Content-Transfer-Encoding: binary');
        echo "\xEF\xBB\xBF";

        $seperator = ";"; // NL version, use , for EN

        foreach ($data[0] as $key => $value) {
            echo  "\"". $key ."\"". $seperator;
        }
        echo "\n";
        foreach ($data as $line) {
            foreach ($line as $key => $value) {
                // echo preg_replace('/[\s+,;]/', ' ', $value) . $seperator;
                echo  "\"". $value ."\"". $seperator;
            }
            echo "\n";
        }
    }


}
