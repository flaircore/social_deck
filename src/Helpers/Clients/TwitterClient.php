<?php

namespace Drupal\social_deck\Helpers\Clients;

use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Twitter client wrapper class in relation to configs.
 */
class TwitterClient {

  /**
   * Twitter SDK instance in relation to configs.
   *
   * @var \Abraham\TwitterOAuth\TwitterOAuth
   */

  private $twitterApi;

  /**
   * Class constructor.
   */
  public function __construct($settings) {

    // API Key.
    $consumer_key = $settings['consumer_key'];

    // API Key Secret.
    $consumer_secret = $settings['consumer_secret'];

    // Access Token.
    $oauth_token = $settings['oauth_token'];

    // Access Token Secret.
    $oauth_token_secret = $settings['oauth_token_secret'];
    $twitter_api = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
    $this->twitterApi = $twitter_api;
  }

  /**
   * Verifies the Twitter account.
   *
   * @return array
   *   Twitter account name and screen name.
   */
  final public function verifyCredentials() {
    $content = $this->twitterApi->get("account/verify_credentials");

    if ($content->errors) {
      return $content->errors[0]->message;
    }

    return [
      'name' => $content->name,
      'screen_name' => $content->screen_name,
    ];
  }

  /**
   * Posts content to Twitter.
   *
   * @param array $content
   *   The content to post to twitter.
   *
   * @return false|mixed
   *   The Tweet id, or false if failed to post.
   */
  final public function post(array $content) {

    $media_ids = [];
    $params = [
      "status" => $content['status'],
    ];
    if (isset($content['media'])) {
      foreach ($content['media'] as $path) {
        $media_res = $this->twitterApi->upload('media/upload', ['media' => $path]);
        $media_ids[] = $media_res->media_id_string;
      }

      $media_ids = implode(',', $media_ids);
      $params["media_ids"] = $media_ids;
    }

    $this->twitterApi->setTimeouts(60, 10);

    $statues = $this->twitterApi->post("statuses/update", $params);

    // Reply with content link.
    if (isset($content['link'])) {
      $reply_params = [
        'status' => 'Checkout this blog @ ' . $content['link'],
        'in_reply_to_status_id' => $statues->id,
        'auto_populate_reply_metadata' => TRUE,
      ];

      $this->twitterApi->post("statuses/update", $reply_params);
    }

    if ($this->twitterApi->getLastHttpCode() == 200) {
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
