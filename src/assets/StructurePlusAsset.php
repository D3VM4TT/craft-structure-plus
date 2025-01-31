<?php

namespace boost\structureplus\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class StructurePlusAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__ . '/dist';
        $this->depends = [CpAsset::class];

        $this->js = ['hideSources.js'];

        parent::init();
    }
}
