<?php

namespace app\live\controllers;

use app\live\models\LiveSession;
use Yii;
use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        $session = LiveSession::find()
            ->where(['!=', 'status', LiveSession::STATUS_FINISHED])
            ->orderBy([
                'created_at' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->one();

        if ($session === null) {
            Yii::$app->session->setFlash('error', 'There is no active live quiz right now.');
            return $this->redirect(['/live/student/index']);
        }

        return $this->redirect(['/live/student/play', 'code' => $session->join_code]);
    }
}
