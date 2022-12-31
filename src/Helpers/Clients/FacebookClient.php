<?php

namespace Drupal\social_deck\Helpers\Clients;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;

/**
 * Facebook client wrapper class in relation to configs.
 */
class FacebookClient {

  /**
   * Facebooks client instance in relation to config.
   *
   * @var \FacebookAds\Api
   */
  private $facebookApi;

  /**
   * The Facebook page id to post to.
   *
   * @var mixed
   */
  private $pageId;

  /**
   * Class constructor.
   */
  public function __construct($settings) {

    $access_token = $settings['access_token'];
    $app_secret = $settings['app_secret'];
    $app_id = $settings['app_id'];
    $page_post_id = $settings['page_post_id'];
    $this->pageId = $page_post_id;

    $fb = new Facebook([
      'app_id' => $app_id,
      'app_secret' => $app_secret,
    ]);

    $longLivedToken = $fb->getOAuth2Client()->getLongLivedAccessToken($access_token);

    $fb->setDefaultAccessToken($longLivedToken);

    $response = $fb->sendRequest('GET', $page_post_id, ['fields' => 'access_token'])
      ->getDecodedBody();

    // Store for use later.
    $foreverPageAccessToken = $response['access_token'];

    $fb->setDefaultAccessToken($foreverPageAccessToken);

    $this->facebookApi = $fb;

  }

  /**
   * Posts content to facebook.
   *
   * @param array $content
   *   The content to post to Facebook.
   *
   * @return mixed|void
   *   The Facebook post id, if successfully posted.
   *
   * @throws \Facebook\Exceptions\FacebookSDKException
   *   Facebook api error.
   */
  final public function post(array $content) {
    $page_post_id = $this->pageId;

    try {
      // Returns a `Facebook\FacebookResponse` object.
      $res = $this->facebookApi->sendRequest('POST', "$page_post_id/feed", [
        'message' => $content['status'],
        'link' => $content['link'],
        'source' => $this->facebookApi->fileToUpload($content['media'][0]),
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
