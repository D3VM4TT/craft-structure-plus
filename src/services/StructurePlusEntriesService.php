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

    public function getHiddenSectionHandlesForJavascript(): ?string
    {
        // Fetch a unique list of channel IDs linked via sp_channelId
        $hiddenChannelIds = $this->getAllRelatedChannels();
        // Fetch section handles for the channels
        $hiddenSectionHandles = [];
        foreach ($hiddenChannelIds as $channelId) {
            if ($channelId) {
                $section = Craft::$app->entries->getSectionById($channelId);
                if ($section) {
                    $hiddenSectionHandles[] = $section->handle;
                }
            }
        }

        return json_encode($hiddenSectionHandles);
    }


}