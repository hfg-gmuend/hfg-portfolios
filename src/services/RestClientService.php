<?php

namespace hfg\portfolios\services;

use Craft;
use yii\base\Component;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

class RestClientService extends Component
{
  // Default request options
  private $_timeout = 30;
  private $_connectionTimeout = 2;
  private $_allowRedirects = true;

  // Time in seconds during which we store the responses in cache
  private $_cacheTtl = 3600;

  // HTTP Client
  private $_client;

  private function _getClient()
  {
    if (isset($this->_client)) {
      return $this->_client;
    }

    $this->_client = new Client([
      'cookies' => true
    ]);

    return $this->_client;
  }

  private function _getCacheId($method, $url, $params)
  {
    return "restclient_" . $method . "_" . $url . "_" . md5(json_encode($params));
  }

  private function _getFilesTempPath($fileNames)
  {
    if (!is_array($fileNames)) {
      return array();
    }

    $paths = array();
    foreach ($fileNames as $param => $file) {
      $paths[$param] = "@" . $file->getTempName();
    }

    return $paths;
  }

  /**
   * Build an HTTP request.
   * @param string $method The request's method (GET or POST)
   * @param string $url The URL to perform the request to
   * @param array $requestParams List of GET or POST parameters
   * @param array $options Additional options such as headers
   * 
   * @return object
   */

  private function _buildRequest($method, $url, $requestParams, $options = array())
  {
    //Obtain Guzzle instance
    $client = $this->_getClient();
    $request = null;

    //Request headers
    $requestHeaders = array(
      "X-Requested-With" => "XMLHttpRequest"
    );

    // Check if we need to send additional headers
    if (in_array("headers", array_keys($options))) {
      $requestHeaders = array_merge($requestHeaders, $options["headers"]);
    }

    //Define request options
    $requestOptions = array(
      "headers" => $requestHeaders,
      "allow_redirects" => [
        "max" => $this->_allowRedirects
      ],
      "connect_timeout" => $this->_connectionTimeout,
      "read_timeout" => $this->_timeout
    );

    if ($method === "post") {
      // $request = $client->post($url, $requestHeaders, $requestParams, $requestOptions);
      $requestOptions = array_merge($requestOptions, ["query" => $requestParams]);
      $request = new Request("POST", $url, $requestOptions);
    } else if ($method === "get") {
      //If we find an occurence of "?", it means there are already parameters specified
      $urlWithParams = $url . (( strpos($url, "?") !== false ) ? "&" : "?");
      $urlWithParams .= http_build_query($requestParams);
      // $request = $client->get($urlWithParams, $requestHeaders, $requestOptions);
      $request = new Request("GET", $urlWithParams, $requestOptions);
    }

    return $request;
  }

  /**
   * Sends a request and returns the reponse and error if any.
   * If the server returns a JSON response, it will be returned as an object.
   * 
   * @param string $method The request's method (GET or POST)
   * @param string $url The URL to perform the request to
   * @param array $options An options array
   * 
   * @return object { "statusCode": 200, "body": {…}, "error": "…" }
   */
  private function _request($method, $url, $options)
  {
    $result = false;

    //Extract parameters from options
    $requestParams = ( isset($options["params"]) ) ? $options["params"] : array();

    //If there are any files, add them to the list of params
    if (isset($options["files"])) {
      $requestParams = array_merge( $requestParams, $this->_getFilesTempPath($options["files"]) );
    }

    $getFromCache = (isset($options["fromCache"])) ? $options["fromCache"] : true;

    $cacheId = $this->_getCacheId($method, $url, $requestParams);

    if ($getFromCache) {
      //Check if the response has already been cached
      if ($cachedResult = Craft::$app->getCache()->get($cacheId)) {
        return $cachedResult;
      }
    }

    //If cache is empty or bypassed, send the request
    $responseReceived = false;
    $response = false;
    $errorMsg = "";

    try {
        //Build the request
        $request = $this->_buildRequest($method, $url, $requestParams, $options);

        //Potentially long-running request, so close session to prevent session blocking on subsequent requests
        Craft::$app->session->close();

        //Send the request
        $response = $this->_client->send($request);
        $responseReceived = true;
    }
    //HTTP response with error code received
    catch(\GuzzleHttp\Exception\BadResponseException $e) {
      $response = $e->getResponse();
      $errorMsg = $e->getMessage();
      $responseReceived = true;
    }
    //No response received
    catch(\Exception $e) {
      $errorMsg = $e->getMessage();
    }

    $result = array(
      "statusCode" => 0,
      "body" => array()
    );

    if ($responseReceived) {
      $result["statusCode"] = $response->getStatusCode();
      $result["body"] = $response->getBody()->getContents();

      //Store in cache if response was successful
      if ($response->getStatusCode() == 200) {
        Craft::$app->cache->set($cacheId, $result, $this->_cacheTtl);
      }
    }

    //If there was an error, add the message to the results
    if (! $responseReceived || $response->getStatusCode() !== 200) {
      $result["error"] = $errorMsg;
    }

    return $result;
  }


  /**
   * Set cache time to live
   * 
   * @param int $ttl Number of seconds during which the cache is valid
   * 
   * @return null
   */
  public function setCacheTtl($ttl)
  {
    if (is_numeric($ttl)) {
      $this->_cacheTtl = $ttl;
    }

    return;
  }

  /**
   * Shorthand method to create a GET request
   */
  public function get($url, $params, $fromCache, $additionalOptions = [])
  {
    $options = array(
      "params"    => $params,
      "fromCache" => $fromCache
    );

    //Merge with additional options
    $options = array_merge($options, $additionalOptions);

    return $this->_request("get", $url, $options);
  }

  /**
   * Shorthand method to create a POST request
   */
  public function post($url, $params, $files, $additionalOptions)
  {
    $options = array(
      "params"    => $params,
      "files"     => $files,
      //POST requests will never be cached
      "fromCache" => false
    );

    //Merge with additional options
    $options = array_merge($options, $additionalOptions);

    return $this->_request("post", $url, $options);
  }
}