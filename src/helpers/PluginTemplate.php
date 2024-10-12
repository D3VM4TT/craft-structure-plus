<?php

namespace boost\structureplus\helpers;

use boost\structureplus\StructurePlus;
use Craft;
use craft\helpers\Template;
use craft\web\View;
use yii\base\Exception;

class PluginTemplate
{
    /**
     * Render a plugin template
     *
     * @param string      $templatePath
     * @param array       $params
     *
     * @return string
     */
    public static function  renderPluginTemplate(
        string $templatePath,
        array $params = [],
    ): string {
        $template = StructurePlus::HANDLE . '/' . $templatePath;
        $oldMode = Craft::$app->view->getTemplateMode();
        // Look for the template on the frontend first
        try {
            $templateMode = View::TEMPLATE_MODE_CP;
            if (Craft::$app->view->doesTemplateExist($template, View::TEMPLATE_MODE_SITE)) {
                $templateMode = View::TEMPLATE_MODE_SITE;
            }
            Craft::$app->view->setTemplateMode($templateMode);
        } catch (Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }

        // Render the template with our vars passed in
        try {
            $htmlText = Craft::$app->view->renderTemplate($template, $params);
        } catch (\Exception $e) {
            $htmlText = Craft::t(
                StructurePlus::HANDLE,
                'Error rendering `{template}` -> {error}',
                ['template' => $templatePath, 'error' => $e->getMessage()]
            );
            Craft::error($htmlText, __METHOD__);
        }

        // Restore the old template mode
        try {
            Craft::$app->view->setTemplateMode($oldMode);
        } catch (Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }

        return Template::raw($htmlText);
    }
}