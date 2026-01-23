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
            $this->apiKey = getenv('OPENAI_API_KEY');
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
                    return $this->analyzeText($message->encrypted_content);
            }
        } catch (\Exception $e) {
            Yii::error("AI Analysis Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function analyzeText($text)
    {
        $prompt = "You are a cryptanalysis expert for law enforcement. Analyze the following text:\n\n" .
                  "TEXT: \"$text\"\n\n" .
                  "Provide a detailed report in JSON format with the following keys:\n" .
                  "1. language: Detected language.\n" .
                  "2. cipher_explanation: Technical explanation of the cipher used.\n" .
                  "3. translation: English translation if not in English.\n" .
                  "4. insights: Actionable intelligence (activities, urgency).\n" .
                  "5. entities: Array of objects with 'name', 'type' (individual/group/location), and 'risk_score' (0-100).\n" .
                  "6. risk_score: Overall threat level (0-100).\n" .
                  "7. confidence_score: Confidence in analysis (0-100).";

        $client = new Client(['timeout' => 60, 'verify' => false]);
        $response = $client->post($this->apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert cryptanalyst and linguist assisting law enforcement. Always respond in valid JSON.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.3,
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $result = json_decode($data['choices'][0]['message']['content'], true);
        
        // Format insights for display
        $formattedInsights = "LANGUAGE: " . ($result['language'] ?? 'Unknown') . "\n";
        $formattedInsights .= "CIPHER: " . ($result['cipher_explanation'] ?? 'None') . "\n";
        $formattedInsights .= "TRANSLATION: " . ($result['translation'] ?? 'N/A') . "\n";
        $formattedInsights .= "ANALYSIS: " . ($result['insights'] ?? 'No insights.');
        
        $result['success'] = true;
        $result['insights'] = $formattedInsights;
        return $result;
    }
private function analyzeImage($filePath)
{
    if (!file_exists($filePath)) {
        return ['success' => false, 'error' => 'Image file not found'];
    }

    $imageData = base64_encode(file_get_contents($filePath));
    $mimeType  = mime_content_type($filePath);

    $client = new Client(['timeout' => 60, 'verify' => false]);

    /* =========================
     * STEP 1: STRICT OCR ONLY
     * ========================= */
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
                    'content' => 'You are an OCR engine. Output ONLY visible text. No analysis.'
                ],
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => 'Extract all visible text exactly as shown.'],
                        ['type' => 'image_url', 'image_url' => [
                            'url' => "data:$mimeType;base64,$imageData"
                        ]],
                    ],
                ],
            ],
        ]
    ]);

    $ocrData = json_decode($ocrResponse->getBody()->getContents(), true);
    $extractedText = trim($ocrData['choices'][0]['message']['content'] ?? '');

    if ($extractedText === '') {
        return [
            'success' => false,
            'error' => 'No text could be extracted from image'
        ];
    }

    /* =========================
     * STEP 2: SANITIZE OCR NOISE
     * ========================= */
    $sanitizedText = preg_replace('/[^\x20-\x7E]/', '', $extractedText);
    $sanitizedText = preg_replace('/\s+/', ' ', $sanitizedText);

    /*
     * IMPORTANT:
     * We DO NOT attempt Base64 / AES decoding here.
     * OCR output is NEVER cryptographically reliable.
     */

    /* =========================
     * STEP 3: DELEGATE TO TEXT ANALYZER
     * ========================= */
    $textResult = $this->analyzeText($sanitizedText);

    // Preserve original OCR for audit trail
    if ($textResult['success']) {
        $textResult['extracted_text'] = $sanitizedText;
        $textResult['ocr_raw'] = $extractedText;
    }

    return $textResult;
}



    private function analyzeAudio($filePath)
    {
        if (!file_exists($filePath)) {
            return ['success' => false, 'error' => "Audio file not found at: " . $filePath];
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
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $transcription = $data['text'] ?? '';

        if (empty($transcription)) {
            return ['success' => false, 'error' => 'Failed to transcribe audio.'];
        }

        $result = $this->analyzeText($transcription);
        if ($result['success']) {
            $result['insights'] = "TRANSCRIPTION: $transcription\n\n" . $result['insights'];
            $result['extracted_text'] = $transcription;
        }
        
        return $result;
    }
}
