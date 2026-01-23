<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

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
        return $this->render('index');
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
            $data = $analyzer->fetchGlobalOSINTData($keyword);
            return ['success' => true, 'data' => $data];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
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