<?php

namespace Drupal\social_deck\Helpers;

use Drupal\social_deck\Helpers\Clients\TwitterClient;
use Drupal\social_deck\Helpers\Clients\FacebookClient;

/**
 * Social deck clients service class.
 */
class SocialDeckClients {

  /**
   * An array to hold config settings.
   *
   * @var array
   */
  protected $settings;

  /**
   * Twitter client instance.
   *
   * @var \Drupal\social_deck\Helpers\Clients\TwitterClient
   */
  protected $twitter;

  /**
   * Facebook client instance.
   *
   * @var \Drupal\social_deck\Helpers\Clients\FacebookClient
   */
  protected $facebook;

  /**
   * Class constructor.
   */
  public function __construct() {

    $this->settings = [
      'twitter' => [],
      'facebook' => [],
    ];

    $this->setup();
  }

  /**
   * Class constructor setup method.
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
        'consumer_key' => $consumer_key,
        'consumer_secret' => $consumer_secret,
        'oauth_token' => $oauth_token,
        'oauth_token_secret' => $oauth_token_secret,
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
        'access_token' => $fb_access_token,
        'app_secret' => $fb_app_secret,
        'app_id' => $fb_app_id,
        'page_post_id' => $fb_page_id,
      ];

      $this->settings['facebook'] = $facebook;

      $this->facebook = new FacebookClient($this->settings['facebook']);
    }

  }

  /**
   * The Facebook client instance.
   *
   * @return \Drupal\social_deck\Helpers\Clients\FacebookClient
   *   Facebook client instance.
   */
  public function getFacebookInstance() {
    return $this->facebook;

  }

  /**
   * The Twitter client instance.
   *
   * @return \Drupal\social_deck\Helpers\Clients\TwitterClient
   *   Twitter client instance.
   */
  public function getTwitterInstance() {
    return $this->twitter;
  }

}
