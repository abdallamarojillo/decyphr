<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class AnalysisResult extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%analysis_results}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => new Expression($this->db->driverName === 'sqlite' ? "datetime('now')" : 'NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['message_id', 'analysis_type', 'method', 'confidence_score', 'findings', 'processing_time', 'analyzed_at'], 'required'],
            [['message_id'], 'integer'],
            [['confidence_score', 'processing_time'], 'number'],
            [['findings', 'patterns_detected', 'ai_insights'], 'string'],
            [['analyzed_at', 'created_at'], 'safe'],
            [['analysis_type'], 'string', 'max' => 50],
            [['method'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message_id' => 'Message ID',
            'analysis_type' => 'Analysis Type',
            'method' => 'Method',
            'confidence_score' => 'Confidence Score',
            'findings' => 'Findings',
            'patterns_detected' => 'Patterns Detected',
            'ai_insights' => 'AI Insights',
            'processing_time' => 'Processing Time (s)',
            'analyzed_at' => 'Analyzed At',
            'created_at' => 'Created At',
        ];
    }

    public function getMessage()
    {
        return $this->hasOne(Message::class, ['id' => 'message_id']);
    }

    public function getFindingsArray()
    {
        return json_decode($this->findings, true) ?? [];
    }

    public function getPatternsArray()
    {
        return json_decode($this->patterns_detected, true) ?? [];
    }
}
