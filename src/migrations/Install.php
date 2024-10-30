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
        if (!$this->db->columnExists('{{%entries}}', 'sp_channelId')) {
            $this->addColumn('{{%entries}}', 'sp_channelId', $this->integer()->after('sectionId'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        if ($this->db->columnExists('{{%entries}}', 'sp_channelId')) {
            $this->dropColumn('{{%entries}}', 'sp_channelId');
        }

        return true;
    }
}
