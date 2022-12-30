<?php

namespace Drupal\social_deck\Helpers;

use Drupal\social_deck\Helpers\Clients\TwitterClient;
use Drupal\social_deck\Helpers\Clients\FacebookClient;

/**
 *
 */
class SocialDeckClients {

  /**
   * @var array*/
  protected $settings;

  protected $twitter;

  protected $facebook;

  /**
   *
   */
  public function __construct() {

    $this->settings = [
      'twitter' => [],
      'facebook' => [],
    ];

    $this->setup();
  }

  /**
   *
   */
  protected function setup() {
    /** @var  \Drupal\Core\Config\ConfigFactoryInterface $config */
    $config = \Drupal::service('config.factory');
    $consumer_key = $config->get('social_deck.settings')->get('twitter.consumer_key');
    $consumer_secret = $config->get('social_deck.settings')->get('twitter.consumer_secret');
    $oauth_token = $config->get('social_deck.settings')->get('twitter.oauth_token');
    $oauth_token_secret = $config->get('social_deck.settings')->get('twitter.oauth_token_secret');

    if ($consumer_key) {
      $twitter = [
        'CONSUMER_KEY' => $consumer_key,
        'CONSUMER_SECRET' => $consumer_secret,
        'OAUTH_TOKEN' => $oauth_token,
        'OAUTH_TOKEN_SECRET' => $oauth_token_secret,
      ];
      $this->settings['twitter'] = $twitter;

      // 'CONSUMER_KEY', 'CONSUMER_SECRET', 'OAUTH_TOKEN', 'OAUTH_TOKEN_SECRET'
      $this->twitter = new TwitterClient($this->settings['twitter']);
    }

    $fb_app_id = $config->get('social_deck.settings')->get('facebook.app_id');
    $fb_app_secret = $config->get('social_deck.settings')->get('facebook.app_secret');
    $fb_access_token = $config->get('social_deck.settings')->get('facebook.access_token');
    $fb_page_id = $config->get('social_deck.settings')->get('facebook.page_id');

    if ($fb_access_token) {
      $facebook = [
        'ACCESS_TOKEN' => $fb_access_token,
        'APP_SECRET' => $fb_app_secret,
        'APP_ID' => $fb_app_id,
        'PAGE_POST_ID' => $fb_page_id,
      ];

      $this->settings['facebook'] = $facebook;

      $this->facebook = new FacebookClient($this->settings['facebook']);
    }

  }

  /**
   * @return \Drupal\social_deck\Helpers\Clients\FacebookClient
   */
  public function getFacebookInstance() {
    return $this->facebook;

  }

  /**
   * @return \Drupal\social_deck\Helpers\Clients\TwitterClient
   */
  public function getTwitterInstance() {
    return $this->twitter;
  }

}
