<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    private function getToken() {
         // check valid cookie (no time-out)
         $request = Yii::$app->request;
         $cookies = $request->cookies;
         if ($cookies->has('token')) {
             $token = $cookies->getValue('token');
         } else {
             // ToDo, what to do when no valid cookie exists
             dd('No Cookie');
         }
         return $token;
    }

    private function getSubmission() {
        $token = $this->getToken();
        $sql = "select quiz_id, no_answered, question_order from submission where token = '$token'";
        $submission = Yii::$app->db->createCommand($sql)->queryOne();

        return $submission;
    }

    private function getQuiz($id) {
        // check if quiz is still ative, otherwise redirect to final page
        $sql = "select name from quiz where id = $id and active = 1";
        $quiz = Yii::$app->db->createCommand($sql)->queryOne();

        if ( ! $quiz) {
            dd('Quiz is not active anymore');
        }

        return $quiz;
    }

    public function actionQuestion() {

        $submission = $this->getSubmission();
        $quiz = $this->getQuiz( $submission['quiz_id'] );

        // get question question_order[question_answered] and show on page
        $questionOrderArray = explode(' ', $submission['question_order']);
        $thisQuestion = $questionOrderArray[$submission['no_answered']];
        $sql = "select id, question, a1, a2, a3, a4, a5, a6 from question where id = $thisQuestion";
        $question = Yii::$app->db->createCommand($sql)->queryOne();

        $this->layout = false;
        return $this->render('question', [ 'title' => $quiz['name'], 'question' => $question, ]);
    }

    public function actionAnswer() {
        $submission = $this->getSubmission();
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
