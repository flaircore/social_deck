<?php

namespace Drupal\social_deck\Service;

use Drupal\social_deck\Helpers\SocialDeckClients;

/**
 * Service for posting social media content.
 */
class PostToSocialMedia {

  /**
   * Database operations related to social media posts.
   *
   * @var \Drupal\social_deck\Service\SocialMediaPosts
   */
  private $socialMediaPosts;

  /**
   * Class constructor.
   */
  public function __construct(SocialMediaPosts $social_media_posts) {
    $this->socialMediaPosts = $social_media_posts;
  }

  /**
   * Posts data to social media, and updates entities.
   */
  final public function postToSocials() {
    $post = $this->socialMediaPosts->getSocialPost();

    if (!$post) {
      return;
    }
    $is_posted = FALSE;
    $clients = new SocialDeckClients();
    $post_info = [];

    // Twitter.
    /** @var \Drupal\social_deck\Helpers\Clients\TwitterClient|NULL $tweet */
    $tweet = $clients->getTwitterInstance();
    if ($tweet) {
      $tweet_res = $tweet->post($post);
      // If posted add is posted true and save::
      // save tweet id for use later too.
      if ($tweet_res) {
        $is_posted = TRUE;
        $post_info['tweet_id'] = $tweet_res;

      }
      // Else do nothing.
    }

    $facebook = $clients->getFacebookInstance();
    if ($facebook) {
      $is_posted = TRUE;
      $post_info['facebook_id'] = $facebook;
    }

    // Finally Update with data.
    $this->socialMediaPosts->updatePost(
      $post['id'],
      $is_posted,
      $post_info);
  }

}
