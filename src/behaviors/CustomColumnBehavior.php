<?php

namespace boost\structureplus\behaviors;


// Define a custom behavior to handle the `customColumn` property
use yii\base\Behavior;

class CustomColumnBehavior extends Behavior
{
    public function canGetProperty($name, $checkVars = true): bool
    {
        return $name === 'customColumn' || parent::canGetProperty($name, $checkVars);
    }

    public function canSetProperty($name, $checkVars = true): bool
    {
        return $name === 'customColumn' || parent::canSetProperty($name, $checkVars);
    }

    public function __get($name)
    {
        if ($name === 'customColumn') {
            // Return a default or computed value for `customColumn`
            return null;
        }

        return parent::__get($name);
    }
}