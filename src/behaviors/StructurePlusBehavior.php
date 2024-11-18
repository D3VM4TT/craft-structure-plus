<?php

namespace boost\structureplus\behaviors;


// Define a custom behavior to handle the `customColumn` property
use yii\base\Behavior;

class StructurePlusBehavior extends Behavior
{
   const PROPERTY_NAME = 'structurePlus';
    public function canGetProperty($name, $checkVars = true): bool
    {
        return $name === self::PROPERTY_NAME || parent::canGetProperty($name, $checkVars);
    }

    public function canSetProperty($name, $checkVars = true): bool
    {
        return $name === self::PROPERTY_NAME || parent::canSetProperty($name, $checkVars);
    }

    public function __get($name)
    {
        if ($name === self::PROPERTY_NAME) {
            // Return a default or computed value for `customColumn`
            return null;
        }

        return parent::__get($name);
    }
}