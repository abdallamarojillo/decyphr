<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\OtpForm $model */

$this->title = 'Verify OTP';
?>

<div class="site-verify-otp container mt-5" style="max-width: 400px;">
    <h3 class="mb-3 text-center">Two-Factor Authentication</h3>

    <p class="text-muted text-center">
        Enter the 6-digit code sent to your email and phone.
    </p>

    <?php $form = ActiveForm::begin(['id' => 'otp-form']); ?>

        <?= $form->field($model, 'otp')
            ->textInput([
                'maxlength' => 6,
                'placeholder' => 'Enter OTP',
                'autocomplete' => 'one-time-code',
                'class' => 'form-control text-center',
                'style' => 'letter-spacing: 6px; font-size: 1.5rem;',
            ])
            ->label(false)
        ?>

        <div class="d-grid gap-2 mt-4">
            <?= Html::submitButton(
                'Verify & Login',
                ['class' => 'btn btn-primary btn-lg']
            ) ?>
        </div>

    <?php ActiveForm::end(); ?>

    <div class="text-center mt-3">
        <small class="text-muted">
            Didn’t receive the code?
            <?= Html::a('Resend OTP', ['site/resend-otp'], ['class' => 'fw-bold']) ?>
        </small>
    </div>
</div>