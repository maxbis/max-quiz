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


    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/question']);
        } else {
            return $this->redirect(['quiz/']);
        }
        
    }

    public function actionLogin()
    {
        MyHelpers::CheckIP();

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

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

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

    public function actionAbout()
    {
        return $this->render('about');
    }


    /* Quiz functions */

    private function getToken() {
         // check valid cookie (no time-out)
         $request = Yii::$app->request;
         $cookies = $request->cookies;
         if ($cookies->has('token')) {
             $token = $cookies->getValue('token');
         } else { // No cookie, start quiz
             return 0;
         }
         return $token;
    }


    private function getSubmission() {
        $token = $this->getToken();
        if ( ! $token ) {
            return 0;
        }
        $sql = "select * from submission where token = '".$token."'";
        $submission = Yii::$app->db->createCommand($sql)->queryOne();

        if (  $submission['no_answered'] !=  $submission['no_questions'] ) {
            $questionOrderArray = explode(' ', $submission['question_order']);
            // dd($submission);
            $thisQuestion = $questionOrderArray[$submission['no_answered']];
            $submission['thisQuestion'] = $thisQuestion;
        }

        return $submission;
    }


    private function getQuiz($id) {
        // check if quiz is still ative, otherwise redirect to final page
        $sql = "select name from quiz where id = $id";
        $quiz = Yii::$app->db->createCommand($sql)->queryOne();

        return $quiz;
    }


    public function actionQuestion() {
        $submission = $this->getSubmission();
        if ( ! $submission ) {
            return $this->redirect(['submission/create']);
        }
        // are we ready
        // ToDo add some method to force to stop.
        if (  $submission['no_answered'] ==  $submission['no_questions'] || $submission['finished']  ) {
            return $this->redirect(['site/finished']);
        }

        $quiz = $this->getQuiz( $submission['quiz_id'] );

        $sql = "select id, question, a1, a2, a3, a4, a5, a6 from question where id = ${submission['thisQuestion']}";
        $question = Yii::$app->db->createCommand($sql)->queryOne();

        $title = $quiz['name'] . ' ['.strtoupper(substr($submission['token'], -3)).'] ';

        $this->layout = false;
        return $this->render('question', [ 'title' => $title, 'question' => $question, 'submission' => $submission ]);
    }

    public function actionAnswer() {
        $request = Yii::$app->request;

        if ($request->isPost) {
            $givenAnswer = $request->post('selectedAnswer');
        } else {
            return $this->redirect(['site/question']);
        }

        if ( $givenAnswer == "" ) {
            return $this->redirect(['site/question']);
        }
        
        $token = $this->getToken();
        $submission = $this->getSubmission();
        $sql = "select correct from question where id = ${submission['thisQuestion']}";
        $question = Yii::$app->db->createCommand($sql)->queryOne();

        if ( $givenAnswer == $question['correct'] ) {
            $punt = 1;
        } else {
            $punt = 0;
        }

        $sql = "insert into log (submission_id, quiz_id, question_id, answer_no, correct)
                values (${submission['id']}, ${submission['quiz_id']}, ${submission['thisQuestion']}, ${givenAnswer}, ${punt})
                ";
        $log = Yii::$app->db->createCommand($sql)->execute();

        $sql = "update submission
                set no_correct = no_correct + $punt,
                no_answered = no_answered + 1,
                answer_order = concat(answer_order, ' ', '$givenAnswer') 
                where token = '".$this->getToken()."'";
        $question = Yii::$app->db->createCommand($sql)->execute();

        // are we ready?
        if (  $submission['no_answered']+1 ==  $submission['no_questions'] ) {
            $sql = "update submission set end_time = NOW(), finished=1 where token = '".$this->getToken()."'";
            $question = Yii::$app->db->createCommand($sql)->execute();
            return $this->redirect(['site/finished']);
        }

        return $this->redirect(['site/question']);
    }

    public function actionFinished() {
        $submission = $this->getSubmission();
        if ( ! $submission ) {
            return $this->redirect(['submission/create']);
        }

        $this->layout = false;
        return $this->render('finished', [ 'submission' => $submission ]);
    }


}
