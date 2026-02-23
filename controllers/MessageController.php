<?php

namespace app\controllers;

use Yii;
use app\models\Message;
use app\models\AuditLog;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use app\models\Log;
use app\models\LogType;

class MessageController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'ajax-upload' => ['POST'],
                    'analyze' => ['GET', 'POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Message::find()->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $message = $this->findModel($id);
        $analysisResults = $message->getAnalysisResults()->all();
        $frequencyAnalysis = $message->getFrequencyAnalysis()->one();

        return $this->render('view', [
            'message' => $message,
            'analysisResults' => $analysisResults,
            'frequencyAnalysis' => $frequencyAnalysis,
        ]);
    }

    /**
     * Trigger analysis for an existing message
     */
    public function actionAnalyze($id)
    {
        $message = $this->findModel($id);
        
        // Update status to analyzing
        $message->status = Message::STATUS_ANALYZING;
        $message->save(false);
        
        $analysisResult = Yii::$app->cryptoAnalyzer->analyzeMessage($message->id);
        
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            // Log the event
            Log::log(
                'Message Analysis Done',
                'Uploaded Message successfully analyzed',
                LogType::API,
                [$message]
            );

            return [
                'success' => true,
                'analysis' => $analysisResult,
                'redirect_url' => \yii\helpers\Url::to(['view', 'id' => $message->id])
            ];
        }
        
        Yii::$app->session->setFlash('success', 'Analysis completed successfully.');
        return $this->redirect(['view', 'id' => $message->id]);
    }

    public function actionExportDossier($id)
    {
        $generator = new \app\components\DossierGenerator();
        $filename = $generator->generate($id);
        
        if ($filename) {
            $path = Yii::getAlias('@webroot/uploads/dossiers/') . $filename;
            if (file_exists($path)) {

                // Log the event
                Log::log(
                    'Generated a Dossier Report',
                    'Generated a report',
                    LogType::INFO,
                    $filename
                );

                return Yii::$app->response->sendFile($path, $filename);
            }
        }
        
        Yii::$app->session->setFlash('error', 'Failed to generate dossier.');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionUpload()
    {
        $model = new Message();
        return $this->render('upload', ['model' => $model]);
    }

    public function actionAjaxUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Message();
        $model->status = Message::STATUS_PENDING;
        $model->intercepted_at = date('Y-m-d H:i:s');

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->file = UploadedFile::getInstance($model, 'file');

            if ($model->file) {
                $model->file_type = $this->getFileType($model->file->extension);
                
                $uploadDir = Yii::getAlias('@webroot/uploads/');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = time() . '_' . $model->file->baseName . '.' . $model->file->extension;
                $filePath = $uploadDir . $fileName;
                
                if ($model->file_type === 'text') {
                    $model->encrypted_content = file_get_contents($model->file->tempName);
                }

                if ($model->file->saveAs($filePath)) {
                    $model->file_path = realpath($filePath);
                }
            }

            if ($model->save()) {

                 // Log the event
                Log::log(
                    'Uploaded a message',
                    'Uploaded a message for analysis',
                    LogType::INFO,
                    $model
                );

                $analysisResult = Yii::$app->cryptoAnalyzer->analyzeMessage($model->id);
                
                return [
                    'success' => true,
                    'message_id' => $model->id,
                    'redirect_url' => \yii\helpers\Url::to(['view', 'id' => $model->id]),
                    'analysis' => $analysisResult
                ];
            }
        }

        return ['success' => false, 'errors' => $model->errors];
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Delete associated files if they exist
        if ($model->file_path && file_exists($model->file_path)) {
            @unlink($model->file_path);
        }
        
        $model->delete();

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

                // Log the event
                Log::log(
                    'deleted a message',
                    'deleted an analyzed message',
                    LogType::RECORD_CHANGE,
                    $model ?? NULL
                );

            return ['success' => true];
        }

        return $this->redirect(['index']);
    }

    private function getFileType($extension)
    {
        $extension = strtolower($extension);
        if (in_array($extension, ['png', 'jpg', 'jpeg'])) return 'image';
        if (in_array($extension, ['mp3', 'wav'])) return 'audio';
        return 'text';
    }

    protected function findModel($id)
    {
        if (($model = Message::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
