<?php

namespace boost\structureplus\events;

use boost\structureplus\behaviors\StructurePlusBehavior;
use craft\elements\Entry;
use yii\base\Event;

class DefineBehaviorsEvent
{
    public static function register(): void
    {

        // Attach the behavior to the `Entry` class
        Event::on(
            Entry::class,
            Entry::EVENT_DEFINE_BEHAVIORS,
            function (Event $event) {
                $event->sender->attachBehavior('structurePlusBehavior', StructurePlusBehavior::class);
            }
        );

    }
}