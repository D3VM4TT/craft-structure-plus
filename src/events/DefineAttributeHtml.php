<?php

namespace boost\structureplus\events;

use boost\structureplus\behaviors\StructurePlusBehavior;
use boost\structureplus\helpers\PluginTemplate;
use boost\structureplus\StructurePlus;
use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\events\DefineAttributeHtmlEvent;
use craft\models\Section;
use yii\base\Event;

class DefineAttributeHtml
{
    public static function register(): void
    {

        // Attach the behavior to the `entry` class
        Event::on(
            Entry::class,
            Element::EVENT_DEFINE_ATTRIBUTE_HTML,
            function (DefineAttributeHtmlEvent $e) {
                if ($e->attribute !== StructurePlusBehavior::PROPERTY_NAME) {
                        return;
                    }

                    /** @var Entry $entry */
                    $entry = $e->sender;

                    // Fetch the channelId related to this entry
                    $channelId = (new \craft\db\Query())
                        ->select([StructurePlus::DB_FIELD_CHANNEL_ID])
                        ->from('{{%entries}}')
                        ->where(['id' => $entry->id])
                        ->scalar();

                    $relatedChannel = null;

                    if ($channelId !== null) {
                        $relatedChannel = Craft::$app->entries->getSectionById($channelId);
                    }

                    $e->html = '';

                    if ($relatedChannel instanceof Section) {
                        $sourceDisabled = false;
                        $sources = Craft::$app->elementSources->getSources(Entry::class, '', true);

                        foreach ($sources as $source) {
                            // Check if 'data' exists and is an array
                            if (isset($source['data']) && is_array($source['data'])) {
                                // Check if 'handle' exists in 'data'
                                if (isset($source['data']['handle']) && $source['data']['handle'] === $relatedChannel->handle) {
                                    $sourceDisabled = $source['disabled'] ?? null;
                                    break; // Exit loop as we've found the desired source
                                }
                            }
                        }

                        $e->html = PluginTemplate::renderPluginTemplate('_sidebars/admin-buttons.twig', [
                            "relatedChannel" => $relatedChannel->handle,
                            "sourceDisabled" => $sourceDisabled,
                        ]);
                }
            }
        );

    }
}