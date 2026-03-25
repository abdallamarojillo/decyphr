<?php

namespace app\components;

use Yii;
use yii\base\Component;
use GuzzleHttp\Client;

class AIAnalyzer extends Component
{
    public $apiKey;
    public $apiUrl = 'https://api.openai.com/v1/chat/completions';
    public $whisperUrl = 'https://api.openai.com/v1/audio/transcriptions';
    public $model = 'gpt-4o';

    public function init()
    {
        parent::init();

        if (empty($this->apiKey)) {
            $this->apiKey = getenv('OPENAI_API_KEY') ?: (Yii::$app->params['openaiApiKey'] ?? null);
        }
    }

    /**
     * Analyze content using OpenAI (Text, Image, or Audio)
     */
    public function analyze($message)
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'OpenAI API Key not configured.'];
        }

        try {
            switch ($message->file_type) {
                case 'image':
                    return $this->analyzeImage($message->file_path);

                case 'audio':
                    return $this->analyzeAudio($message->file_path);

                default:
                    return $this->analyzeText($message->encrypted_content ?? '');
            }
        } catch (\Throwable $e) {
            Yii::error('AI Analysis Error: ' . $e->getMessage(), __METHOD__);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Purpose-built deep decode for individual OSINT posts.
     */
    public function analyzeRawTextForOsint(string $text, array $context = []): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'OpenAI API Key not configured.'];
        }

        $text = trim($text);

        if ($text === '') {
            return ['success' => false, 'error' => 'No text provided for analysis.'];
        }

        $platform = $context['platform'] ?? 'unknown';
        $author   = $context['author'] ?? 'unknown';
        $postId   = $context['post_id'] ?? null;

        $schema = [
            'success' => true,
            'input_type' => 'text',
            'source_text' => $text,
            'detected_language' => '',
            'communication_pattern' => '',
            'plain_english_meaning' => '',
            'hidden_signal_explanation' => '',
            'possible_intent' => '',
            'entities' => [],
            'risk_score' => 0,
            'confidence_score' => 0,
            'recommended_action' => '',
            // backward-compatible aliases for older consumers
            'language' => '',
            'cipher_explanation' => '',
            'translation' => '',
            'insights' => '',
        ];

        $prompt = <<<PROMPT
You are a senior intelligence analyst specializing in:
- organized crime indicators
- coded communication
- Sheng / slang interpretation
- online mobilization language
- suspicious shorthand and indirect signaling

Your task is NOT to invent a cipher.
You must first determine whether the message is:
- plain language
- slang
- coded slang
- indirect / obfuscated communication
- abbreviation-heavy
- symbolic messaging
- possibly cipher-like
- unclear / ambiguous

Analyze the following social media post in plain, practical intelligence terms.

POST CONTEXT
Platform: {$platform}
Author: {$author}
Post ID: {$postId}

POST TEXT
{$text}

Return ONLY valid JSON with this exact schema:

{
  "detected_language": "string",
  "communication_pattern": "plain language | slang | coded slang | indirect / obfuscated communication | abbreviation-heavy | symbolic messaging | possibly cipher-like | unclear / ambiguous",
  "plain_english_meaning": "plain-language interpretation of the message",
  "hidden_signal_explanation": "brief explanation of whether there are hidden, coded, indirect, or suspicious meanings",
  "possible_intent": "what the sender may be trying to coordinate, signal, encourage, conceal, or communicate",
  "entities": [
    {
      "name": "entity name",
      "type": "person | group | location | object | event | unknown",
      "risk_score": 0
    }
  ],
  "risk_score": 0,
  "confidence_score": 0,
  "recommended_action": "brief analyst recommendation"
}

