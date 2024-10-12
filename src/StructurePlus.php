<?php

namespace boost\structureplus;

use boost\structureplus\helpers\PluginTemplate;
use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\DefineHtmlEvent;
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

    public const string HANDLE = 'structure-plus';

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

        $this->attachEventHandlers();

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function() {
            // ...
        });
    }

    private function attachEventHandlers(): void
    {

        // Adds a new nav item to the control panel entry index
        Event::on(
            Element::class,
            Element::EVENT_REGISTER_SOURCES,
            function($event) {
                // Add a new source for Structure Plus
                $event->sources[] = [
                    'key' => 'structurePlus',
                    'label' => 'Structure Plus',
                    'criteria' => ['section' => 'pages'], // Adjust to your section
                    'defaultSort' => ['postDate', 'desc'],
                ];
        });

        Event::on(
            Element::class,
            Element::EVENT_REGISTER_TABLE_ATTRIBUTES,
            function(RegisterElementTableAttributesEvent $event) {
                $event->tableAttributes['customColumn'] = ['label' => 'Structure Plus'];
                $event->handled = true;
        });


        Event::on(
            Entry::class,
            Element::EVENT_DEFINE_ATTRIBUTE_HTML,
            function(DefineAttributeHtmlEvent $e) {
                if($e->attribute === 'customColumn'){
                    $e->html = '
                                <a href="https://craftcms.com" target="_blank">View All</a>
                                <br>
                                <a href="https://craftcms.com" target="_blank">+Add New</a>
                                ';
                }
            }
        );

        Event::on(
            Entry::class,
            Element::EVENT_DEFINE_SIDEBAR_HTML,
            function(DefineHtmlEvent $event) {

            Craft::debug(
                'Entry::EVENT_DEFINE_SIDEBAR_HTML',
                __METHOD__
            );

                $channels = array_filter(
                    Craft::$app->entries->getAllSections(),
                    fn($section) => $section->type === Section::TYPE_CHANNEL
                );

                // Create an associative array with handle as key and name as value
                $channelOptions = [];

                foreach ($channels as $channel) {
                    $channelOptions[$channel->handle] = $channel->name;
                }



                $html = '';

                /** @var Entry $entry */
                $entry = $event->sender ?? null;

                if ($entry !== null && $entry->uri !== null) {
                    $html .= PluginTemplate::renderPluginTemplate('_sidebars/channel-select.twig', ["options" => $channelOptions] );
                }

                $event->html = $html . $event->html;
            }
        );
    }

}
