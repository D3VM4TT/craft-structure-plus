<?php

namespace boost\structureplus\services;

use boost\structureplus\StructurePlus;
use Craft;
use craft\db\Query;
use yii\db\Exception;

class StructurePlusEntriesService
{

    public function getAllRelatedChannels(): array
    {
        return (new Query())
            ->select(['sp_channelId'])
            ->distinct()
            ->from('{{%entries}}')
            ->column();
    }

    /**
     * @throws Exception
     */
    public function updateEntrySPChannelId($entryId, $channelId): void
    {
        Craft::$app->db->createCommand()
            ->update(
                '{{%entries}}',
                [StructurePlus::DB_FIELD_CHANNEL_ID => $channelId],
                ['id' => $entryId]
            )
            ->execute();
    }


}