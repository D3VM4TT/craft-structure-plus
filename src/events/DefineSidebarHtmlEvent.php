<?php

namespace boost\structureplus\events;

use boost\structureplus\helpers\PluginTemplate;
use boost\structureplus\StructurePlus;
use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\events\DefineHtmlEvent;
use craft\models\Section;
use yii\base\Event;

class DefineSidebarHtmlEvent
{
    public static function register(): void
    {

        // *** ADD HTML TO SIDEBAR ***
        Event::on(
            Entry::class,
            Element::EVENT_DEFINE_SIDEBAR_HTML,
            function (DefineHtmlEvent $event) {

                /** @var Entry $entry */
                $entry = $event->sender ?? null;

                $channelId = (new \craft\db\Query())
                    ->select([StructurePlus::DB_FIELD_CHANNEL_ID])
                    ->from('{{%entries}}')
                    ->where(['id' => $entry->id])
                    ->scalar();


                if ($entry->section->type !== Section::TYPE_STRUCTURE) {
                    return;
                }

                Craft::debug(
                    'Entry::EVENT_DEFINE_SIDEBAR_HTML',
                    __METHOD__
                );

                $currentUser = Craft::$app->getUser()->getIdentity();
                if (!$currentUser) {
                    return; // No logged-in user, skip rendering.
                }

                // Filter channels that the user has permission to view.
                $channels = array_filter(
                    Craft::$app->entries->getAllSections(),
                    function ($section) use ($currentUser) {
                        return $section->type === Section::TYPE_CHANNEL
                            && Craft::$app->getUser()->checkPermission("viewEntries:{$section->uid}");
                    }
                );


                $channelOptions = [0 => 'Select a channel...'];

                foreach ($channels as $channel) {
                    $channelOptions[$channel->id] = $channel->name;
                }

                $html = '';

                if ($entry !== null && $entry->uri !== null) {
                    $html .= PluginTemplate::renderPluginTemplate('_sidebars/channel-select.twig', [
                        "options" => $channelOptions,
                        "selectedChannel" => $channelId,
                    ]);
                }

                $event->html = $html . $event->html;
            }
        );

    }
}