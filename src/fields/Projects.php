<?php
/**
 * Portfolios plugin for Craft CMS 3.x
 *
 * Connect to Kirby Portfolios.
 *
 * @link      https://niklassonnenschein.de
 * @copyright Copyright (c) 2019 Niklas Sonnenschein
 */

namespace hfg\portfolios\fields;

use hfg\portfolios\Portfolios;
use hfg\portfolios\assetbundles\projectsfield\ProjectsFieldAsset;
//use hfg\portfolios\models\PortfolioModel;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use craft\db\mysql\Schema;
use craft\helpers\Json;

/**
 * @author    Niklas Sonnenschein
 * @package   Portfolios
 * @since     1.0.0
 */
class Projects extends Field
{
    // Public Properties
    // =========================================================================
    public $columnType = "MEDIUMTEXT";


    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('portfolios', 'Projects');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_LONGTEXT;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return parent::serializeValue($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'portfolios/_components/fields/Projects_settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();

        // Register our asset bundle
        $view->registerAssetBundle(ProjectsFieldAsset::class);

        // Get our id and namespace
        $id = $view->formatInputId($this->handle);
        $namespacedId = $view->namespaceInputId($id);

        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id' => $id,
            'name' => $this->handle,
            'namespace' => $namespacedId,
            'prefix' => $view->namespaceInputId(''),
            ];
        $jsonVars = Json::encode($jsonVars);
        $view->registerJs('new Portfolios.Field("'.$view->namespaceInputId($id).'-field")');
        //Craft::$app->getView()->registerJs("$('#{$namespacedId}-field').PortfoliosProjects(" . $jsonVars . ");");

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'portfolios/_components/fields/Projects_input',
            [
                'name' => $this->handle,
                'value' => $value["projects"],
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
            ]
        );
    }
}
