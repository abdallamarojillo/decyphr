<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\Message;
use app\models\Entity;
use app\models\AnalysisResult;
use app\models\CommunicationLink;

class DashboardController extends Controller
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

    public function actionIndex()
    {
        // Get statistics
        $stats = [
            'total_messages' => Message::find()->count(),
            'pending_messages' => Message::find()->where(['status' => Message::STATUS_PENDING])->count(),
            'analyzing_messages' => Message::find()->where(['status' => Message::STATUS_ANALYZING])->count(),
            'decrypted_messages' => Message::find()->where(['status' => Message::STATUS_DECRYPTED])->count(),
            'failed_messages' => Message::find()->where(['status' => Message::STATUS_FAILED])->count(),
            'total_entities' => Entity::find()->count(),
            'high_risk_entities' => Entity::find()->where(['>=', 'risk_score', 75])->count(),
            'total_links' => CommunicationLink::find()->count(),
            'total_analyses' => AnalysisResult::find()->count(),
            'critical_threats' => AnalysisResult::find()->where(['>=', 'confidence_score', 80])->count(),
            'decryption_rate' => Message::find()->count() > 0 
                ? round((Message::find()->where(['not', ['decrypted_content' => null]])->count() / Message::find()->count()) * 100, 1) 
                : 0,
        ];

        // Get recent messages
        $recentMessages = Message::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        // Get high-risk entities
        $highRiskEntities = Entity::find()
            ->where(['>=', 'risk_score', 75])
            ->orderBy(['risk_score' => SORT_DESC])
            ->limit(10)
            ->all();

        // Get recent analysis results
        $recentAnalyses = AnalysisResult::find()
            ->with('message')
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        // Get message status distribution
        $statusDistribution = [
            'Pending' => $stats['pending_messages'],
            'Analyzing' => $stats['analyzing_messages'],
            'Decrypted' => $stats['decrypted_messages'],
            'Failed' => $stats['failed_messages'],
        ];

        return $this->render('index', [
            'stats' => $stats,
            'recentMessages' => $recentMessages,
            'highRiskEntities' => $highRiskEntities,
            'recentAnalyses' => $recentAnalyses,
            'statusDistribution' => $statusDistribution,
        ]);
    }
}
