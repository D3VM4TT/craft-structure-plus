<?php

namespace boost\structureplus;

use boost\structureplus\assets\StructurePlusAsset;
use boost\structureplus\behaviors\StructurePlusBehavior;
use boost\structureplus\events\DefineBehaviorsEvent;
use boost\structureplus\events\DefineAttributeHtml;
use boost\structureplus\events\DefineSidebarHtmlEvent;
use boost\structureplus\events\PermissionsEvent;
use boost\structureplus\services\StructurePlusEntriesService;
use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\RegisterElementTableAttributesEvent;
use craft\models\Section;
use craft\web\View;
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

    /* TODO: Create a settings page where the user can show/hide certain sources  */

    public const HANDLE = 'structure-plus';

    const DB_FIELD_CHANNEL_ID = 'sp_channelId';

    public string $schemaVersion = '1.0.0';

    private StructurePlusEntriesService $structurePlusEntriesService;

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

        // REGISTER SERVICES
        $this->setComponents([
            'structurePlusEntry' => StructurePlusEntriesService::class,
        ]);

        $this->structurePlusEntriesService = self::getInstance()->structurePlusEntry;
    }

    private function attachEventHandlers(): void
    {


        // Pass hidden section handles to JavaScript
        Event::on(View::class, View::EVENT_BEFORE_RENDER_TEMPLATE, function (Event $event) {
            if (Craft::$app->getRequest()->isCpRequest) {
                $jsonHiddenSections = $this->getHiddenSectionHandlesForJavascript();
                Craft::$app->getView()->registerJs("window.hiddenSections = $jsonHiddenSections;", View::POS_HEAD);
            }
        });

        Craft::$app->getView()->registerAssetBundle(StructurePlusAsset::class);

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

                // *** Add the "View all" & "Add new +" buttons ***
                DefineAttributeHtml::register();

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

                        // TODO: Update all entry id references to use the cannonical id
                        if ($channelId !== null) {
                            $this->structurePlusEntriesService->updateEntrySPChannelId($entry->getCanonicalId(), $channelId);
                        }
                    }
                );
            }
        }

        PermissionsEvent::register();
    }

    private function getHiddenSectionHandlesForJavascript()
    {
        // Fetch a unique list of channel IDs linked via sp_channelId
        $hiddenChannelIds = $this->structurePlusEntriesService->getAllRelatedChannels();
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
