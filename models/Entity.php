<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class Entity extends ActiveRecord
{
    const TYPE_PERSON = 'person';
    const TYPE_GROUP = 'group';
    const TYPE_DEVICE = 'device';
    const TYPE_UNKNOWN = 'unknown';

    public static function tableName()
    {
        return '{{%entities}}';
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
            [['entity_code', 'first_seen', 'last_seen'], 'required'],
            [['risk_score'], 'number'],
            [['aliases', 'metadata'], 'string'],
            [['first_seen', 'last_seen', 'created_at', 'updated_at'], 'safe'],
            [['entity_code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 255],
            [['entity_type'], 'in', 'range' => [self::TYPE_PERSON, self::TYPE_GROUP, self::TYPE_DEVICE, self::TYPE_UNKNOWN]],
            [['entity_code'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'entity_code' => 'Entity Code',
            'entity_type' => 'Type',
            'name' => 'Name',
            'aliases' => 'Aliases',
            'risk_score' => 'Risk Score',
            'metadata' => 'Metadata',
            'first_seen' => 'First Seen',
            'last_seen' => 'Last Seen',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getSentMessages()
    {
        return $this->hasMany(Message::class, ['source_id' => 'id']);
    }

    public function getReceivedMessages()
    {
        return $this->hasMany(Message::class, ['destination_id' => 'id']);
    }

    public function getOutgoingLinks()
    {
        return $this->hasMany(CommunicationLink::class, ['source_entity_id' => 'id']);
    }

    public function getIncomingLinks()
    {
        return $this->hasMany(CommunicationLink::class, ['target_entity_id' => 'id']);
    }

    public function getBlockchainTraces()
    {
        return $this->hasMany(BlockchainTrace::class, ['entity_id' => 'id']);
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_PERSON => 'Person',
            self::TYPE_GROUP => 'Group',
            self::TYPE_DEVICE => 'Device',
            self::TYPE_UNKNOWN => 'Unknown',
        ];
    }

    public function getRiskLevel()
    {
        if ($this->risk_score >= 75) {
            return 'Critical';
        } elseif ($this->risk_score >= 50) {
            return 'High';
        } elseif ($this->risk_score >= 25) {
            return 'Medium';
        } else {
            return 'Low';
        }
    }

    public function getRiskClass()
    {
        if ($this->risk_score >= 75) {
            return 'danger';
        } elseif ($this->risk_score >= 50) {
            return 'warning';
        } elseif ($this->risk_score >= 25) {
            return 'info';
        } else {
            return 'success';
        }
    }
}
