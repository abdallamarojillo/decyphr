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
use app\helpers\GlobalHelper;
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
        
        $model = OsintAiAnalysis::find()->where(['request_id' => $requestId])->one();

        if($model == null)
        {
            $model = new OsintAiAnalysis();
        }
    
        $model->request_id = $requestId;
        $model->keyword = $keyword;
        $model->summary = $aiAnalysis['threat_summary'] ?? '';
        $model->numerical_score = $aiAnalysis['numerical_score'] ?? 0;
        $model->report = json_encode($aiAnalysis, JSON_UNESCAPED_UNICODE);
        $model->analyzed_at = date('Y-m-d H:i:s');
        $model->created_by = GlobalHelper::CurrentUser('id');

        if (!$model->save()) {
            Log::log(
                'OSINT AI Analysis Save Failed',
                'Failed saving AI analysis',
                LogType::ERROR,
                $model->errors
            );
        }
    }

    /**
     * Re-analyze existing OSINT posts for a given request_id
     *
     * @param string $requestId
     * @return array
     */
    public function reanalyzeOsintPosts(string $requestId)
    {
        // 1. Fetch posts from DB
        $posts = OsintPost::find()->where(['request_id' => $requestId])->all();

        if (empty($posts)) {
            return ['error' => 'No OSINT posts found for request_id: ' . $requestId];
        }

        // 2. Prepare $rawPlatforms structure as expected by getAiIntelligence
        $rawPlatforms = [];
        foreach ($posts as $post) {
            $platform = $post->platform ?? 'unknown';
            if (!isset($rawPlatforms[$platform])) {
                $rawPlatforms[$platform] = ['data' => []];
            }

            $rawPlatforms[$platform]['data'][] = [
                'text'       => $post->text,
                'author'     => $post->author,
                'created_at' => $post->created_at,
                'location'   => $post->location ?? 'N/A',
            ];
        }

        $keyword = $posts[0]->keyword ?? 'Unknown';

        // 3. Call private AI function internally
        $aiAnalysis = $this->getAiIntelligence($keyword, $rawPlatforms);

        // 4. Save new AI analysis
        $this->saveAiAnalysis($requestId, $keyword, $aiAnalysis);

        // 5. Update posts with new AI analysis and threat score
        foreach ($posts as $post) {
            $post->ai_report = json_encode($aiAnalysis, JSON_UNESCAPED_UNICODE);
            $post->threat_score = $aiAnalysis['numerical_score'] ?? 0;
            $post->save(false);
        }

        return [
            'keyword'      => $keyword,
            'timestamp'    => date('Y-m-d H:i:s'),
            'platforms'    => $rawPlatforms,
            'ai_report'    => $aiAnalysis,
            'threat_score' => $aiAnalysis['numerical_score'] ?? 0,
        ];
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

            'reddit' => $this->client->getAsync(
            'https://reddit34.p.rapidapi.com/getSearchPosts',
            [
                'headers' => [
                    'X-RapidAPI-Key' => $this->apiKey,
                    'X-RapidAPI-Host' => 'reddit34.p.rapidapi.com'
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
            'reddit' => $this->parseRedditResponse($responses['reddit']),
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
                $osintPost->created_by = GlobalHelper::CurrentUser('id');
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
            'analysis_basis' => [],
            'decoded_language' => [],
            'dog_whistles' => [],
            'localized_risks' => [],
            'location_suggestions' => [],
            'recommended_interventions' => [],
            'risk_trajectory' => '',
            'numerical_score' => 0
        ];
    }

    // Collect social media evidence
    $contentSummary = "";
    foreach ($data as $platform => $platformResult) {

        foreach (array_slice($platformResult['data'] ?? [], 0, 15) as $item) {

            $contentSummary .= sprintf(
                "[%s] %s (@%s) in %s: %s\n",
                strtoupper($platform),
                $item['created_at'] ?? 'N/A',
                $item['author'] ?? 'Unknown',
                $item['location'] ?? 'Unknown',
                $item['text'] ?? ''
            );
        }
    }

$prompt = <<<PROMPT
### ROLE ###
You are a Senior Intelligence Analyst specializing in Kenyan security threats,
online mobilization, political incitement, radicalization signals,
organized crime indicators, and Sheng / coded language used online.

You analyze social media OSINT to detect early warning signals of unrest,
violence, organized activity, influence campaigns, or destabilization risks.

### KEYWORD UNDER INVESTIGATION ###
{$keyword}

### SOCIAL MEDIA EVIDENCE ###
$contentSummary

### ANALYSIS TASK ###
Conduct a professional intelligence assessment.

Focus on:

• Threat signals
• Repeated narratives
• Mobilization language
• Sheng or coded terms
• Dog whistles
• Geographic concentrations
• Signs of coordination
• Potential escalation

### THREAT SUMMARY ###
Write a detailed intelligence brief (6–10 paragraphs) covering:

• Situation overview  
• Key threat indicators  
• Evidence from posts  
• Identified locations  
• Possible actors or groups involved  
• Risk trajectory  
• Potential real-world consequences  

### INTERVENTIONS ###
Recommend operational responses such as:

• monitoring priorities
• preventive engagement
• law enforcement readiness
• counter-messaging
• intelligence collection priorities

### OUTPUT FORMAT ###
Return ONLY valid JSON:

{
"threat_summary": "Detailed multi-paragraph intelligence brief",

"analysis_basis": [
{
"indicators_detected": "",
"evidence_quotes": ["example message"],
"inference_rules_applied": "",
"uncertainty_factors": ""
}
],

"decoded_language": [
{
"original_term": "",
"language": "",
"decoded_meaning": "",
"contextual_explanation": ""
}
],

"dog_whistles": [
{
"phrase": "",
"implied_signal": "",
"threat_type": "",
"confidence": 0.0
}
],

"localized_risks": [
{
"risk_description": "",
"location": "",
"severity": "Low | Medium | High | Critical"
}
],

"location_suggestions": [
{
"location_name": "",
"reason": ""
}
],

"recommended_interventions": [
{
"action": "",
"priority": "Low | Medium | High",
"responsible_entity": ""
}
],

"risk_trajectory": "Escalating | Stable | Declining",

"numerical_score": 0
}

If a section has no data return [].

Return ONLY JSON.
PROMPT;

    try {

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->aiKey,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.2
            ]
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        $rawContent = $body['choices'][0]['message']['content'] ?? '{}';

        // Extract JSON safely
        if (preg_match('/\{.*\}/s', $rawContent, $matches)) {
            $aiData = json_decode($matches[0], true);
        } else {
            $aiData = [];
        }

        Log::log(
            'OSINT AI Analysis complete',
            'AI analysis finished for keyword: '.$keyword,
            LogType::API,
            $aiData ?? null
        );

        // Standard schema
        $standard = [
            'threat_summary' => '',
            'analysis_basis' => [],
            'decoded_language' => [],
            'dog_whistles' => [],
            'localized_risks' => [],
            'location_suggestions' => [],
            'recommended_interventions' => [],
            'risk_trajectory' => '',
            'numerical_score' => 0
        ];

        foreach ($standard as $key => $default) {

            if (!array_key_exists($key, $aiData)) {
                $aiData[$key] = $default;
                continue;
            }

            switch ($key) {

                case 'threat_summary':
                case 'risk_trajectory':
                    if (!is_string($aiData[$key])) {
                        $aiData[$key] = $default;
                    }
                    break;

                case 'numerical_score':
                    if (!is_numeric($aiData[$key])) {
                        $aiData[$key] = 0;
                    }
                    break;

                default:
                    if (!is_array($aiData[$key])) {
                        $aiData[$key] = [];
                    }
            }
        }

        return $aiData;

    } catch (\Exception $e) {

        Log::log(
            'AI Analysis Failed',
            'Error: ' . $e->getMessage(),
            LogType::ERROR,
            $data
        );

        return [
            'threat_summary' => 'AI Error',
            'analysis_basis' => [],
            'decoded_language' => [],
            'dog_whistles' => [],
            'localized_risks' => [],
            'location_suggestions' => [],
            'recommended_interventions' => [],
            'risk_trajectory' => '',
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

    private function parseRedditResponse($res)
    {
        if ($res['state'] !== 'fulfilled') {
            return ['data' => [], 'status' => 'Fail'];
        }

        $json = json_decode((string)$res['value']->getBody(), true);
        $posts = [];

        foreach (($json['data']['posts'] ?? []) as $p) {

            $d = $p['data'] ?? [];

            $text = trim(($d['title'] ?? '') . ' ' . ($d['selftext'] ?? ''));

            $posts[] = [
                'text' => $text,
                'author' => $d['author'] ?? 'Unknown',
                'created_at' => isset($d['created_utc'])
                    ? date('Y-m-d H:i:s', $d['created_utc'])
                    : 'N/A',
                'location' => $d['subreddit_name_prefixed'] ?? 'N/A',
                'engagement' => [
                    'likes'    => $d['ups'] ?? 0,
                    'shares'   => $d['num_crossposts'] ?? 0,
                    'comments' => $d['num_comments'] ?? 0,
                    'views'    => $d['view_count'] ?? 0
                ],
                'post_id' => $d['id'] ?? null,
                'subreddit' => $d['subreddit'] ?? null,
                'thumbnail' => $d['thumbnail'] ?? null,
                'url' => isset($d['permalink'])
                    ? 'https://www.reddit.com' . $d['permalink']
                    : ($d['url'] ?? null)
            ];
        }

        return [
            'data' => $posts,
            'status' => count($posts) > 0 ? 'OK' : 'Empty'
        ];
    }

}