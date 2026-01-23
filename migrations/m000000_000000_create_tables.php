<?php

use yii\db\Migration;

class m000000_000000_create_tables extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%messages}}', [
            'id' => $this->primaryKey(),
            'source_id' => $this->integer(),
            'destination_id' => $this->integer(),
            'device_id' => $this->integer(),
            'encrypted_content' => $this->text(),
            'decrypted_content' => $this->text(),
            'encryption_type' => $this->string(100),
            'message_hash' => $this->string(64),
            'status' => $this->string(50)->notNull()->defaultValue('pending'),
            'analysis_notes' => $this->text(),
            'file_path' => $this->string(255),
            'file_type' => $this->string(50),
            'intercepted_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_status', '{{%messages}}', 'status');
        $this->createIndex('idx_intercepted_at', '{{%messages}}', 'intercepted_at');
        $this->createIndex('idx_source_dest', '{{%messages}}', ['source_id', 'destination_id']);

        $this->createTable('{{%entities}}', [
            'id' => $this->primaryKey(),
            'entity_code' => $this->string(50)->notNull()->unique(),
            'entity_type' => $this->string(50)->notNull()->defaultValue('unknown'),
            'name' => $this->string(255),
            'aliases' => $this->text(),
            'risk_score' => $this->decimal(5, 2)->defaultValue(0),
            'metadata' => $this->text(),
            'first_seen' => $this->dateTime()->notNull(),
            'last_seen' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_entity_type', '{{%entities}}', 'entity_type');
        $this->createIndex('idx_risk_score', '{{%entities}}', 'risk_score');
        $this->createIndex('idx_last_seen', '{{%entities}}', 'last_seen');

        $this->createTable('{{%analysis_results}}', [
            'id' => $this->primaryKey(),
            'message_id' => $this->integer()->notNull(),
            'analysis_type' => $this->string(50)->notNull(),
            'method' => $this->string(100)->notNull(),
            'confidence_score' => $this->decimal(5, 2)->notNull(),
            'findings' => $this->text()->notNull(),
            'patterns_detected' => $this->text(),
            'ai_insights' => $this->text(),
            'processing_time' => $this->decimal(10, 3)->notNull(),
            'analyzed_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_message_id', '{{%analysis_results}}', 'message_id');
        $this->createIndex('idx_analysis_type', '{{%analysis_results}}', 'analysis_type');
        $this->createIndex('idx_confidence', '{{%analysis_results}}', 'confidence_score');

        $this->createTable('{{%frequency_analysis}}', [
            'id' => $this->primaryKey(),
            'message_id' => $this->integer()->notNull(),
            'character_frequencies' => $this->text()->notNull(),
            'bigram_frequencies' => $this->text(),
            'trigram_frequencies' => $this->text(),
            'index_of_coincidence' => $this->decimal(10, 6),
            'entropy' => $this->decimal(10, 6),
            'suggested_cipher' => $this->string(100),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_freq_message_id', '{{%frequency_analysis}}', 'message_id');

        $this->createTable('{{%communication_links}}', [
            'id' => $this->primaryKey(),
            'source_entity_id' => $this->integer()->notNull(),
            'target_entity_id' => $this->integer()->notNull(),
            'message_count' => $this->integer()->defaultValue(0),
            'first_contact' => $this->dateTime()->notNull(),
            'last_contact' => $this->dateTime()->notNull(),
            'link_strength' => $this->decimal(5, 2)->defaultValue(0),
            'metadata' => $this->text(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_source_target', '{{%communication_links}}', ['source_entity_id', 'target_entity_id'], true);
        $this->createIndex('idx_link_strength', '{{%communication_links}}', 'link_strength');

        $this->createTable('{{%blockchain_traces}}', [
            'id' => $this->primaryKey(),
            'entity_id' => $this->integer(),
            'blockchain' => $this->string(50)->notNull(),
            'address' => $this->string(255)->notNull(),
            'transaction_hash' => $this->string(255),
            'amount' => $this->decimal(20, 8),
            'timestamp' => $this->dateTime(),
            'metadata' => $this->text(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_blockchain_entity_id', '{{%blockchain_traces}}', 'entity_id');
        $this->createIndex('idx_blockchain', '{{%blockchain_traces}}', 'blockchain');
        $this->createIndex('idx_address', '{{%blockchain_traces}}', 'address');

        $this->createTable('{{%audit_logs}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'action' => $this->string(100)->notNull(),
            'entity_type' => $this->string(50),
            'entity_id' => $this->integer(),
            'details' => $this->text(),
            'ip_address' => $this->string(45),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_audit_user_id', '{{%audit_logs}}', 'user_id');
        $this->createIndex('idx_audit_action', '{{%audit_logs}}', 'action');
        $this->createIndex('idx_audit_created_at', '{{%audit_logs}}', 'created_at');
    }

    public function safeDown()
    {
        $this->dropTable('{{%audit_logs}}');
        $this->dropTable('{{%blockchain_traces}}');
        $this->dropTable('{{%communication_links}}');
        $this->dropTable('{{%frequency_analysis}}');
        $this->dropTable('{{%analysis_results}}');
        $this->dropTable('{{%entities}}');
        $this->dropTable('{{%messages}}');
    }
}
