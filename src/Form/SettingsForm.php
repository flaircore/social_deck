<?php

namespace Drupal\social_deck\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\social_deck\Helpers\SocialDeckClients;

/**
 * Configure Social Deck settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_deck_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['social_deck.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['twitter'] = [
      '#tree' => TRUE,
      '#type' => 'fieldset',
      '#title' => $this->t('Twitter Settings'),
      '#collapsible' => FALSE,
      '#open' => FALSE,
    ];

    $form['facebook'] = [
      '#tree' => TRUE,
      '#type' => 'fieldset',
      '#title' => $this->t('Facebook Settings'),
      '#collapsible' => FALSE,
      '#open' => FALSE,
    ];

    // Twitter inputs.
    $form['twitter']['consumer_key'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#group' => 'twitter',
      '#title' => $this->t('Consumer Key'),
      '#default_value' => $this->config('social_deck.settings')->get('twitter.consumer_key'),
      '#size' => 32,
    ];

    $form['twitter']['consumer_secret'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#group' => 'twitter',
      '#title' => $this->t('Consumer Secret'),
      '#default_value' => $this->config('social_deck.settings')->get('twitter.consumer_secret'),
      '#size' => 32,
    ];

    $form['twitter']['oauth_token'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#group' => 'twitter',
      '#title' => $this->t('Oauth Token'),
      '#default_value' => $this->config('social_deck.settings')->get('twitter.oauth_token'),
      '#size' => 32,
    ];

    $form['twitter']['oauth_token_secret'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#group' => 'twitter',
      '#title' => $this->t('Oauth Token Secret'),
      '#default_value' => $this->config('social_deck.settings')->get('twitter.oauth_token_secret'),
      '#size' => 32,
    ];

    // Verify twitter settings.
    if ($this->config('social_deck.settings')->get('twitter.oauth_token')) {
      $form['twitter']['twitter_message'] = [
        '#markup' => '<div id="verify_twitter_settings"></div>',
      ];

      $form['twitter']['actions'] = [
        '#type' => 'button',
        '#value' => $this->t('Verify twitter settings'),
        '#ajax' => [
          'callback' => '::verifyTwitterSettings',
        ],
      ];
    }

    // Facebook inputs.
    $form['facebook']['app_id'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#group' => 'facebook',
      '#title' => $this->t('App Id'),
      '#default_value' => $this->config('social_deck.settings')->get('facebook.app_id'),
      '#size' => 32,
    ];

    $form['facebook']['app_secret'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#group' => 'facebook',
      '#title' => $this->t('App Secret'),
      '#default_value' => $this->config('social_deck.settings')->get('facebook.app_secret'),
      '#size' => 32,
    ];

    $form['facebook']['access_token'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#group' => 'facebook',
      '#title' => $this->t('Access Token'),
      '#default_value' => $this->config('social_deck.settings')->get('facebook.access_token'),
      '#size' => 32,
    ];

    $form['facebook']['page_id'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#group' => 'facebook',
      '#title' => $this->t('Page Id'),
      '#default_value' => $this->config('social_deck.settings')->get('facebook.page_id'),
      '#size' => 32,
    ];

    // Verify facebook settings.
    if ($this->config('social_deck.settings')->get('facebook.app_id')) {
      $form['facebook']['facebook_message'] = [
        '#markup' => '<div id="verify_facebook_settings"></div>',
      ];

      $form['facebook']['actions'] = [
        '#type' => 'button',
        '#value' => $this->t('Verify facebook settings'),
        '#ajax' => [
          'callback' => '::verifyFacebookSettings',
        ],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $consumer_key = $form_state->getValue('twitter')['consumer_key'];
    $consumer_secret = $form_state->getValue('twitter')['consumer_secret'];
    $auth_token = $form_state->getValue('twitter')['oauth_token'];
    $auth_token_secret = $form_state->getValue('twitter')['oauth_token_secret'];
    $fb_app_id = $form_state->getValue('facebook')['app_id'];
    $fb_app_secret = $form_state->getValue('facebook')['app_secret'];
    $fb_access_token = $form_state->getValue('facebook')['access_token'];
    $fb_page_id = $form_state->getValue('facebook')['page_id'];
    $this->config('social_deck.settings')
      ->set('twitter.consumer_key', $consumer_key)
      ->set('twitter.consumer_secret', $consumer_secret)
      ->set('twitter.oauth_token', $auth_token)
      ->set('twitter.oauth_token_secret', $auth_token_secret)
      ->set('facebook.app_id', $fb_app_id)
      ->set('facebook.app_secret', $fb_app_secret)
      ->set('facebook.access_token', $fb_access_token)
      ->set('facebook.page_id', $fb_page_id)
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Render twitter verification message.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An Ajax response.
   */
  public function verifyTwitterSettings(array &$form, FormStateInterface $form_state) {
    $clients = new SocialDeckClients();
    /** @var \Drupal\social_deck\Helpers\Clients\TwitterClient $tweet */
    $tweet = $clients->getTwitterInstance();
    $tweet = $tweet->verifyCredentials();
    if (isset($tweet['name'])) {
      $content = $this->t('Settings verified: Name: @name, Screen Name: @screen_name',
        ['@name' => $tweet['name'], '@screen_name' => $tweet['screen_name']]);
    }
    else {
      $content = $this->t('Wrong Settings: @msg', ['@msg' => $tweet]);
    }
    $res = new AjaxResponse();
    $twitter_res = '<div class="message">' . $content . '</div>';
    $res->addCommand(
      new HtmlCommand(
        '#verify_twitter_settings',
        $twitter_res,
      )
    );

    return $res;
  }

  /**
   * Render Facebook verification message.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\social_deck\Helpers\Clients\FacebookClient
   *   Should be an Ajax response.
   */
  public function verifyFacebookSettings(array &$form, FormStateInterface $form_state) {
    $clients = new SocialDeckClients();
    return $clients->getFacebookInstance();

  }

}
