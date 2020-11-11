<?php
/**
 * Portfolios plugin for Craft CMS 3.x
 *
 * Connect to Kirby Portfolios.
 *
 * @link      https://niklassonnenschein.de
 * @copyright Copyright (c) 2019 Niklas Sonnenschein
 */

namespace hfg\portfolios\assetbundles\portfolios;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Niklas Sonnenschein
 * @package   Portfolios
 * @since     1.0.0
 */
class PortfoliosAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@hfg/portfolios/assetbundles/portfolios/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Portfolios.js',
        ];

        $this->css = [
            'css/Portfolios.css',
        ];

        parent::init();
    }
}
