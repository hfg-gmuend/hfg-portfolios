<?php
/**
 * Portfolios plugin for Craft CMS 3.x
 *
 * Connect to Kirby Portfolios.
 *
 * @link      https://niklassonnenschein.de
 * @copyright Copyright (c) 2019 Niklas Sonnenschein
 */

namespace hfg\portfolios\assetbundles\projectsfield;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use hfg\portfolios\assetbundles\portfolios\PortfoliosAsset;

/**
 * @author    Niklas Sonnenschein
 * @package   Portfolios
 * @since     1.0.0
 */
class ProjectsFieldAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@hfg/portfolios/assetbundles/projectsfield/dist";

        $this->depends = [
            CpAsset::class,
            PortfoliosAsset::class
        ];

        $this->js = [
            'js/Projects.js',
        ];

        $this->css = [
            'css/Projects.css',
        ];

        parent::init();
    }
}
