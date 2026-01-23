<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';
$app = new yii\web\Application($config);

use app\models\Message;
use app\models\AnalysisResult;

$messages = Message::find()->all();

foreach ($messages as $message) {
    echo "Message ID: " . $message->id . "\n";
    echo "Status: " . $message->status . "\n";
    echo "Encryption Type: " . $message->encryption_type . "\n";
    echo "Decrypted Content: " . $message->decrypted_content . "\n";
    echo "Analysis Notes: " . $message->analysis_notes . "\n";
    
    $results = AnalysisResult::find()->where(['message_id' => $message->id])->all();
    echo "Analysis Results Count: " . count($results) . "\n";
    foreach ($results as $res) {
        echo " - Type: " . $res->analysis_type . ", Method: " . $res->method . "\n";
        if ($res->analysis_type == 'ai_analysis') {
            echo "   AI Insights: " . substr($res->ai_insights, 0, 100) . "...\n";
        }
    }
    echo "-----------------------------------\n";
}
