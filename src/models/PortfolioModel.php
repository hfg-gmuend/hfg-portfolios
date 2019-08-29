<?php
/**
 * Portfolios plugin for Craft CMS 3.x
 *
 * Connect to Kirby Portfolios.
 *
 * @link      https://niklassonnenschein.de
 * @copyright Copyright (c) 2019 Niklas Sonnenschein
 */

namespace hfg\portfolios\models;

use hfg\portfolios\Portfolios;

use Craft;
use craft\base\Model;

/**
 * @author    Niklas Sonnenschein
 * @package   Portfolios
 * @since     1.0.0
 */
class PortfolioModel extends Model
{
    // Public Properties
    // =========================================================================

    public $projects = "";

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }
}
