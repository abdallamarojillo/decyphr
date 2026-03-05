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

    // --- Base Queries ---
    $query = OsintAiAnalysis::find()->orderBy(['id' => SORT_DESC]);
    $postQuery = OsintPost::find();

    if ($role !== 'admin') {
        $query->where(['created_by' => $userId]);
        $postQuery->where(['created_by' => $userId]);
    }

    $osintaidata = $query->all();

    // --- Collect Request IDs for high-threat reports ---
    $highThreatIds = [];
    foreach ($osintaidata as $analysis) {
        if ((int)$analysis->numerical_score >= 70) {
            $highThreatIds[] = $analysis->request_id;
        }
    }

    // --- Related Posts ---
    $relatedPosts = [];
    if (!empty($highThreatIds)) {
        $relatedPosts = OsintPost::find()
            ->where(['request_id' => $highThreatIds])
            ->all();
    }

    // --- Metrics ---
    $totalReports = count($osintaidata);
    $scores = array_column($osintaidata, 'numerical_score');
    $avgScore = $totalReports > 0 ? array_sum($scores) / $totalReports : 0;
    $criticalCount = count($highThreatIds);

    // --- Platform aggregation ---
    $platformCounts = [];
    foreach ($relatedPosts as $post) {
        $p = $post->platform ?: 'Unknown';
        $platformCounts[$p] = ($platformCounts[$p] ?? 0) + 1;
    }

    // --- Location aggregation ---
    $locationStats = [];
    foreach ($osintaidata as $analysis) {
        if (empty($analysis->report)) continue;
        $report = json_decode($analysis->report, true);
        if (!is_array($report)) continue;

        $score = (int)$analysis->numerical_score;

        if (!empty($report['localized_risks']) && is_array($report['localized_risks'])) {
            foreach ($report['localized_risks'] as $risk) {
                if (empty($risk['location'])) continue;
                $loc = trim($risk['location']);
                if (!isset($locationStats[$loc])) {
                    $locationStats[$loc] = ['count' => 0, 'max_score' => 0];
                }
                $locationStats[$loc]['count']++;
                $locationStats[$loc]['max_score'] = max($locationStats[$loc]['max_score'], $score);
            }
        }
    }

    uasort($locationStats, fn($a, $b) => $b['count'] <=> $a['count']);
    $topLocations = array_slice($locationStats, 0, 5, true); // still top 5 by location frequency

    // --- USER MAPPING: Count number of high-threat posts per user ---
    $userMap = [];
    foreach ($relatedPosts as $post) {
        if (empty($post->author)) continue;
        $author = trim($post->author);
        $platform = $post->platform ?: 'Unknown';
        $userMap[$author]['count'] = ($userMap[$author]['count'] ?? 0) + 1;
        $userMap[$author]['platforms'][] = $platform;
    }

    // Sort users by number of high-threat posts DESC
    uasort($userMap, fn($a, $b) => $b['count'] <=> $a['count']);

    return $this->render('index', [
        'osintaidata' => $osintaidata,
        'relatedPosts' => $relatedPosts,
        'topLocations' => $topLocations,
        'userMap' => $userMap, // all users with counts, sorted
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

    // --- Base Queries ---
     $query = OsintAiAnalysis::find()
        ->where(['>=', 'numerical_score', 70])
        ->orderBy(['numerical_score' => SORT_DESC, 'id' => SORT_DESC]);

    $postQuery = OsintPost::find();

    if ($role !== 'admin') {
        $query->where(['created_by' => $userId]);
        $postQuery->where(['created_by' => $userId]);
    }

    $osintaidata = $query->all();

    // --- Collect Request IDs for high-threat reports ---
    $highThreatIds = [];
    foreach ($osintaidata as $analysis) {
        if ((int)$analysis->numerical_score >= 70) {
            $highThreatIds[] = $analysis->request_id;
        }
    }

    // --- Related Posts ---
    $relatedPosts = [];
    if (!empty($highThreatIds)) {
        $relatedPosts = OsintPost::find()
            ->where(['request_id' => $highThreatIds])
            ->all();
    }

    // --- Metrics ---
    $totalReports = count($osintaidata);
    $scores = array_column($osintaidata, 'numerical_score');
    $avgScore = $totalReports > 0 ? array_sum($scores) / $totalReports : 0;
    $criticalCount = count($highThreatIds);

    // --- Platform aggregation ---
    $platformCounts = [];
    foreach ($relatedPosts as $post) {
        $p = $post->platform ?: 'Unknown';
        $platformCounts[$p] = ($platformCounts[$p] ?? 0) + 1;
    }

    // --- Location aggregation ---
    $locationStats = [];
    foreach ($osintaidata as $analysis) {
        if (empty($analysis->report)) continue;
        $report = json_decode($analysis->report, true);
        if (!is_array($report)) continue;

        $score = (int)$analysis->numerical_score;

        if (!empty($report['localized_risks']) && is_array($report['localized_risks'])) {
            foreach ($report['localized_risks'] as $risk) {
                if (empty($risk['location'])) continue;
                $loc = trim($risk['location']);
                if (!isset($locationStats[$loc])) {
                    $locationStats[$loc] = ['count' => 0, 'max_score' => 0];
                }
                $locationStats[$loc]['count']++;
                $locationStats[$loc]['max_score'] = max($locationStats[$loc]['max_score'], $score);
            }
        }
    }

    uasort($locationStats, fn($a, $b) => $b['count'] <=> $a['count']);
    $topLocations = array_slice($locationStats, 0, 5, true); // still top 5 by location frequency

    // --- USER MAPPING: Count number of high-threat posts per user ---
    $userMap = [];
    foreach ($relatedPosts as $post) {
        if (empty($post->author)) continue;
        $author = trim($post->author);
        $platform = $post->platform ?: 'Unknown';
        $userMap[$author]['count'] = ($userMap[$author]['count'] ?? 0) + 1;
        $userMap[$author]['platforms'][] = $platform;
    }

    // Sort users by number of high-threat posts DESC
    uasort($userMap, fn($a, $b) => $b['count'] <=> $a['count']);

    return $this->render('index', [
        'osintaidata' => $osintaidata,
        'relatedPosts' => $relatedPosts,
        'topLocations' => $topLocations,
        'isCriticalView' => true, 
        'userMap' => $userMap, // all users with counts, sorted
        'metrics' => [
            'avgScore' => round($avgScore, 1),
            'critical' => $criticalCount,
            'totalPosts' => count($relatedPosts),
            'platformLabels' => array_keys($platformCounts),
            'platformData' => array_values($platformCounts),
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