Rules:
- Be conservative.
- Do not overstate criminal intent when evidence is weak.
- If the message is ordinary, say so clearly.
- Keep risk_score and confidence_score between 0 and 100.
- Return only JSON.
PROMPT;

        try {
            $client = new Client(['timeout' => 60, 'verify' => false]);

            $response = $client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a careful intelligence analyst. Always return valid JSON only.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.2,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $raw  = $data['choices'][0]['message']['content'] ?? '{}';
            $result = json_decode($raw, true);

            if (!is_array($result)) {
                $result = [];
            }

            $result = array_merge($schema, $result);

            if (!is_string($result['detected_language'])) {
                $result['detected_language'] = '';
            }
            if (!is_string($result['communication_pattern'])) {
                $result['communication_pattern'] = '';
            }
            if (!is_string($result['plain_english_meaning'])) {
                $result['plain_english_meaning'] = '';
            }
            if (!is_string($result['hidden_signal_explanation'])) {
                $result['hidden_signal_explanation'] = '';
            }
            if (!is_string($result['possible_intent'])) {
                $result['possible_intent'] = '';
            }
            if (!is_string($result['recommended_action'])) {
                $result['recommended_action'] = '';
            }
            if (!is_array($result['entities'])) {
                $result['entities'] = [];
            }

            $result['risk_score'] = is_numeric($result['risk_score'])
                ? max(0, min(100, (int) $result['risk_score']))
                : 0;

            $result['confidence_score'] = is_numeric($result['confidence_score'])
                ? max(0, min(100, (int) $result['confidence_score']))
                : 0;

            // Backward-compatible aliases
            $result['language'] = $result['detected_language'];
            $result['cipher_explanation'] = $result['communication_pattern'];
            $result['translation'] = $result['plain_english_meaning'];

            $result['insights'] =
                "LANGUAGE: " . ($result['detected_language'] ?: 'Unknown') . "\n" .
                "PATTERN: " . ($result['communication_pattern'] ?: 'Unknown') . "\n" .
                "PLAIN MEANING: " . ($result['plain_english_meaning'] ?: 'N/A') . "\n" .
                "HIDDEN SIGNAL: " . ($result['hidden_signal_explanation'] ?: 'No hidden meaning identified.') . "\n" .
                "INTENT: " . ($result['possible_intent'] ?: 'No clear intent identified.') . "\n" .
                "ACTION: " . ($result['recommended_action'] ?: 'Continue monitoring.');

            return $result;
        } catch (\Throwable $e) {
            Yii::error('AIAnalyzer deep decode error: ' . $e->getMessage(), __METHOD__);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'input_type' => 'text',
                'source_text' => $text,
                'detected_language' => '',
                'communication_pattern' => '',
                'plain_english_meaning' => '',
                'hidden_signal_explanation' => '',
                'possible_intent' => '',
                'entities' => [],
                'risk_score' => 0,
                'confidence_score' => 0,
                'recommended_action' => '',
                'language' => '',
                'cipher_explanation' => '',
                'translation' => '',
                'insights' => '',
            ];
        }
    }

    private function analyzeText($text)
    {
        return $this->analyzeRawTextForOsint((string) $text, ['platform' => 'direct_text']);
    }

    private function analyzeImage($filePath)
    {
        if (!file_exists($filePath)) {
            return ['success' => false, 'error' => 'Image file not found'];
        }

        $imageData = base64_encode(file_get_contents($filePath));
        $mimeType  = mime_content_type($filePath);

        $client = new Client(['timeout' => 60, 'verify' => false]);

        $ocrResponse = $client->post($this->apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model' => $this->model,
                'temperature' => 0.0,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an OCR engine. Output ONLY visible text. No analysis.',
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => 'Extract all visible text exactly as shown.'],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:$mimeType;base64,$imageData",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $ocrData = json_decode($ocrResponse->getBody()->getContents(), true);
        $extractedText = trim($ocrData['choices'][0]['message']['content'] ?? '');

        if ($extractedText === '') {
            return [
                'success' => false,
                'error' => 'No text could be extracted from image',
            ];
        }

        $sanitizedText = preg_replace('/[^\x20-\x7E]/', '', $extractedText);
        $sanitizedText = preg_replace('/\s+/', ' ', $sanitizedText);

        $textResult = $this->analyzeRawTextForOsint($sanitizedText, [
            'platform' => 'image_ocr',
        ]);

        if (!empty($textResult['success'])) {
            $textResult['input_type'] = 'image';
            $textResult['extracted_text'] = $sanitizedText;
            $textResult['ocr_raw'] = $extractedText;
        }

        return $textResult;
    }

    private function analyzeAudio($filePath)
    {
        if (!file_exists($filePath)) {
            return ['success' => false, 'error' => 'Audio file not found at: ' . $filePath];
        }

        $client = new Client(['timeout' => 60, 'verify' => false]);

        $response = $client->post($this->whisperUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => basename($filePath),
                ],
                [
                    'name'     => 'model',
                    'contents' => 'whisper-1',
                ],
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $transcription = trim($data['text'] ?? '');

        if ($transcription === '') {
            return ['success' => false, 'error' => 'Failed to transcribe audio.'];
        }

        $result = $this->analyzeRawTextForOsint($transcription, [
            'platform' => 'audio_transcription',
        ]);

        if (!empty($result['success'])) {
            $result['input_type'] = 'audio';
            $result['extracted_text'] = $transcription;
            $result['insights'] = "TRANSCRIPTION: {$transcription}\n\n" . ($result['insights'] ?? '');
        }

        return $result;
    }
}