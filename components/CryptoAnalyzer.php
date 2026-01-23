<?php

namespace app\components;

use Yii;
use yii\base\Component;
use app\models\Message;
use app\models\AnalysisResult;
use app\models\FrequencyAnalysis;
use app\models\Entity;
use app\models\CommunicationLink;

class CryptoAnalyzer extends Component
{
    /**
     * Main analysis entry point
     */
    public function analyzeMessage($messageId)
    {
        $message = Message::findOne($messageId);
        if (!$message) {
            return ['success' => false, 'error' => 'Message not found'];
        }

        $message->status = Message::STATUS_ANALYZING;
        $message->save();

        $errors = [];

        try {
            // 1. AI Analysis (Multi-modal)
            $aiResult = Yii::$app->aiAnalyzer->analyze($message);

            if ($aiResult['success']) {
                $this->saveAIResult($message, $aiResult);
                
                // CRITICAL: If AI extracted text from image/audio, update message content
                if (isset($aiResult['extracted_text']) && !empty($aiResult['extracted_text'])) {
                    $message->encrypted_content = $aiResult['extracted_text'];
                    $message->save(false);
                }

                // Automated Entity Linking
                if (isset($aiResult['entities']) && is_array($aiResult['entities'])) {
                    foreach ($aiResult['entities'] as $entityData) {
                        $entity = Entity::find()->where(['name' => $entityData['name']])->one();
                        if (!$entity) {
                            $entity = new Entity();
                            $entity->name = $entityData['name'];
                            $entity->entity_type = $entityData['type'] ?? 'individual';
                            $entity->risk_score = $entityData['risk_score'] ?? 50;
                            $entity->save();
                        }
                        
                        // Link message to entity
                        $link = new CommunicationLink();
                        $link->message_id = $message->id;
                        $link->entity_id = $entity->id;
                        $link->link_type = 'mentioned';
                        $link->save();
                    }
                }
            } else {
                $errors[] = "AI Analysis Error: " . ($aiResult['error'] ?? 'Unknown error');
                $message->analysis_notes = "AI Error: " . ($aiResult['error'] ?? 'Unknown error');
            }

            // 2. Classical Cryptanalysis (if text is available)
            if (!empty($message->encrypted_content)) {
                $this->runClassicalAnalysis($message);
            }

            $message->status = Message::STATUS_ANALYZED;
            $message->save();

            return [
                'success' => true,
                'message_id' => $message->id,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            $message->status = Message::STATUS_FAILED;
            $message->analysis_notes = "Critical Error: " . $e->getMessage();
            $message->save();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function runClassicalAnalysis($message)
    {
        $text = $message->encrypted_content;
        
        // Frequency Analysis
        $charFreq = Yii::$app->frequencyAnalyzer->analyzeCharacterFrequency($text);
        $ic = Yii::$app->frequencyAnalyzer->calculateIndexOfCoincidence($text);
        $entropy = Yii::$app->frequencyAnalyzer->calculateEntropy($text);
        $suggestedCipher = Yii::$app->frequencyAnalyzer->suggestCipherType($text);

        $freqAnalysis = new FrequencyAnalysis();
        $freqAnalysis->message_id = $message->id;
        $freqAnalysis->character_frequencies = json_encode($charFreq);
        $freqAnalysis->index_of_coincidence = $ic;
        $freqAnalysis->entropy = $entropy;
        $freqAnalysis->suggested_cipher = $suggestedCipher;
        $freqAnalysis->save();

        // Try Decryption (Caesar)
        if ($ic > 0.05) {
            $caesarResults = Yii::$app->frequencyAnalyzer->breakCaesarCipher($text);
            if (!empty($caesarResults)) {
                $message->decrypted_content = $caesarResults[0]['text'];
                $message->encryption_type = "Caesar Cipher (Shift: " . $caesarResults[0]['shift'] . ")";
                $message->status = Message::STATUS_DECRYPTED;
                $message->save();
            }
        }
    }

    private function saveAIResult($message, $aiResult)
    {
        $result = new AnalysisResult();
        $result->message_id = $message->id;
        $result->analysis_type = 'ai_intelligence';
        $result->method = 'OpenAI GPT-4o';
        $result->confidence_score = $aiResult['confidence_score'] ?? 95.0;
        
        // Properly format insights - ensure it's a string
        $insights = $aiResult['insights'] ?? '';
        if (is_array($insights)) {
            $insights = json_encode($insights);
        }
        
        $result->findings = json_encode(['insights' => $insights]);
        $result->ai_insights = $insights;
        $result->processing_time = 5.0;
        $result->analyzed_at = date('Y-m-d H:i:s');
        $result->save();
        
        // Store formatted insights in message analysis_notes
        $message->analysis_notes = $insights;
        $message->save(false);
    }
}