<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class CommunicationLink extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%communication_links}}';
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
            [['source_entity_id', 'target_entity_id', 'first_contact', 'last_contact'], 'required'],
            [['source_entity_id', 'target_entity_id', 'message_count'], 'integer'],
            [['link_strength'], 'number'],
            [['metadata'], 'string'],
            [['first_contact', 'last_contact', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'source_entity_id' => 'Source Entity',
            'target_entity_id' => 'Target Entity',
            'message_count' => 'Message Count',
            'first_contact' => 'First Contact',
            'last_contact' => 'Last Contact',
            'link_strength' => 'Link Strength',
            'metadata' => 'Metadata',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getSourceEntity()
    {
        return $this->hasOne(Entity::class, ['id' => 'source_entity_id']);
    }

    public function getTargetEntity()
    {
        return $this->hasOne(Entity::class, ['id' => 'target_entity_id']);
    }

    public function getMetadataArray()
    {
        return json_decode($this->metadata, true) ?? [];
    }
}
