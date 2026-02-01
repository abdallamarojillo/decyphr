<?php

namespace app\components;

use Yii;
use yii\base\Component;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

//this component analyses twitter(x), facebook and tiktok posts for analysis.
//TO DO: Check tiktok, facebook api responses via postman
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

public function fetchGlobalOSINTData($keyword)
    {
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
        if (empty($this->aiKey)) return ['summary' => 'AI Key missing.', 'numerical_score' => 0];

        $contentSummary = "";
        foreach ($data as $name => $platformResult) {
            foreach (array_slice($platformResult['data'] ?? [], 0, 8) as $item) {
                $contentSummary .= sprintf(
                    "[%s] %s (@%s) in %s: %s\n",
                    strtoupper($name), $item['created_at'], $item['author'], $item['location'], $item['text']
                );
            }
        }

        $prompt = "### ROLE ###
        You are a Senior Intelligence Officer for the Kenyan National Intelligence Service,
        with deep expertise in:
        - Kenyan socio-political dynamics,
        - Street-level intelligence,
        - Kenyan Sheng (urban slang) including coded speech, dog-whistles, and evolving youth dialects.

        You think like a field analyst and a Sheng linguist combined.

        ### KENYAN LOCAL CONTEXT ###

        ### TASK ###
        Analyze the provided content and:

        1. Summarize the threat as it specifically affects Kenyan national interest.
        2. Detect and decode any Sheng, street slang, or coded language.
        3. Identify Sheng dog-whistles, euphemisms, or indirect references that may signal:
        - Violence
        - Radicalization
        - Crime
        - Civil unrest
        - Hate speech
        - Recruitment or mobilization
        4. Translate Sheng terms into clear English and explain their hidden or cultural meaning.
        5. Assess localized risks in a Kenyan context (e.g. estates, counties, youth groups, matatu culture, online Kenyan communities).
        6. Suggest the town/county/village of location in that message, if the messages suggests a place

        ### OUTPUT ###
        Return ONLY valid JSON
        ";

        try {
            $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => ['Authorization' => 'Bearer ' . $this->aiKey, 'Content-Type' => 'application/json'],
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'response_format' => ['type' => 'json_object']
                ]
            ]);
            $body = json_decode($response->getBody()->getContents(), true);
            return json_decode($body['choices'][0]['message']['content'], true);
        } catch (\Exception $e) {
            return ['summary' => 'AI Error', 'numerical_score' => 0];
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