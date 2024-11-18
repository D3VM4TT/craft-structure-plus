<?php

namespace boost\structureplus\events;

use boost\structureplus\StructurePlus;
use craft\services\UserPermissions;
use craft\events\RegisterUserPermissionsEvent;
use yii\base\Event;

class PermissionsEvent
{
    const PERMISSION_ACCESS_PLUGIN = 'accessPlugin-' . StructurePlus::HANDLE;
    const PERMISSION_LINK_CHANNELS = StructurePlus::HANDLE . ":link-channels";
    const PERMISSION_SHOW_BUTTONS = StructurePlus::HANDLE . ":show-buttons";

    public static function register(): void
    {
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function (RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    'heading' => 'Structure Plus',
                    'permissions' => [
                        self::PERMISSION_ACCESS_PLUGIN => [
                            'label' => \Craft::t(StructurePlus::HANDLE, 'Access Structure Plus'),
                            'nested' => [
                                self::PERMISSION_LINK_CHANNELS => [
                                    'label' => \Craft::t(StructurePlus::HANDLE, 'Link Channels'),
                                ],
                                self::PERMISSION_SHOW_BUTTONS => [
                                    'label' => \Craft::t(StructurePlus::HANDLE, 'Show Buttons'),
                                ],
                            ],
                        ],
                    ],
                ];
            }
        );
    }
}