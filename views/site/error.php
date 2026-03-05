<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception \Exception */

$this->title = $name;

// Extract HTTP status code if available
$code = property_exists($exception, 'statusCode') ? $exception->statusCode : 500;
?>

<div class="error-container d-flex align-items-center justify-content-center">
    <div class="error-card shadow-lg text-center p-5">
        <div class="error-icon-wrapper mb-4">
            <div class="pulse-ring"></div>
            <i class="bi bi-shield-lock-fill display-1 text-danger"></i>
        </div>

        <h1 class="fw-black tracking-tight text-white mb-2">
            SYSTEM <span class="text-danger">ERROR <?= $code ?></span>
        </h1>
        
        <div class="terminal-box mb-4">
            <div class="terminal-header d-flex gap-1 mb-2">
                <span class="dot red"></span>
                <span class="dot yellow"></span>
                <span class="dot green"></span>
            </div>
            <p class="text-start mb-0 font-monospace small">
                <span class="text-danger fw-bold">> ERROR: <?= Html::encode($message) ?></span><br>
                <span class="text-muted small">>> Trace ID: <?= Yii::$app->security->generateRandomString(8) ?></span>
            </p>
        </div>

        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
            <a href="<?= Url::to(['site/index']) ?>" class="btn btn-outline-light rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-house-door me-2"></i> Dashboard
            </a>
            <button onclick="history.back()" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-arrow-left me-2"></i> Retrace Step
            </button>
        </div>
    </div>
</div>

<style>
    /* CSS for the OSINT Error Aesthetic */
    body {
        background-color: #0b0e14; /* Deep Dark Background */
        background-image: radial-gradient(#1a1f29 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .error-container {
        min-height: 80vh;
    }

    .error-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        max-width: 600px;
        width: 100%;
    }

    .fw-black { font-weight: 900; }

    /* Terminal Look */
    .terminal-box {
        background: #000;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #333;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
    }

    .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    .red { background: #ff5f56; }
    .yellow { background: #ffbd2e; }
    .green { background: #27c93f; }

    /* Animated Pulse Effect */
    .error-icon-wrapper {
        position: relative;
        display: inline-block;
    }

    .pulse-ring {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100px;
        height: 100px;
        background: rgba(220, 53, 69, 0.2);
        border-radius: 50%;
        animation: pulse-animation 2s infinite;
    }

    @keyframes pulse-animation {
        0% { width: 80px; height: 80px; opacity: 1; }
        100% { width: 180px; height: 180px; opacity: 0; }
    }
</style>