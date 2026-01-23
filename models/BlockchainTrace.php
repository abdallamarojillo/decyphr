<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class BlockchainTrace extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%blockchain_traces}}';
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
            [['blockchain', 'address'], 'required'],
            [['entity_id'], 'integer'],
            [['amount'], 'number'],
            [['metadata'], 'string'],
            [['timestamp', 'created_at'], 'safe'],
            [['blockchain'], 'string', 'max' => 50],
            [['address', 'transaction_hash'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'entity_id' => 'Entity ID',
            'blockchain' => 'Blockchain',
            'address' => 'Address',
            'transaction_hash' => 'Transaction Hash',
            'amount' => 'Amount',
            'timestamp' => 'Timestamp',
            'metadata' => 'Metadata',
            'created_at' => 'Created At',
        ];
    }

    public function getEntity()
    {
        return $this->hasOne(Entity::class, ['id' => 'entity_id']);
    }

    public function getMetadataArray()
    {
        return json_decode($this->metadata, true) ?? [];
    }
}
