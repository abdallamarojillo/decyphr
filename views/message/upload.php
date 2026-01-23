<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Intercepted Data Submission';
?>

<div class="message-upload">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-white p-4 border-0" style="background: linear-gradient(135deg, #002f87, #03E277);">
                    <div class="text-center">
                        <div class="bg-primary rounded-3 p-2 d-inline-block mb-2">
                            <i class="bi bi-shield-lock-fill fs-3"></i>
                        </div>
                        <h3 class="mb-0 fw-bold">Intercepted Data Submission</h3>
                        <p class="text-white-50 mb-0 small">Submit intercepted data in various formats (Text, Image, Audio)</p>
                    </div>
                </div>

                <div class="card-body p-5 bg-light">
                    <?php $form = ActiveForm::begin([
                        'id' => 'analysis-form',
                        'options' => ['enctype' => 'multipart/form-data'],
                        'action' => Url::to(['message/ajax-upload']),
                    ]); ?>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-2">Encrypted Content / Tactical Notes</label>
                        <?= $form->field($model, 'encrypted_content')->textarea([
                            'rows' => 6, 
                            'class' => 'form-control border-0 shadow-sm rounded-3 p-3',
                            'placeholder' => 'Paste encrypted text or tactical notes here...'
                        ])->label(false) ?>
                    </div>

                    <div class="upload-zone mb-4 p-5 border-2 border-dashed rounded-4 text-center bg-white shadow-sm position-relative" id="drop-zone" style="transition: all 0.3s ease;">
                        <div class="upload-icon-wrapper mb-3">
                            <i class="bi bi-cloud-arrow-up-fill display-1 text-primary opacity-75"></i>
                        </div>
                        <h5 class="fw-bold">Drop files here or click to upload</h5>
                        <p class="text-muted small">Supports Images (OCR), Audio (Transcription), and Text files</p>
                        <?= $form->field($model, 'file')->fileInput(['class' => 'position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer', 'id' => 'file-input'])->label(false) ?>
                        <div id="file-preview" class="mt-3 fw-bold text-primary animate__animated animate__fadeIn"></div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <?= $form->field($model, 'device_id')->textInput(['class' => 'form-control border-0 shadow-sm rounded-3', 'placeholder' => 'Device ID (Optional)'])->label('Source Device') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'intercepted_at')->input('datetime-local', ['class' => 'form-control border-0 shadow-sm rounded-3', 'value' => date('Y-m-d\TH:i')])->label('Intercept Time') ?>
                        </div>
                    </div>

                    <div class="d-grid">
                        <?= Html::submitButton('<i class="bi bi-cpu-fill me-2"></i> Start Intelligence Analysis', [
                            'class' => 'btn btn-primary btn-lg rounded-pill fw-bold py-3 shadow-sm',
                            'id' => 'submit-btn'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                    <!-- Progress Overlay -->
                    <div id="analysis-progress" class="d-none mt-5 text-center animate__animated animate__fadeIn">
                        <div class="spinner-grow text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2" id="progress-status">Initializing Analysis Engine...</h5>
                        <div class="progress rounded-pill mb-3" style="height: 10px;">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                        </div>
                        <p class="text-muted small">Leveraging OpenAI GPT-4o for intelligence extraction.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-dashed { border-style: dashed !important; border-color: #dee2e6 !important; }
.cursor-pointer { cursor: pointer; }
.rounded-4 { border-radius: 1.25rem !important; }
.upload-zone:hover { border-color: #0d6efd !important; background-color: #f8f9fa !important; transform: translateY(-2px); }
.card { transition: transform 0.3s ease; }
.form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15) !important; }
</style>

<!-- Include Animate.css for modern transitions -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<?php
$js = <<<JS
$('#file-input').on('change', function() {
    var fileName = $(this).val().split('\\\\').pop();
    if (fileName) {
        $('#file-preview').html('<i class="bi bi-file-earmark-check-fill me-2"></i>Selected: ' + fileName);
        $('.upload-icon-wrapper i').removeClass('bi-cloud-arrow-up-fill').addClass('bi-file-earmark-check-fill text-success');
    }
});

$('#analysis-form').on('submit', function(e) {
    e.preventDefault();
    var form = $(this);
    var formData = new FormData(this);
    
    $('#analysis-form').addClass('animate__animated animate__fadeOut').css('pointer-events', 'none');
    setTimeout(function() {
        $('#analysis-form').addClass('d-none');
        $('#analysis-progress').removeClass('d-none');
    }, 500);
    
    $('#submit-btn').prop('disabled', true);

    var statuses = [
        { text: "Uploading Intercepted Data...", progress: 20 },
        { text: "Running OCR/Transcription...", progress: 40 },
        { text: "Executing AI Intelligence Extraction...", progress: 60 },
        { text: "Attempting to decipher the data...", progress: 80 },
        { text: "Finalizing Intelligence Report...", progress: 95 }
    ];
    
    var statusIdx = 0;
    var statusInterval = setInterval(function() {
        if (statusIdx < statuses.length) {
            $('#progress-status').text(statuses[statusIdx].text);
            $('#progress-bar').css('width', statuses[statusIdx].progress + '%');
            statusIdx++;
        }
    }, 2500);

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#progress-bar').css('width', '100%');
                $('#progress-status').text("Analysis Complete! Redirecting...");
                setTimeout(function() {
                    window.location.href = response.redirect_url;
                }, 1500);
            } else {
                clearInterval(statusInterval);
                alert('Analysis Failed: ' + JSON.stringify(response.errors));
                $('#analysis-form').removeClass('d-none animate__fadeOut').addClass('animate__fadeIn').css('pointer-events', 'auto');
                $('#analysis-progress').addClass('d-none');
                $('#submit-btn').prop('disabled', false);
            }
        },
        error: function() {
            clearInterval(statusInterval);
            alert('A critical error occurred during submission. Please check your file size and network connection.');
            $('#analysis-form').removeClass('d-none animate__fadeOut').addClass('animate__fadeIn').css('pointer-events', 'auto');
            $('#analysis-progress').addClass('d-none');
            $('#submit-btn').prop('disabled', false);
        }
    });
});
JS;
$this->registerJs($js);
?>
