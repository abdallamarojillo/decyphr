<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class AuditLog extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%audit_logs}}';
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
            [['action'], 'required'],
            [['user_id', 'entity_id'], 'integer'],
            [['details'], 'string'],
            [['created_at'], 'safe'],
            [['action'], 'string', 'max' => 100],
            [['entity_type'], 'string', 'max' => 50],
            [['ip_address'], 'string', 'max' => 45],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'action' => 'Action',
            'entity_type' => 'Entity Type',
            'entity_id' => 'Entity ID',
            'details' => 'Details',
            'ip_address' => 'IP Address',
            'created_at' => 'Created At',
        ];
    }

    public static function log($action, $entityType = null, $entityId = null, $details = null)
    {
        $log = new self();
        $log->action = $action;
        $log->entity_type = $entityType;
        $log->entity_id = $entityId;
        $log->details = is_array($details) ? json_encode($details) : $details;
        $log->ip_address = Yii::$app->request->userIP;
        $log->save();
    }

    public function getDetailsArray()
    {
        return json_decode($this->details, true) ?? [];
    }
}
