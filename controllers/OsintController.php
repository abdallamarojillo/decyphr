<?php

namespace app\controllers;

use app\components\OsintDossier;
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
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\base\Exception;
use yii\web\ForbiddenHttpException;

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
                ->orderBy(['created_at' => SORT_DESC])
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

    public function actionView($request_id)
    {
        $role = GlobalHelper::CurrentUser('role');
        $userId = GlobalHelper::CurrentUser('id');

        // --- Base Queries ---
        $query = OsintAiAnalysis::find()->where(['request_id' => $request_id]);
        $postQuery = OsintPost::find()->where(['request_id' => $request_id]);

        if ($role !== 'admin') {
            $query->andWhere(['created_by' => $userId]);
            $postQuery->andWhere(['created_by' => $userId]);
        }

        $osintaidata = $query->all();
        $relatedPosts = $postQuery->orderBy(['created_at' => SORT_DESC])->all();

        if ($osintaidata == null) {
            throw new NotFoundHttpException('The requested data is not available');
        }

        // --- Collect Request IDs for high-threat reports ---
        $highThreatIds = [];
        foreach ($osintaidata as $analysis) {
            if ((int)$analysis->numerical_score >= 70) {
                $highThreatIds[] = $analysis->request_id;
            }
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

            if (empty($analysis->report)) {
                continue;
            }

            $report = json_decode($analysis->report, true);
            if (!is_array($report)) {
                continue;
            }

            $score = (int)$analysis->numerical_score;

            if (!empty($report['localized_risks']) && is_array($report['localized_risks'])) {
                foreach ($report['localized_risks'] as $risk) {

                    if (empty($risk['location'])) {
                        continue;
                    }

                    $loc = trim($risk['location']);

                    if (!isset($locationStats[$loc])) {
                        $locationStats[$loc] = [
                            'count' => 0,
                            'max_score' => 0
                        ];
                    }

                    $locationStats[$loc]['count']++;
                    $locationStats[$loc]['max_score'] = max($locationStats[$loc]['max_score'], $score);
                }
            }
        }

        uasort($locationStats, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        $topLocations = array_slice($locationStats, 0, 5, true);

        // --- USER MAPPING ---
        $userMap = [];

        foreach ($relatedPosts as $post) {

            if (empty($post->author)) {
                continue;
            }

            $author = trim($post->author);
            $platform = $post->platform ?: 'Unknown';

            $userMap[$author]['count'] = ($userMap[$author]['count'] ?? 0) + 1;
            $userMap[$author]['platforms'][] = $platform;
        }

        uasort($userMap, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return $this->render('view', [
            'osintaidata' => $osintaidata,
            'relatedPosts' => $relatedPosts,
            'topLocations' => $topLocations,
            'userMap' => $userMap,
            'metrics' => [
                'avgScore' => round($avgScore, 1),
                'critical' => $criticalCount,
                'totalPosts' => count($relatedPosts),
                'platformLabels' => array_keys($platformCounts),
                'platformData' => array_values($platformCounts),
            ]
        ]);
    }

    public function actionDeleteOsintPost()
    {
        $role   = GlobalHelper::CurrentUser('role');
        $userId = GlobalHelper::CurrentUser('id');

        $id = Yii::$app->request->post('id');
        $request_id = Yii::$app->request->post('request_id');

        $model = OsintPost::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('This post does not exist');
        }

        if ($role != 'admin' && $model->created_by != $userId) {
            throw new ForbiddenHttpException('You are not authorized to execute this action');
        }

        $keyword =  $model->keyword;
        $platform = $model->platform;
        $text = $model->text;
        $author = $model->author;

        if ($model->delete()) {

             Log::log(
                'Human in the Loop (HITL) Action',
                'Manually deleted osint data for request ID: ' . $request_id,
                LogType::RECORD_DELETE,
                [
                    'request_id' => $request_id,
                    'keyword' => $keyword,
                    'platform' => $platform,
                    'text' => $text,
                    'author' => $author,
                ]
            );

            Yii::$app->session->setFlash('success', 'Post successfully excluded from analysis.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete the post.');
        }

        return $this->redirect(['view', 'request_id' => $request_id]);
    }




    /**
     * Main OSINT Fetch - Provides data for the primary analysis UI
     */

    public function actionPost()
    {
        $request_id = '69a96bd012dff';
        $model = OsintPost::find()->where(['request_id' => $request_id])->limit(2)->one();
        print_r($model);
    }

    public function actionFetch()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $keyword = Yii::$app->request->post('keyword');
        $max_tokens = Yii::$app->request->post('max_tokens') ? : 2000;
        $ai_model = Yii::$app->request->post('ai_model') ? : 'gpt-4o';

        $options = [
            'ai_model' => $ai_model,
            'max_tokens' => (int)$max_tokens, // Cast to int for API consistency
        ];

        if (!$keyword) {
            return ['success' => false, 'error' => 'No tactical keyword provided.'];
        }

        try {
            $analyzer = Yii::$app->globalOSINTAnalyzer;
            $platformData = $analyzer->fetchGlobalOSINTData($keyword, $options);

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
                'Sent OSINT for Analysis with the keyword - '.$keyword. ', and the options : ai_model'.$ai_model. ', max_tokens '.$max_tokens,
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

    public function actionManuallyUpdateThreatScore()
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            throw new BadRequestHttpException('Invalid request method.');
        }

        $request_id   = $request->post('request_id');
        $threat_score = $request->post('threat_score');

        if (empty($request_id) || $threat_score === null) {
            throw new BadRequestHttpException('Missing required parameters.');
        }

        if (!is_numeric($threat_score)) {
            throw new BadRequestHttpException('Threat score must be numeric.');
        }

        $osint_ai_analysis = OsintAiAnalysis::findOne(['request_id' => $request_id]);

        if (!$osint_ai_analysis) {
            throw new NotFoundHttpException('AI Analysis record not found.');
        }

        $original_rating = $osint_ai_analysis->numerical_score;

        $transaction = Yii::$app->db->beginTransaction();

        try {

            // 1️. Update AI Analysis
            $osint_ai_analysis->numerical_score = $threat_score;

            if (!$osint_ai_analysis->save(false)) {
                throw new Exception('Failed to update AI analysis score.');
            }

            // 2️. Bulk update ALL OSINT posts for this request
            $rowsUpdated = OsintPost::updateAll(
                ['threat_score' => $threat_score],
                ['request_id' => $request_id]
            );

            if ($rowsUpdated === 0) {
                throw new Exception('No OSINT posts were updated.');
            }

            // 3️. Log HITL action
            Log::log(
                'Human in the Loop (HITL) Action',
                'Manually updated AI threat score for request ID: ' . $request_id .
                ' from ' . $original_rating . ' to ' . $threat_score,
                LogType::RECORD_CHANGE,
                [
                    'request_id' => $request_id,
                    'original_score' => $original_rating,
                    'new_score' => $threat_score,
                    'affected_posts' => $rowsUpdated,
                    'updated_by' => Yii::$app->user->id ?? null,
                ]
            );

            $transaction->commit();

            Yii::$app->session->setFlash('success', 
                "Threat score updated successfully. {$rowsUpdated} posts affected."
            );

        } catch (\Throwable $e) {

            $transaction->rollBack();

            Yii::error($e->getMessage());

            Yii::$app->session->setFlash('error', 'Failed to update threat score.');
        }

        return $this->redirect(['view', 'request_id' => $request_id]);
    }


    /**
     * Re-analyze existing OSINT posts for a given request_id
     * POST parameter: request_id
     */
    public function actionReanalyze($request_id)
    {
        try {
            $analyzer = Yii::$app->globalOSINTAnalyzer;

            $result = $analyzer->reanalyzeOsintPosts($request_id);

            if (isset($result['error'])) {
                Yii::$app->session->setFlash('error', 'Re-analysis failed: ' . $result['error']);
            } else {
                Yii::$app->session->setFlash('success', 'OSINT posts successfully re-analyzed.');

                Log::log(
                    'OSINT Re-analysis',
                    'Resubmitted OSINT Data for Reanalysis',
                    LogType::API,
                    ['request_id' => $request_id]
                );

            }

        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Re-analysis failed: ' . $e->getMessage());
            Log::log(
                'OSINT Re-analysis Failed',
                'Error: ' . $e->getMessage(),
                LogType::ERROR,
                ['request_id' => $request_id]
            );
        }

        // Redirect back to your view page
        return $this->redirect(['view', 'request_id' => $request_id]);
    }

    public function actionDeepDecodePost()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $text = trim((string) Yii::$app->request->post('text'));
        $postId = Yii::$app->request->post('post_id');
        $platform = trim((string) Yii::$app->request->post('platform'));
        $author = trim((string) Yii::$app->request->post('author'));

        if ($text === '') {
            return ['success' => false, 'error' => 'No post text provided.'];
        }

        try {
            $analyzer = Yii::$app->aiAnalyzer; 
            $result = $analyzer->analyzeRawTextForOsint($text, [
                'platform' => $platform,
                'author' => $author,
                'post_id' => $postId,
            ]);

            return [
                'success' => true,
                'data' => $result,
                'post_id' => $postId,
            ];
        } catch (\Throwable $e) {
            Yii::error('Deep decode failed: ' . $e->getMessage(), __METHOD__);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }


