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
            $sql="update quizquestion set actief=$active where quiz_id=$quizId and question_id=$questionId";
        } else {
            $sql="insert into quizquestion (quiz_id, question_id, actief) values ($quizId, $questionId, 0)";
        }

        $result2 = Yii::$app->db->createCommand($sql)->execute();

        return ['success' => true, 'message' => 'Operation successful', 'result' => $result];
    }

}
