<?php
/**
 * Portfolios plugin for Craft CMS 3.x
 *
 * Connect to Kirby Portfolios.
 *
 * @link      https://niklassonnenschein.de
 * @copyright Copyright (c) 2019 Niklas Sonnenschein
 */

namespace hfg\portfolios\controllers;

use hfg\portfolios\Portfolios;
use hfg\portfolios\services\RestClientService;

use Craft;
use craft\web\Controller;
use yii\web\Response;

/**
 * @author    Niklas Sonnenschein
 * @package   Portfolios
 * @since     1.0.0
 */
class ExplorerController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * @return Response
     */
    public function actionGetModal()
    {
        $this->requireAcceptsJson();

        $namespaceInputId = Craft::$app->getRequest()->getBodyParam('namespaceInputId');

        return $this->asJson([
          'success' => true,
          'html' => Craft::$app->getView()->renderTemplate('portfolios/_elements/explorer', [
            'namespaceInputId' => $namespaceInputId,
            'gateways' => Portfolios::$plugin->getSettings()->gateways
          ])
        ]);
    }

    /**
     * Get all projects
     * 
     * @return Response
     */
     public function actionGetProjects(): Response
     {
       $this->requireAcceptsJson();

       $gatewayHandle = Craft::$app->getRequest()->getParam("gateway");
       $method = Craft::$app->getRequest()->getParam("method");
       $options = Craft::$app->getRequest()->getParam("options", []);

       $projects = $this->_getProjects($gatewayHandle, $method, $options);

       $html = Craft::$app->getView()->renderTemplate("portfolios/_elements/projects", [
          "projects" => $projects
       ]);

       return $this->asJson([
         "html" => $html
       ]);
     }

     /**
      * Get data from a single project
      * 
      * @return Response
      */
      public function actionGetData(): Response
      {
        $this->requireAcceptsJson();

        $url      = Craft::$app->getRequest()->getParam("url") . ".json";
        $response = Portfolios::$plugin->restClient->get($url, array(), false);
        
        if ($response["statusCode"] === 200) {
          return $this->asJson($response["body"]);
        }

        return null;
      }




     /**
      * Get Gateway URL by handle
      * 
      * @param string $gatewayHandle Gateway ID as defined in plugin settings page
      *
      * @return string $gatewayUrl
      */
     private function _getGatewayUrl($gatewayHandle)
     {
       $gateways = Portfolios::$plugin->getSettings()->gateways;

       foreach ($gateways as $gateway) {
         if($gateway["handle"] == $gatewayHandle) {
           return $gateway["url"];
         }
       }

       return null;
     }

     /**
      * Fetch projects from Gatway JSON
      * 
      * @param string $gatewayHandle
      * @param string $method POST or GET request
      * @param array  $options 
      *
      * @return array $response
      */
     private function _getProjects($gatewayHandle, $method = "get", $options) {
       $url = $this->_getGatewayUrl($gatewayHandle);
       $response = Portfolios::$plugin->restClient->get($url, array(), false);

       if ($response["statusCode"] === 200) {
         return json_decode($response["body"], true);
       }

        return null;
     }
}
