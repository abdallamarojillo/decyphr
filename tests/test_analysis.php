<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

// Mock application
$app = new yii\web\Application($config);

use app\models\Message;

echo "--- Starting Analysis Test ---\n";

// 1. Create a test message (Caesar Cipher Shift 1)
$message = new Message();
$message->encrypted_content = "Uif efmjwfsz pg uif hppet xjmm cf upnpsspx bu uisff jo uif bgufsoppo bu qjfs tfwfo. Uif dpoubdu jt xfbsjoh b sfe dbq. Ep opu bddfqu efmbzt.";
$message->intercepted_at = date('Y-m-d H:i:s');
$message->status = Message::STATUS_PENDING;

if ($message->save()) {
    echo "Test message created with ID: " . $message->id . "\n";
} else {
    echo "Failed to create test message: " . print_r($message->errors, true) . "\n";
    exit(1);
}

// 2. Trigger analysis
echo "Triggering analysis...\n";
$result = Yii::$app->cryptoAnalyzer->analyzeMessage($message->id);

// 3. Verify results
if ($result['success']) {
    echo "Analysis completed successfully!\n";
    
    $updatedMessage = Message::findOne($message->id);
    echo "Status: " . $updatedMessage->status . "\n";
    echo "Decrypted Content: " . $updatedMessage->decrypted_content . "\n";
    echo "Encryption Type: " . $updatedMessage->encryption_type . "\n";
    
    if (!empty($result['errors'])) {
        echo "Warnings/Errors during analysis:\n";
        foreach ($result['errors'] as $error) {
            echo " - $error\n";
        }
    }
} else {
    echo "Analysis failed: " . ($result['error'] ?? 'Unknown error') . "\n";
    exit(1);
}

echo "--- Test Completed ---\n";
