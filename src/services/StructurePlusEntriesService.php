<?php

namespace boost\structureplus\services;

use Craft;
use craft\db\Query;

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


}