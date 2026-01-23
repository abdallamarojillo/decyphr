<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;

class VisualizationController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionNetwork()
    {
        $entityIds = Yii::$app->request->get('entities');
        
        // Check for AJAX or explicit ajax parameter
        if (Yii::$app->request->isAjax || Yii::$app->request->get('ajax')) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return Yii::$app->graphBuilder->buildCommunicationGraph($entityIds);
        }

        return $this->render('network', [
            'entityIds' => $entityIds,
        ]);
    }

    public function actionTimeline($entityId = null)
    {
        if ($entityId) {
            $timeline = Yii::$app->graphBuilder->buildActivityTimeline($entityId);
            
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $timeline;
            }

            return $this->render('timeline', [
                'timeline' => $timeline,
                'entityId' => $entityId,
            ]);
        }

        return $this->render('timeline', [
            'timeline' => [],
            'entityId' => null,
        ]);
    }

    public function actionStats()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return Yii::$app->graphBuilder->calculateNetworkStats();
    }
}
