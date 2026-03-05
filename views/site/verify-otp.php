<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm; // Switched to bootstrap5 for consistency

/** @var yii\web\View $this */
/** @var app\models\OtpForm $model */

$this->title = 'Verify OTP';
?>

<div class="site-verify-otp bg-light min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5" style="max-width: 450px; width: 100%;">
        
        <div class="d-flex justify-content-center mb-4">
            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" 
                 style="width: 80px; height: 80px;">
                <i class="fas fa-shield-alt fa-2x text-primary"></i>
            </div>
        </div>

        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark mb-2">Two-Factor Auth</h3>
            <p class="text-muted small px-3">
                We've sent a 6-digit verification code to your registered devices.
            </p>
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'otp-form',
            'fieldConfig' => [
                'template' => "{input}\n{error}",
                'errorOptions' => ['class' => 'invalid-feedback text-center mt-2'],
            ],
        ]); ?>

            <?= $form->field($model, 'otp')->textInput([
                'maxlength' => 6,
                'placeholder' => '000000',
                'autocomplete' => 'one-time-code',
                // Using bg-light border-0 for that sleek modern input feel
                'class' => 'form-control form-control-lg text-center fw-bold bg-light border-0 shadow-none py-3',
                'style' => 'letter-spacing: 12px; font-size: 2rem; border-radius: 12px;'
            ]) ?>

            <div class="d-grid gap-2 mt-4">
                <?= Html::submitButton('Verify & Login', [
                    'class' => 'btn btn-primary btn-lg border-0 rounded-3 fw-bold shadow-sm py-3',
                    'style' => 'background-color: #002f87;' 
                ]) ?>
            </div>

        <?php ActiveForm::end(); ?>

        <div class="text-center mt-4 pt-2">
            <p class="text-muted small mb-0">Didn’t receive the code?</p>
            <?= Html::a('Resend New Code', ['site/resend-otp'], [
                'class' => 'fw-bold text-decoration-none text-primary small'
            ]) ?>
        </div>

        <div class="mt-4 text-center border-top pt-3">
            <?= Html::a('<i class="fas fa-arrow-left me-2"></i>Back to Login', ['site/login'], [
                'class' => 'text-muted text-decoration-none small fw-medium'
            ]) ?>
        </div>
    </div>
</div>