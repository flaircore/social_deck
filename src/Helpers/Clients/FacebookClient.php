<?php

namespace Drupal\social_deck\Helpers\Clients;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;

/**
 *
 */
class FacebookClient {

  /**
   * @var \FacebookAds\Api*/
  private $facebook_api;
  private $page_id;

  /**
   *
   */
  public function __construct($settings) {

    $ACCESS_TOKEN = $settings['ACCESS_TOKEN'];
    $APP_SECRET = $settings['APP_SECRET'];
    $APP_ID = $settings['APP_ID'];
    $PAGE_POST_ID = $settings['PAGE_POST_ID'];
    $this->page_id = $PAGE_POST_ID;

    $fb = new Facebook([
      'app_id' => $APP_ID,
      'app_secret' => $APP_SECRET,
    ]);

    $longLivedToken = $fb->getOAuth2Client()->getLongLivedAccessToken($ACCESS_TOKEN);

    $fb->setDefaultAccessToken($longLivedToken);

    $response = $fb->sendRequest('GET', $PAGE_POST_ID, ['fields' => 'access_token'])
      ->getDecodedBody();

    // STORE FOR USE LATER.
    $foreverPageAccessToken = $response['access_token'];

    $fb->setDefaultAccessToken($foreverPageAccessToken);

    $this->facebook_api = $fb;

  }

  /**
 *
 */
  final public function post($content) {
    $PAGE_POST_ID = $this->page_id;

    try {
      // Returns a `Facebook\FacebookResponse` object.
      $res = $this->facebook_api->sendRequest('POST', "$PAGE_POST_ID/feed", [
        'message' => $content['status'],
        'link' => $content['link'],
        'source' => $this->facebook_api->fileToUpload($content['media'][0]),
      ]);
    }
    catch (FacebookResponseException $e) {
      // Echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    }
    catch (FacebookSDKException $e) {
      // Echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }

    $graphNode = $res->getGraphNode();

    return $graphNode['id'];
  }

}
