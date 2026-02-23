<?php

namespace app\components;

use Yii;
use yii\base\Component;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use app\models\Log;
use app\models\LogType;
use app\models\OsintPost;
use app\models\OsintAiAnalysis;

//this component analyses twitter(x), facebook and tiktok posts for analysis.

class GlobalOSINTAnalyzer extends Component
{
    private $client;
    private $apiKey;
    private $aiKey;

    public function init()
    {
        parent::init();
        $this->client = new Client(['timeout' => 60, 'http_errors' => false]);
        $this->apiKey = Yii::$app->params['RAPIDAPI_KEY'] ?? '';
        $this->aiKey = Yii::$app->params['openaiApiKey'] ?? '';
    }

    private function saveAiAnalysis($requestId, $keyword, array $aiAnalysis)
{
    $model = new OsintAiAnalysis();

    $model->request_id = $requestId;
    $model->keyword = $keyword;
    $model->summary = $aiAnalysis['threat_summary'] ?? '';
    $model->numerical_score = $aiAnalysis['numerical_score'] ?? 0;
    $model->report = json_encode($aiAnalysis, JSON_UNESCAPED_UNICODE);
    $model->analyzed_at = date('Y-m-d H:i:s');

    if (!$model->save()) {
        Log::log(
            'OSINT AI Analysis Save Failed',
            'Failed saving AI analysis',
            LogType::ERROR,
            $model->errors
        );
    }
}

public function fetchGlobalOSINTData($keyword)
{
    $request_id = uniqid();
        if (empty($this->apiKey)) return ['error' => 'API Key missing'];

        // ENHANCEMENT: Targeted Query Construction
        // We append "Kenya" to the keyword to force the social media algorithm 
        // to prioritize Kenyan-based results.
        $targetedQuery = $keyword . " Kenya";
        $promises = [
            'x' => $this->client->getAsync('https://twitter-api45.p.rapidapi.com/search.php', [
                'headers' => ['X-RapidAPI-Key' => $this->apiKey, 'X-RapidAPI-Host' => 'twitter-api45.p.rapidapi.com'],
                'query' => ['query' => $targetedQuery],
            ]),
            'tiktok' => $this->client->getAsync('https://tiktok-api6.p.rapidapi.com/search/general/query',
                [
                    'headers' => [
                        'X-RapidAPI-Key' => $this->apiKey,
                        'X-RapidAPI-Host' => 'tiktok-api6.p.rapidapi.com'
                    ],
                    'query' => [
                        'query' => $targetedQuery
                    ],
            ]),

            'facebook' => $this->client->getAsync(
            'https://facebook-scraper3.p.rapidapi.com/search/posts',
            [
                'headers' => [
                    'X-RapidAPI-Key' => $this->apiKey,
                    'X-RapidAPI-Host' => 'facebook-scraper3.p.rapidapi.com'
                ],
                'query' => [
                    'query' => $targetedQuery
                ],
            ]),

        ];

        $responses = Promise\Utils::settle($promises)->wait();

        $rawPlatforms = [
            'x' => $this->parseXResponse($responses['x']),
            'tiktok' => $this->parseTikTokResponse($responses['tiktok']),
            'facebook' => $this->parseFacebookResponse($responses['facebook']),
        ];

        $aiAnalysis = $this->getAiIntelligence($keyword, $rawPlatforms);
        $this->saveAiAnalysis($request_id, $keyword, $aiAnalysis);


        // Persist posts to DB
        foreach ($rawPlatforms as $platformName => $platformData) {
            foreach ($platformData['data'] as $post) {
                $osintPost = new OsintPost();
                $osintPost->request_id = $request_id;
                $osintPost->keyword = $keyword;
                $osintPost->platform = $platformName;
                $osintPost->text = $post['text'] ?? '';
                $osintPost->author = $post['author'] ?? '';
                $osintPost->created_at = (new \DateTime($post['created_at'] ?? 'now'))->format('Y-m-d H:i:s');
                $osintPost->location = $post['location'] ?? null;
                $osintPost->url = $post['url'] ?? null;
                $osintPost->post_id = $post['post_id'] ?? null;
                $osintPost->video_url = $post['video_url'] ?? null;
                $osintPost->cover = $post['cover'] ?? null;
                $osintPost->engagement = json_encode($post['engagement'] ?? []);
                $osintPost->ai_report = json_encode($aiAnalysis ?? []); //to remove
                $osintPost->threat_score = $aiAnalysis['numerical_score'] ?? 0; //to remove
                $osintPost->save(false);
            }
        }


        return [
            'keyword' => $keyword,
            'timestamp' => date('Y-m-d H:i:s'),
            'platforms' => $rawPlatforms,
            'ai_report' => $aiAnalysis,
            'threat_score' => $aiAnalysis['numerical_score'] ?? 0,
        ];
    }

private function getAiIntelligence($keyword, $data)
{
    if (empty($this->aiKey)) {
        return [
            'threat_summary' => 'AI Key missing.',
            'decoded_language' => [],
            'dog_whistles' => [],
            'localized_risks' => [],
            'location_suggestions' => [],
            'numerical_score' => 0
        ];
    }

    // 1️⃣ Summarize social media content
    $contentSummary = "";
    foreach ($data as $platform => $platformResult) {
        foreach (array_slice($platformResult['data'] ?? [], 0, 8) as $item) {
            $contentSummary .= sprintf(
                "[%s] %s (@%s) in %s: %s\n",
                strtoupper($platform),
                $item['created_at'] ?? 'N/A',
                $item['author'] ?? 'N/A',
                $item['location'] ?? 'Unknown',
                $item['text'] ?? ''
            );
        }
    }

    // 2️⃣ Construct strict prompt with schema instructions
    $prompt = <<<PROMPT
### ROLE ###
You are a Senior Intelligence Officer for the Kenyan National Intelligence Service,
with expertise in Kenyan socio-political dynamics, street intelligence, and Sheng linguistics.

### TASK ###
Analyze the following content:
$contentSummary

### OUTPUT (STRICT JSON SCHEMA) ###
Return ONLY valid JSON with EXACT keys and types:
- threat_summary (string)
- decoded_language (array of {original_term, language, decoded_meaning, contextual_explanation})
- dog_whistles (array of {phrase, implied_signal, threat_type, confidence})
- localized_risks (array of {risk_description, location, severity})
- location_suggestions (array of {location_name, reason})
- numerical_score (number)

If no data exists for a section, return an EMPTY ARRAY, not a string.
PROMPT;

    try {
        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->aiKey,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'model' => 'gpt-4o-mini',
                'messages' => [['role'=>'user','content'=>$prompt]],
                'max_tokens' => 1000
            ]
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        $rawContent = $body['choices'][0]['message']['content'] ?? '{}';

        // 3️⃣ Extract first JSON object robustly
        if (preg_match('/\{.*\}/s', $rawContent, $matches)) {
            $aiData = json_decode($matches[0], true);
        } else {
            $aiData = [];
        }

            Log::log(
                'OSINT AI Analysis complete',
                'the AI analysis for the prompt is complete - '.$prompt,
                LogType::API,
                $aiData ?? NULL
            );

        // 4️⃣ Auto-repair and normalize keys
        $standard = [
            'threat_summary' => '',
            'decoded_language' => [],
            'dog_whistles' => [],
            'localized_risks' => [],
            'location_suggestions' => [],
            'numerical_score' => 0
        ];

        if (!is_array($aiData)) {
            $aiData = [];
        }

        foreach ($standard as $key => $default) {
            if (!isset($aiData[$key]) || ($key !== 'numerical_score' && !is_array($aiData[$key]))) {
                $aiData[$key] = $default;
            }
            if ($key === 'numerical_score' && !is_numeric($aiData[$key])) {
                $aiData[$key] = 0;
            }
        }

        // 5️⃣ Optional: log any schema violation for monitoring
        foreach (['decoded_language','dog_whistles','localized_risks','location_suggestions'] as $key) {
            if (!is_array($aiData[$key])) {
                Log::log(
                    'AI Schema Violation',
                    "$key returned as non-array",
                    LogType::WARNING,
                    $aiData
                );
            }
        }

        return $aiData;

    } catch (\Exception $e) {
        Log::log(
            'AI Analysis Failed',
            'Error: ' . $e->getMessage() . ' | Prompt: ' . $prompt,
            LogType::ERROR,
            $data
        );

        return [
            'threat_summary' => 'AI Error',
            'decoded_language' => [],
            'dog_whistles' => [],
            'localized_risks' => [],
            'location_suggestions' => [],
            'numerical_score' => 0
        ];
    }
}
    private function parseXResponse($res)
    {
        if ($res['state'] !== 'fulfilled') {
            return [
                'data' => [],
                'status' => 'Fail'
            ];
        }

        $json = json_decode((string) $res['value']->getBody(), true);
        $posts = [];

        foreach (($json['timeline'] ?? []) as $i) {
            if (($i['type'] ?? '') === 'tweet') {
                $posts[] = [
                    'text'       => $i['text'] ?? '',
                    'author'     => $i['screen_name'] ?? 'Unknown',
                    'created_at' => $i['created_at'] ?? 'N/A',
                    'location'   => $i['location'] ?? 'N/A',
                    'engagement' => [
                        'likes'   => $i['favorites'] ?? 0,
                        'shares'  => $i['retweets'] ?? 0,
                        'replies' => $i['replies'] ?? 0,
                        'views'   => $i['views'] ?? 0,
                    ],
                ];
            }
        }

        return [
            'data'   => $posts,
            'status' => count($posts) > 0 ? 'OK' : 'Empty',
        ];
    }

    private function parseTikTokResponse($res) {
        if ($res['state'] !== 'fulfilled') {
            return ['data' => [], 'status' => 'Fail'];
        }

        $json = json_decode((string)$res['value']->getBody(), true);
        $videos = [];

        foreach (($json['videos'] ?? []) as $v) {
            $videos[] = [
                'text' => $v['description'] ?? '',
                'author' => $v['author_name'] ?? ($v['author'] ?? 'Unknown'),
                'created_at' => isset($v['create_time'])
                    ? date('Y-m-d H:i:s', $v['create_time'])
                    : 'N/A',
                'location' => 'N/A',
                'engagement' => [
                    'likes'    => $v['statistics']['number_of_hearts'] ?? 0,
                    'shares'   => $v['statistics']['number_of_reposts'] ?? 0,
                    'comments' => $v['statistics']['number_of_comments'] ?? 0,
                    'views'    => $v['statistics']['number_of_plays'] ?? 0
                ],
                'video_id' => $v['video_id'] ?? null,
                'cover'    => $v['cover'] ?? null,
                'video_url'=> $v['unwatermarked_download_url'] ?? $v['download_url'] ?? null
            ];
        }

        return [
            'data' => $videos,
            'status' => count($videos) > 0 ? 'OK' : 'Empty'
        ];
    }


    private function parseFacebookResponse($res)
    {
        if ($res['state'] !== 'fulfilled') {
            return ['data' => [], 'status' => 'Fail'];
        }

        $json = json_decode((string)$res['value']->getBody(), true);
        $posts = [];

        foreach (($json['results'] ?? []) as $p) {
            $posts[] = [
                'text' => $p['message'] ?? '',
                'author' => $p['author']['name'] ?? 'Unknown',
                'created_at' => isset($p['timestamp'])
                    ? date('Y-m-d H:i:s', $p['timestamp'])
                    : 'N/A',
                'location' => 'N/A',
                'engagement' => [
                    'likes'    => $p['reactions_count'] ?? 0,
                    'shares'   => $p['reshare_count'] ?? 0,
                    'comments' => $p['comments_count'] ?? 0,
                ],
                'url' => $p['url'] ?? null,
                'post_id' => $p['post_id'] ?? null,
            ];
        }

        return [
            'data' => $posts,
            'status' => count($posts) > 0 ? 'OK' : 'Empty'
        ];
    }

}