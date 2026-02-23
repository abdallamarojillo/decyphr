<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\OtpForm $model */

$this->title = 'Verify OTP';
?>

<div class="site-verify-otp d-flex flex-column align-items-center justify-content-center mt-5" style="max-width: 400px; margin: auto;">

    <!-- Key Icon -->
    <div class="mb-4 text-primary">
        <i class="fas fa-key fa-3x"></i>
    </div>

    <h3 class="mb-2 text-center fw-bold">Two-Factor Authentication</h3>

    <p class="text-muted text-center mb-4">
        Enter the 6-digit code sent to your email and phone.
    </p>

    <?php $form = ActiveForm::begin([
        'id' => 'otp-form',
        'options' => ['class' => 'w-100']
    ]); ?>

        <?= $form->field($model, 'otp')->textInput([
            'maxlength' => 6,
            'placeholder' => 'Enter OTP',
            'autocomplete' => 'one-time-code',
            'class' => 'form-control text-center py-3',
            'style' => 'letter-spacing: 10px; font-size: 1.75rem; border-radius: 0.75rem; border: 1px solid #ced4da;'
        ])->label(false) ?>

        <div class="d-grid gap-2 mt-4">
            <?= Html::submitButton('Verify & Login', ['class' => 'btn btn-primary btn-lg shadow-sm']) ?>
        </div>

    <?php ActiveForm::end(); ?>

    <div class="text-center mt-3">
        <small class="text-muted">
            Didn’t receive the code?
            <?= Html::a('Resend OTP', ['site/resend-otp'], ['class' => 'fw-bold text-decoration-none']) ?>
        </small>
    </div>
</div>

<style>
    .form-control:focus {
        border-color: #0069d9;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #004085;
    }
</style>

