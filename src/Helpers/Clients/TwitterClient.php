<?php

namespace Drupal\social_deck\Helpers\Clients;

use Abraham\TwitterOAuth\TwitterOAuth;

/**
 *
 */
class TwitterClient {

  /**
   * @var \Abraham\TwitterOAuth\TwitterOAuth*/

  private $twitter_api;

  /**
   *
   */
  public function __construct($settings) {

    // API Key.
    $CONSUMER_KEY = $settings['CONSUMER_KEY'];

    // API Key Secret.
    $CONSUMER_SECRET = $settings['CONSUMER_SECRET'];
    ;

    // Access Token.
    $OAUTH_TOKEN = $settings['OAUTH_TOKEN'];

    // Access Token Secret.
    $OAUTH_TOKEN_SECRET = $settings['OAUTH_TOKEN_SECRET'];
    $twitter_api = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $OAUTH_TOKEN, $OAUTH_TOKEN_SECRET);
    $this->twitter_api = $twitter_api;
  }

  /**
 *
 */
  final public function verify_credentials() {
    $content = $this->twitter_api->get("account/verify_credentials");

    if ($content->errors) {
      return $content->errors[0]->message;
    }

    return [
      'name' => $content->name,
      'screen_name' => $content->screen_name,
    ];
  }

  /**
 *
 */
  final public function post($content) {

    $media_ids = [];
    $params = [
      "status" => $content['status'],
    ];
    if (isset($content['media'])) {
      foreach ($content['media'] as $path) {
        $media_res = $this->twitter_api->upload('media/upload', ['media' => $path]);
        $media_ids[] = $media_res->media_id_string;
      }

      $media_ids = implode(',', $media_ids);
      $params["media_ids"] = $media_ids;
    }

    $this->twitter_api->setTimeouts(60, 10);

    $statues = $this->twitter_api->post("statuses/update", $params);

    // Reply with content link.
    if (isset($content['link'])) {
      $reply_params = [
        'status' => 'Checkout this blog @ ' . $content['link'],
        'in_reply_to_status_id' => $statues->id,
        'auto_populate_reply_metadata' => TRUE,
      ];

      $this->twitter_api->post("statuses/update", $reply_params);
    }

    if ($this->twitter_api->getLastHttpCode() == 200) {
      // Tweet posted successfully
      // Post link as comment.
      return $statues->id;
    }
    else {
      // Handle error case.
      return FALSE;
    }

  }

}
