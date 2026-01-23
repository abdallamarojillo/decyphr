<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class FrequencyAnalysis extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%frequency_analysis}}';
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
            [['message_id', 'character_frequencies'], 'required'],
            [['message_id'], 'integer'],
            [['character_frequencies', 'bigram_frequencies', 'trigram_frequencies'], 'string'],
            [['index_of_coincidence', 'entropy'], 'number'],
            [['suggested_cipher'], 'string', 'max' => 100],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message_id' => 'Message ID',
            'character_frequencies' => 'Character Frequencies',
            'bigram_frequencies' => 'Bigram Frequencies',
            'trigram_frequencies' => 'Trigram Frequencies',
            'index_of_coincidence' => 'Index of Coincidence',
            'entropy' => 'Entropy',
            'suggested_cipher' => 'Suggested Cipher',
            'created_at' => 'Created At',
        ];
    }

    public function getMessage()
    {
        return $this->hasOne(Message::class, ['id' => 'message_id']);
    }

    public function getCharacterFrequenciesArray()
    {
        return json_decode($this->character_frequencies, true) ?? [];
    }

    public function getBigramFrequenciesArray()
    {
        return json_decode($this->bigram_frequencies, true) ?? [];
    }

    public function getTrigramFrequenciesArray()
    {
        return json_decode($this->trigram_frequencies, true) ?? [];
    }
}
