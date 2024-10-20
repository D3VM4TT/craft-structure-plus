<?php

namespace boost\structureplus\migrations;

use Craft;
use craft\db\Migration;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%entries}}', 'channelId')) {
            $this->addColumn('{{%entries}}', 'channelId', $this->integer()->after('sectionId'));

            // Add a foreign key relation to the sections table
            $this->addForeignKey(
                null,
                '{{%entries}}',
                'channelId',
                '{{%sections}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        // Remove the 'channelId' column if it exists
        if ($this->db->columnExists('{{%entries}}', 'channelId')) {
            $this->dropColumn('{{%entries}}', 'channelId');
        }

        return true;
    }
}
