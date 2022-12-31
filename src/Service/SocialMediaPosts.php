<?php

namespace Drupal\social_deck\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\Entity\Node;

/**
 * Helper service to work with entities related to social_deck features.
 */
class SocialMediaPosts {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Gets posts of type social.
   *
   * Which are not yet shared on social media, and the
   * is due/past.
   */
  public function getSocialPost() {
    $now = new DrupalDateTime('now');
    $now = $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $ids = $nodeStorage->getQuery()
      ->condition('type', 'social')
      ->condition('status', 0)
      // ->condition('field_social_deck_date', $now, '>=')
      ->condition('field_social_post_is_posted', FALSE)
      ->execute();

    if (empty($ids)) {
      return [];
    }

    $posts = $nodeStorage->loadMultiple($ids);

    /** @var \Drupal\node\Entity\Node $post */
    $post = array_values($posts)[0];

    $about = $post->get('field_social_deck_description')->value;
    $about = preg_replace('/<(|\/)p>/', '', $about);

    $post = [
      'id' => $post->id(),
      'title' => $post->getTitle(),
      'status' => $about,
      'link' => $post->get('field_social_deck_link')->getString(),
      'media' => $this->getMedia($post),
    ];
    return $post;
  }

  /**
   * Gets the readable file location.
   */
  private function getMedia(Node $entity) {
    $items = $entity->get('field_social_deck_media')->referencedEntities();
    $fileStorage = $this->entityTypeManager->getStorage('file');
    $media = [];
    /** @var \Drupal\media\Entity\Media $item */
    foreach ($items as $item) {
      /** @var \Drupal\file\Entity\File  $file */
      $file = $fileStorage->load($item->id());
      $media[] = $file->getFileUri();
    }
    return $media;
  }

  /**
   * Updates entities fields.
   */
  final public function updatePost($id, $is_posted, $data) {
    $data = serialize($data);
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $post = $nodeStorage->load($id);
    $post->set('field_social_post_is_posted', $is_posted);
    $post->set('field_social_media_post_info', $data)
      ->save();
  }

}
