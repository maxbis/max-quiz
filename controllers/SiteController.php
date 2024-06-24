<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

use app\models\Submission;
use app\models\Question;

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

    private function getToken()
    {
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


    private function getSubmission()
    {
        // get submission based on token (stored in cookie).
        $token = $this->getToken();
        if (!$token) {
            return 0;
        }
        $sql = "select s.*, q.name quiz_name, q.review quiz_review, q.active quiz_active
                from submission s join quiz q on q.id = s.quiz_id
                where token = '" . $token . "'";
        $submission = Yii::$app->db->createCommand($sql)->queryOne();
        if ( ! $submission ) {
            return false;
        }

        if ($submission['no_answered'] != $submission['no_questions']) {
            // determine next question number
            $questionOrderArray = explode(' ', $submission['question_order']);
            $thisQuestion = $questionOrderArray[$submission['no_answered']];
            $submission['thisQuestion'] = $thisQuestion;
        } else {
            // quiz is finised, no current question left anymore
            // this could only happen if an answer is received on a finshed quiz
            $submission['thisQuestion'] = -99;
        }

        return $submission;
    }


    private function getQuiz($id)
    {
        // check if quiz is still ative, otherwise redirect to final page
        $sql = "select name, blind from quiz where id = $id";
        $quiz = Yii::$app->db->createCommand($sql)->queryOne();

        return $quiz;
    }

    public function actionQuestion()
    {
        $submission = $this->getSubmission();
        if (!$submission) {
            return $this->redirect(['submission/create']);
        }
        // are we (still) ready?
        if ($submission['no_answered'] == $submission['no_questions'] || $submission['finished']) {
            return $this->redirect(['site/finished']);
        }

        $quiz = $this->getQuiz($submission['quiz_id']);

        $sql = "select id, question, a1, a2, a3, a4, a5, a6 from question where id = " . $submission['thisQuestion'];
        $question = Yii::$app->db->createCommand($sql)->queryOne();
        if (!$question) {
            $message = "Question id " . $submission['thisQuestion'] . "not availabel anymore, cannot coninue this quiz.";
            return $this->render('/site/error', ['message' => $message]);
        }

        $title = $quiz['name'] . ' [' .
            strtoupper(substr($submission['token'], 0, 3)) .'-'.
            strtoupper(substr($submission['first_name'], 0, 3)).
            strtoupper(substr($submission['last_name'], 0, 1)) . '] ';

        $this->layout = false;
        return $this->render('question', ['title' => $title, 'question' => $question, 'submission' => $submission, 'quiz' => $quiz]);
    }

    public function actionAnswer()
    {
        usleep(500000); // wait 0.5 seconds to prevent (re)post-DOS-attack
        $request = Yii::$app->request;

        if ($request->isPost) {
            $givenAnswer = $request->post('selectedAnswer');
            $no_answered = $request->post('no_answered');
        } else { // if no post, show question (again)
            return $this->redirect(['site/question']);
        }

        if ($givenAnswer == "") { // this should not happen
            writeLog("Error, the posted selectedAnswer is empty");
            return $this->redirect(['site/question']);
        }

        $submission = $this->getSubmission();
        if ($submission['thisQuestion'] == -99) {
            // this should not happen; an answer is posted while the quiz is finished
            writeLog($msg = "Sequence error, answer given after quiz was finished");
            return $this->redirect(['site/finished']);
        }

        // check order
        if ($no_answered != $submission['no_answered']) {
            // and answer was given on question n while the submission status expects an answer on question n+m
            writeLog("Sequence error: ${submission['id']}, ${submission['quiz_id']}, ${submission['thisQuestion']}");
            Yii::$app->session->setFlash('error', 'Sequence error: question is already answered!');
            return $this->redirect(['site/question']);
        }

        $sql = "select correct from question where id = " . $submission['thisQuestion'];
        $question = Yii::$app->db->createCommand($sql)->queryOne();

        if ($givenAnswer == $question['correct']) {
            $punt = 1;
        } else {
            $punt = 0;
        }

        $sql = "insert into log (submission_id, quiz_id, question_id, answer_no, correct, no_answered)
                values (${submission['id']}, ${submission['quiz_id']}, ${submission['thisQuestion']},
                        $givenAnswer, $punt, $no_answered )
                ";
        $log = Yii::$app->db->createCommand($sql)->execute();

        // update no_answered
        $sql = "update submission
                set no_correct = no_correct + $punt,
                no_answered = no_answered + 1,
                answer_order = concat(answer_order, ' ', '$givenAnswer') 
                where token = '" . $this->getToken() . "'";
        $question = Yii::$app->db->createCommand($sql)->execute();

        // are we ready? The $submission has the status before the update, therefor we add 1
        if ($submission['no_answered'] + 1 == $submission['no_questions']) {
            $sql = "update submission set end_time = NOW(), finished=1 where token = '" . $this->getToken() . "'";
            $question = Yii::$app->db->createCommand($sql)->execute();
            return $this->redirect(['site/finished']);
        }

        return $this->redirect(['site/question']);
    }

    public function actionFinished()
    {
        $submission = $this->getSubmission();
        if (!$submission) {
            return $this->redirect(['submission/create']);
        }

        $this->layout = false;
        return $this->render('finished', ['submission' => $submission]);
    }

    public function actionResults($token)
    {
        $submission = Submission::find()
            ->where(['submission.token' => $token])
            ->joinWith('quiz')
            // ->andWhere(['quiz.active' => 1])
            ->one();

        if (!isset($submission['id'])) {
            return $this->render('error', ['message' => 'No submission found for active quiz (quiz inactive?)']);
        }

        $questionIds = explode(" ", $submission['question_order']);
        $questions = Question::find()->where(['id' => $questionIds])->asArray()->all();
        $questionsById = [];
        foreach ($questions as $question) {
            $questionsById[$question['id']] = $question;
        }

        $this->layout = false;
        return $this->render('results', [
            'questionsById' => $questionsById,
            'submission' => $submission,
        ]);
    }
}