public function actionExportPdf($id)
{
    $model = OsintAiAnalysis::findOne($id);

    if (!$model) {
        throw new NotFoundHttpException('Report not found.');
    }

    $report = is_array($model->report) ? $model->report : json_decode($model->report, true);
    if (!is_array($report)) {
        $report = [];
    }

    $scoreRaw = (float) ($report['numerical_score'] ?? 0);
    $displayScore = $scoreRaw <= 10 ? $scoreRaw * 10 : $scoreRaw;
    $trajectory = $report['risk_trajectory'] ?? 'Stable';

    $statusLabel = 'Low';
    if ($displayScore > 70) {
        $statusLabel = 'Critical';
    } elseif ($displayScore > 40) {
        $statusLabel = 'Elevated';
    }

    $localizedRisks = (array) ($report['localized_risks'] ?? []);
    $decodedLanguage = (array) ($report['decoded_language'] ?? []);
    $locationSuggestions = (array) ($report['location_suggestions'] ?? []);
    $analysisBasis = (array) ($report['analysis_basis'] ?? []);
    $recommendedInterventions = $report['recommended_interventions'] ?? [];

    if (!is_array($recommendedInterventions)) {
        $recommendedInterventions = [$recommendedInterventions];
    }

    $pdf = new OsintDossier();
    $pdf->reportTitle = 'Keyword Brief';
    $pdf->generatedAt = date('d M Y H:i');
    $pdf->classification = 'INTERNAL';
    $pdf->SetTitle('Keyword Insights: Strategic Intelligence Report');
    $pdf->SetAuthor(\Yii::$app->name);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    // Cover summary
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetTextColor(13, 44, 84);
    $pdf->Cell(0, 10, 'Keyword Insights: Strategic Intelligence Report', 0, 1, 'L');
    $pdf->Ln(2);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(90, 90, 90);
    $pdf->Cell(0, 6, 'Reference ID: ' . $model->id, 0, 1, 'L');
    $pdf->Cell(0, 6, 'Request ID: ' . $model->request_id, 0, 1, 'L');
    $pdf->Cell(0, 6, 'Keyword: ' . $model->keyword, 0, 1, 'L');
    $pdf->Cell(0, 6, 'Analysis Date: ' . date('d M Y H:i', strtotime($model->analyzed_at)), 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->InfoBox('Threat Exposure', number_format($displayScore, 1) . '%');
    $pdf->InfoBox('Status', $statusLabel);
    $pdf->InfoBox('Risk Trajectory', $trajectory);

    // Executive Summary
    $pdf->SectionTitle('1. Executive Summary');
    $pdf->Paragraph((string) ($report['threat_summary'] ?? 'No summary available.'));

    // Geographic Threat Vectors
    $pdf->SectionTitle('2. Geographic Threat Vectors');
    if (!empty($localizedRisks)) {
        $pdf->TableHeader(
            ['Location', 'Severity', 'Risk Description'],
            [45, 30, 115]
        );

        foreach ($localizedRisks as $risk) {
            $pdf->TableRow([
                (string) ($risk['location'] ?? 'Unknown'),
                (string) ($risk['severity'] ?? 'Unspecified'),
                (string) ($risk['risk_description'] ?? '-'),
            ], [45, 30, 115]);
        }
    } else {
        $pdf->Paragraph('No geographic risks identified.');
    }

    // Decoded Language
    $pdf->SectionTitle('3. Decoded Language and Signals');
    if (!empty($decodedLanguage)) {
        $pdf->TableHeader(
            ['Original Term', 'Language', 'Meaning', 'Explanation'],
            [35, 25, 40, 90]
        );

        foreach ($decodedLanguage as $item) {
            $pdf->TableRow([
                (string) ($item['original_term'] ?? '-'),
                (string) ($item['language'] ?? '-'),
                (string) ($item['decoded_meaning'] ?? '-'),
                (string) ($item['contextual_explanation'] ?? '-'),
            ], [35, 25, 40, 90]);
        }
    } else {
        $pdf->Paragraph('No decoded language items available.');
    }

    // Surveillance
    $pdf->SectionTitle('4. Surveillance Protocol');
    if (!empty($locationSuggestions)) {
        foreach ($locationSuggestions as $loc) {
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor(30, 30, 30);
            $pdf->Cell(0, 6, (string) ($loc['location_name'] ?? 'Unnamed location'), 0, 1);
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(70, 70, 70);
            $pdf->MultiCell(0, 6, (string) ($loc['reason'] ?? 'No reason provided.'));
            $pdf->Ln(2);
        }
    } else {
        $pdf->Paragraph('No surveillance recommendations provided.');
    }

    // Methodology
    $pdf->SectionTitle('5. Analytical Methodology');
    if (!empty($analysisBasis)) {
        foreach ($analysisBasis as $index => $analysis) {
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor(30, 30, 30);
            $pdf->Cell(0, 6, 'Analysis Segment ' . ($index + 1), 0, 1);

            $pdf->LabelValueRow(
                'Indicators:',
                is_array($analysis['indicators_detected'] ?? null)
                    ? implode(', ', $analysis['indicators_detected'])
                    : (string) ($analysis['indicators_detected'] ?? 'Not provided')
            );

            $quotes = (array) ($analysis['evidence_quotes'] ?? []);
            if (!empty($quotes)) {
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(0, 7, 'Evidence Quotes:', 0, 1);
                foreach ($quotes as $quote) {
                    $pdf->BulletItem((string) $quote);
                }
            }

            $pdf->LabelValueRow(
                'Inference Rules:',
                is_array($analysis['inference_rules_applied'] ?? null)
                    ? implode(', ', $analysis['inference_rules_applied'])
                    : (string) ($analysis['inference_rules_applied'] ?? 'Not provided')
            );

            $pdf->LabelValueRow(
                'Uncertainty:',
                is_array($analysis['uncertainty_factors'] ?? null)
                    ? implode(', ', $analysis['uncertainty_factors'])
                    : (string) ($analysis['uncertainty_factors'] ?? 'None stated')
            );

            $pdf->Ln(3);
        }
    } else {
        $pdf->Paragraph('No analytical methodology data available.');
    }

    // Recommended interventions
    $pdf->SectionTitle('6. Recommended Interventions');
    if (!empty($recommendedInterventions)) {
        $pdf->TableHeader(
            ['Action', 'Responsible Entity', 'Priority'],
            [95, 60, 35]
        );

        foreach ($recommendedInterventions as $action) {
            $entities = $action['responsible_entity'] ?? [];
            if (!is_array($entities)) {
                $entities = [$entities];
            }

            $pdf->TableRow([
                (string) ($action['action'] ?? '-'),
                implode(', ', array_map('strval', $entities)),
                (string) ($action['priority'] ?? '-'),
            ], [95, 60, 35]);
        }
    } else {
        $pdf->Paragraph('No recommended interventions available.');
    }

    // Optional appendix: related posts
    $posts = OsintPost::find()
        ->where(['request_id' => $model->request_id])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(20)
        ->all();

    if (!empty($posts)) {
        $pdf->SectionTitle('Appendix A. Related Source Posts');
        $pdf->TableHeader(
            ['Platform', 'Author', 'Content'],
            [25, 35, 130]
        );

        foreach ($posts as $post) {
            $pdf->TableRow([
                (string) ($post->platform ?? '-'),
                (string) ($post->author ?? '-'),
                (string) ($post->text ?? '-'),
            ], [25, 35, 130]);
        }
    }

    return \Yii::$app->response->sendContentAsFile(
        $pdf->Output('S'),
        'intelligence_dossier_' . $model->id . '.pdf',
        [
            'mimeType' => 'application/pdf',
            'inline' => true,
        ]
    );
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