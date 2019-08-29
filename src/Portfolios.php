<?php
/**
 * Portfolios plugin for Craft CMS 3.x
 *
 * Connect to Kirby Portfolios.
 *
 * @link      https://niklassonnenschein.de
 * @copyright Copyright (c) 2019 Niklas Sonnenschein
 */

namespace hfg\portfolios;

use hfg\portfolios\models\Settings;
use hfg\portfolios\fields\Projects as ProjectsField;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class Portfolios
 *
 * @author    Niklas Sonnenschein
 * @package   Portfolios
 * @since     1.0.0
 *
 */
class Portfolios extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Portfolios
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->_setComponents();
        $this->_registerSiteRoutes();
        $this->_registerCpRoutes();
        $this->_registerFieldTypes();

        Craft::info(
            Craft::t(
                'portfolios',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Private Methods
    // =========================================================================

    private function _setComponents()
    {
        $this->setComponents([
            "restClient" => \hfg\portfolios\services\RestClientService::class
        ]);
    }

    private function _registerSiteRoutes()
    {
      Event::on(
          UrlManager::class,
          UrlManager::EVENT_REGISTER_SITE_URL_RULES,
          function (RegisterUrlRulesEvent $event) {
              $event->rules['siteActionTrigger1'] = 'portfolios/explorer';
          }
      );
    }

    private function _registerCpRoutes()
    {
      Event::on(
          UrlManager::class,
          UrlManager::EVENT_REGISTER_CP_URL_RULES,
          function (RegisterUrlRulesEvent $event) {
              $event->rules['cpActionTrigger1'] = 'portfolios/explorer/get-modal';
              $event->rules['cpActionTrigger2'] = 'portfolios/explorer/get-projects';
              $event->rules['cpActionTrigger3'] = 'portfolios/explorer/get-data';
          }
      );
    }

    private function _registerFieldTypes()
    {
      Event::on(
          Fields::class,
          Fields::EVENT_REGISTER_FIELD_TYPES,
          function (RegisterComponentTypesEvent $event) {
              $event->types[] = ProjectsField::class;
          }
      );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate(
            'portfolios/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
