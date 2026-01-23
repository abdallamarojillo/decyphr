<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class Message extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_ANALYZING = 'analyzing';
    const STATUS_ANALYZED = 'analyzed';
    const STATUS_DECRYPTED = 'decrypted';
    const STATUS_FAILED = 'failed';

    public $file;

    public static function tableName()
    {
        return '{{%messages}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression($this->db->driverName === 'sqlite' ? "datetime('now')" : 'NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['intercepted_at', 'status'], 'required'],
            [['encrypted_content', 'decrypted_content', 'analysis_notes', 'file_path', 'file_type'], 'string'],
            [['intercepted_at', 'created_at', 'updated_at'], 'safe'],
            [['source_id', 'destination_id', 'device_id'], 'integer'],
            [['status'], 'string', 'max' => 50],
            [['encryption_type'], 'string', 'max' => 100],
            [['message_hash'], 'string', 'max' => 64],
            [['file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, mp3, wav, txt', 'checkExtensionByMimeType' => false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'source_id' => 'Source Entity',
            'destination_id' => 'Destination Entity',
            'device_id' => 'Device ID',
            'encrypted_content' => 'Encrypted Content',
            'decrypted_content' => 'Decrypted Content',
            'encryption_type' => 'Encryption Type',
            'message_hash' => 'Message Hash',
            'status' => 'Status',
            'analysis_notes' => 'Analysis Notes',
            'file_path' => 'File Path',
            'file_type' => 'File Type',
            'intercepted_at' => 'Intercepted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getSource()
    {
        return $this->hasOne(Entity::class, ['id' => 'source_id']);
    }

    public function getDestination()
    {
        return $this->hasOne(Entity::class, ['id' => 'destination_id']);
    }

    public function getAnalysisResults()
    {
        return $this->hasMany(AnalysisResult::class, ['message_id' => 'id']);
    }

    public function getFrequencyAnalysis()
    {
        return $this->hasOne(FrequencyAnalysis::class, ['message_id' => 'id']);
    }
}
