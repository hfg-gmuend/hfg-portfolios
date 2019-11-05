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
       $pagination = Craft::$app->getRequest()->getParam("pagination");

       $projects = $this->_getProjects($gatewayHandle, $method, $options, $pagination);

       $html = Craft::$app->getView()->renderTemplate("portfolios/_elements/projects", [
          "projects" => $projects
       ]);

       return $this->asJson([
         "html" => $html
       ]);
     }

     /**
      * Get Gateway URL by handle
      * 
      * @param string $gatewayHandle Gateway ID as defined in plugin settings page
      *
      * @return string $gatewayUrl
      */
     private function _getGatewayUrl($gatewayHandle, $pagination)
     {
       $gateways = Portfolios::$plugin->getSettings()->gateways;

       foreach ($gateways as $gateway) {
         if($gateway["handle"] == $gatewayHandle) {
           if ($pagination) {
            return $gateway["url"] . "/page:" . $pagination;
           } else {
             return $gateway["url"];
           }
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
     private function _getProjects($gatewayHandle, $method = "get", $options, $pagination = false) {
       if ($method == "get") {
        $url = $this->_getGatewayUrl($gatewayHandle, $pagination);
        $response = Portfolios::$plugin->restClient->get($url, array(), false);

        if ($response["statusCode"] === 200) {
          return json_decode($response["body"], true);
        }
       } else if ($method == "search") {
        $responses = [];
        
        for ($i = 1; $i < 10; $i++) {
          $url = $this->_getGatewayUrl($gatewayHandle, $i);
          $response = Portfolios::$plugin->restClient->get($url, array(), false);

          if ($response["statusCode"] === 200) {
            $resultSet = json_decode($response["body"], true);

            for($j = 0; $j < count($resultSet); $j++) {
              if(stripos($resultSet[$j]["title"], $options["q"]) !== false
                || stripos($resultSet[$j]["course"], $options["q"]) !== false
                || stripos($resultSet[$j]["period"], $options["q"]) !== false
                || stripos($resultSet[$j]["year"], $options["q"]) !== false) {
                $responses[] = $resultSet[$j];
              }
            }
          }
        }

        return $responses;
       }

        return null;
     }
}
