<?php

namespace boost\structureplus;

use boost\structureplus\behaviors\StructurePlusBehavior;
use boost\structureplus\events\PermissionsEvent;
use boost\structureplus\helpers\PluginTemplate;
use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\DefineHtmlEvent;
use craft\events\RegisterElementTableAttributesEvent;
use craft\events\DefineAttributeHtmlEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\models\Section;
use craft\services\UserPermissions;
use yii\base\Event;

/**
 * Structure Plus plugin
 *
 * @method static StructurePlus getInstance()
 * @author Boost <matthewdejager5@gmail.com>
 * @copyright Boost
 * @license https://craftcms.github.io/license/ Craft License
 */
class StructurePlus extends Plugin
{

    public const HANDLE = 'structure-plus';

    const DB_FIELD_CHANNEL_ID = 'sp_channelId';

    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function () {
            $this->attachEventHandlers();
        });
    }

    private function attachEventHandlers(): void
    {

        if (Craft::$app->getUser()->checkPermission(PermissionsEvent::PERMISSION_ACCESS_PLUGIN)) {
            if (Craft::$app->getUser()->checkPermission(PermissionsEvent::PERMISSION_SHOW_BUTTONS)) {
                // *** ADD CUSTOM COLUMN TO ENTRY TABLE ***
                Event::on(
                    Element::class,
                    Element::EVENT_REGISTER_TABLE_ATTRIBUTES,
                    function (RegisterElementTableAttributesEvent $event) {
                        $event->tableAttributes[StructurePlusBehavior::PROPERTY_NAME] = ['label' => 'Structure Plus'];
                        $event->handled = true;
                    });

                //  *** ADD BUTTONS ***
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
                            ->select([self::DB_FIELD_CHANNEL_ID])
                            ->from('{{%entries}}')
                            ->where(['id' => $entry->id])
                            ->scalar();

                        $relatedChannel = null;

                        if ($channelId !== null) {
                            $relatedChannel = Craft::$app->entries->getSectionById($channelId);
                        }

                        $e->html = '';

                        if ($relatedChannel instanceof Section) {
                            $e->html = PluginTemplate::renderPluginTemplate('_sidebars/admin-buttons.twig', [
                                "relatedChannel" => $relatedChannel->handle
                            ]);
                        }
                    }
                );


                // Attach the behavior to the `Entry` class
                Event::on(
                    Entry::class,
                    Entry::EVENT_DEFINE_BEHAVIORS,
                    function (Event $event) {
                        $event->sender->attachBehavior('structurePlusBehavior', StructurePlusBehavior::class);
                    }
                );
            }

            if (Craft::$app->getUser()->checkPermission(PermissionsEvent::PERMISSION_LINK_CHANNELS)) {
                // *** ADD HTML TO SIDEBAR ***
                Event::on(
                    Entry::class,
                    Element::EVENT_DEFINE_SIDEBAR_HTML,
                    function (DefineHtmlEvent $event) {

                        /** @var Entry $entry */
                        $entry = $event->sender ?? null;

                        $channelId = (new \craft\db\Query())
                            ->select([self::DB_FIELD_CHANNEL_ID])
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

                // *** SAVE CHANNEL ID TO ENTRY ***
                Event::on(
                    Entry::class,
                    Element::EVENT_AFTER_SAVE,
                    function (Event $event) {

                        /** @var Entry $entry */
                        $entry = $event->sender;

                        if (!$entry instanceof Entry) {
                            return;
                        }

                        $section = $entry->section;

                        if (!$section instanceof Section || $section->type !== Section::TYPE_STRUCTURE) {
                            return;
                        }

                        // Only target Structure entries

                        $channelId = Craft::$app->request->getBodyParam('channelId');

                        if ($channelId !== null) {
                            Craft::$app->db->createCommand()
                                ->update(
                                    '{{%entries}}',
                                    [self::DB_FIELD_CHANNEL_ID => $channelId],
                                    ['id' => $entry->id]
                                )
                                ->execute();
                        }
                    }
                );
            }
        }

        PermissionsEvent::register();
    }

}
