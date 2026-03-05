<?php

namespace app\controllers;

use app\helpers\GlobalHelper;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Log;
use app\models\LogType;
use app\models\OsintAiAnalysis;
use app\models\OsintPost;

/**
 * Global OSINT Threat Intelligence Controller
 */
class OsintController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Only logged-in users
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'fetch' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Dashboard Main View
     */
    public function actionIndex()
    {
        $role = GlobalHelper::CurrentUser('role');
        $userId = GlobalHelper::CurrentUser('id');

        $query = OsintAiAnalysis::find()->orderBy(['id' => SORT_DESC]);
        $postQuery = OsintPost::find();

        if ($role !== 'admin') {
            $query->where(['created_by' => $userId]);
            $postQuery->where(['created_by' => $userId]);
        }

        $osintaidata = $query->all();
        $relatedPosts = $postQuery->all();

        // --- CALCULATE NEW METRICS ---
        $totalReports = count($osintaidata);
        $scores = array_column($osintaidata, 'numerical_score');
        $avgScore = $totalReports > 0 ? array_sum($scores) / $totalReports : 0;
        
        // Count Critical Reports (Score >= 70)
        $criticalCount = 0;
        foreach ($osintaidata as $m) {
            if ((int)$m->numerical_score >= 70) $criticalCount++;
        }

        // --- AGGREGATE PLATFORM DATA ---
        $platformCounts = [];
        foreach ($relatedPosts as $post) {
            // Assuming your OsintPost model has a 'platform' attribute (e.g., 'Telegram', 'X', 'Facebook')
            $p = $post->platform ?: 'Unknown'; 
            $platformCounts[$p] = ($platformCounts[$p] ?? 0) + 1;
        }

        return $this->render('index', [
            'osintaidata' => $osintaidata,
            'relatedPosts' => $relatedPosts,
            'metrics' => [
                'avgScore' => round($avgScore, 1),
                'critical' => $criticalCount,
                'totalPosts' => count($relatedPosts),
                'platformLabels' => array_keys($platformCounts),
                'platformData' => array_values($platformCounts),
            ]
        ]);
    }

public function actionCritical()
{
    $role = GlobalHelper::CurrentUser('role');
    $userId = GlobalHelper::CurrentUser('id');

    // 1. Set up the base query for high-risk scores (>= 70)
    $query = OsintAiAnalysis::find()
        ->where(['>=', 'numerical_score', 70])
        ->orderBy(['numerical_score' => SORT_DESC, 'id' => SORT_DESC]);

    // 2. Apply Role-based Security
    if ($role !== 'admin') {
        $query->andWhere(['created_by' => $userId]);
    }

    $osintaidata = $query->all();

    // 3. Fetch Related Posts only for these specific critical reports
    // This prevents loading thousands of unrelated posts into memory
    $requestIds = \yii\helpers\ArrayHelper::getColumn($osintaidata, 'request_id');
    $relatedPosts = [];
    
    if (!empty($requestIds)) {
        $relatedPosts = OsintPost::find()
            ->where(['request_id' => $requestIds])
            ->all();
    }

    // 4. Reusing the index view, but showing critical
    return $this->render('index', [
        'osintaidata' => $osintaidata,
        'relatedPosts' => $relatedPosts,
        'isCriticalView' => true, // Useful for adding a "Filtered by Critical" badge in the view
        'metrics' => [
            'avgScore' => count($osintaidata) > 0 ? round(array_sum(array_column($osintaidata, 'numerical_score')) / count($osintaidata), 1) : 0,
            'critical' => count($osintaidata),
            'totalPosts' => count($relatedPosts),
            // We pass empty platform arrays or re-calculate if needed
            'platformLabels' => [], 
            'platformData' => [],
        ]
    ]);
}

    /**
     * Main OSINT Fetch - Provides data for the primary analysis UI
     */

    public function actionFetch()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $keyword = Yii::$app->request->post('keyword');

        if (!$keyword) {
            return ['success' => false, 'error' => 'No tactical keyword provided.'];
        }

        try {
            $analyzer = Yii::$app->globalOSINTAnalyzer;
            $platformData = $analyzer->fetchGlobalOSINTData($keyword);

            // 1️. Ensure AI report exists and has correct keys
            $aiReportRaw = $platformData['ai_report'] ?? [];
            $aiReport = [
                'threat_summary'     => $aiReportRaw['threat_summary'] ?? '',
                'decoded_language'   => $aiReportRaw['decoded_language'] ?? [],
                'dog_whistles'       => $aiReportRaw['dog_whistles'] ?? [],
                'localized_risks'    => $aiReportRaw['localized_risks'] ?? [],
                'location_suggestions'=> $aiReportRaw['location_suggestions'] ?? []
            ];

            // 2️. Threat score fallback
            $threatScore = isset($platformData['threat_score']) ? (int) $platformData['threat_score'] : 0;

            // 3️. Platforms fallback
            $platforms = [];
            foreach (['x','facebook','tiktok'] as $key) {
                $platforms[$key] = $platformData['platforms'][$key] ?? ['data'=>[]];
            }

            // 4️. Log the event
            Log::log(
                'Sent OSINT for Analysis',
                'Sent OSINT for Analysis with the keyword - '.$keyword,
                LogType::API,
                $platformData ?? NULL
            );

            return [
                'success' => true,
                'data' => [
                    'ai_report' => $aiReport,
                    'threat_score' => $threatScore,
                    'platforms' => $platforms
                ]
            ];

        } catch (\Exception $e) {
            Log::log(
                'OSINT Fetch Failed',
                'Keyword: '.$keyword.' | Error: '.$e->getMessage(),
                LogType::ERROR,
                []
            );

            return ['success' => false, 'error' => 'Failed to fetch OSINT data'];
        }
    }

    /**
     * Threat Map View - Pass map markers to the Leaflet frontend
     */
    public function actionThreatMap($keyword = null)
    {
        $mapData = [];
        if ($keyword) {
            $data = Yii::$app->globalOSINTAnalyzer->fetchGlobalOSINTData($keyword);
            $mapData = $data['map_data'] ?? [];
        }

        return $this->render('threat-map', [
            'mapData' => $mapData,
            'keyword' => $keyword
        ]);
    }

    /**
     * Endpoint for AJAX Charting & Visuals
     */
    public function actionGetThreatData($keyword)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (!$keyword) {
            return ['success' => false, 'error' => 'Keyword required'];
        }

        try {
            $osintData = Yii::$app->globalOSINTAnalyzer->fetchGlobalOSINTData($keyword);

            // Structure data specifically for Chart.js or D3.js widgets
            $visualData = [
                'keyword' => $keyword,
                'threat_score' => $osintData['threat_score'],
                'sentiment' => $osintData['sentiment_summary'],
                'hotspots' => $osintData['geo_hotspots'],
                'prediction' => $osintData['prediction'],
                'platforms' => []
            ];

            foreach ($osintData['platforms'] as $key => $platform) {
                $visualData['platforms'][] = [
                    'name' => strtoupper($key),
                    'count' => count($platform['data'] ?? []),
                    'threat_indicators' => array_reduce($platform['data'] ?? [], function($carry, $item) {
                        return $carry + count($item['threat_indicators']);
                    }, 0)
                ];
            }

            return ['success' => true, 'data' => $visualData];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function actionPrediction()
    {
        return $this->render('prediction');
    }

    public function actionReport()
    {
        return $this->render('report');
    }
}