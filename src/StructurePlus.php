<?php

namespace boost\structureplus;

use boost\structureplus\behaviors\StructurePlusBehavior;
use boost\structureplus\events\DefineBehaviorsEvent;
use boost\structureplus\events\DefineSidebarHtmlEvent;
use boost\structureplus\events\PermissionsEvent;
use boost\structureplus\helpers\PluginTemplate;
use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\RegisterElementTableAttributesEvent;
use craft\events\DefineAttributeHtmlEvent;
use craft\models\Section;
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


                DefineBehaviorsEvent::register();
            }

            if (Craft::$app->getUser()->checkPermission(PermissionsEvent::PERMISSION_LINK_CHANNELS)) {

                DefineSidebarHtmlEvent::register();

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
