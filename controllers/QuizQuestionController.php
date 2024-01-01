<?php

namespace app\controllers;

use Yii;

class QuizQuestionController extends \yii\web\Controller
{
    public function actionConnect()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $quizId = Yii::$app->request->post('quiz_id');
        $questionId = Yii::$app->request->post('question_id');
        $active = Yii::$app->request->post('active');

        $sql="select id from quizquestion where quiz_id=$quizId and question_id=$questionId";
        $result = Yii::$app->db->createCommand($sql)->queryOne();
        if ( $result ) {
            $sql="update quizquestion set active=$active where quiz_id=$quizId and question_id=$questionId";
        } else {
            $sql="insert into quizquestion (quiz_id, question_id, active) values ($quizId, $questionId, 1)";
        }

        Yii::$app->db->createCommand($sql)->execute();

        $sql = "select count(*) count from quizquestion where quiz_id=$quizId and active=1";
        $count = Yii::$app->db->createCommand($sql)->queryOne();

        return ['success' => true, 'message' => 'Operation successful', 'result' => $count];
    }

    public function actionActive()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = Yii::$app->request->post('id');
        $active = Yii::$app->request->post('active');


        $sql = "update quiz set active = $active where id = $id;";
        $result = Yii::$app->db->createCommand($sql)->execute();

        return ['success' => true, 'message' => 'Operation successful', 'result' => $result, 'sql' => $sql];
    }
}
