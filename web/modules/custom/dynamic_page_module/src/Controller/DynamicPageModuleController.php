<?php

namespace Drupal\dynamic_page_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\File\FileUrlGenerator;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Dynamic page module routes.
 */
class DynamicPageModuleController extends ControllerBase {

  /**
   * Database connection object.
   *
   * @var \Drupal\Core\Database
   */
  protected $connection;

  /**
   * Manages the entity type plugin.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Generates url for file.
   *
   * @var \Drupal\Core\File\FileUrlGenerator
   */
  protected $url;

  /**
   * Constructor to instantiate database connection.
   *
   * @param \Drupal\Core\Database $connection
   *   Stores database connection instance.
   */
  public function __construct(Connection $connection, EntityTypeManager $entityTypeManager, FileUrlGenerator $url) {
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
    $this->url = $url;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('file_url_generator'),
    );
  }

  /**
   * Builds the response.
   */
  public function aboutPage() {
    $query = $this->connection->query("SELECT name as name,  designation as dg,  linkedin_url as url, profile_image as img
      FROM {leaders}");
    $query->execute();
    // $query = $this->connection->select('leaders', 'ld')
    //   ->fields('ld', ['name', 'designation', 'linkedin_url', 'profile_image'])
    //   ->execute();
    $leader_data = $query->fetchAll();
    $user_data = [];
    $news = [];
    // Check if query result is empty or not.
    if ($leader_data) {
      // Iteration each leader data to add image path.
      foreach ($leader_data as $leader) {
        $image_id = File::load($leader->img);
        $image_path = $this->url->generateAbsoluteString($image_id->getFileUri());
        $leader->img = $image_path;
      }
    }
    $config = \Drupal::config('dynamic_page_module.settings');
    // Check if configuration exists or not.
    if ($config->get('anchor')) {
      $user = User::load($config->get('anchor'));
      $user_data = [
        'desc' => $user->get('field_description')->value,
        'name' => $user->get('field_name')->value,
        'uid' => $user->get('uid')->value,
      ];
      $query = \Drupal::entityQuery('node');
      $query->condition('type', 'news')
        ->accessCheck(TRUE)
        ->condition('status', 1)
        ->condition('uid', $user_data['uid'])
        ->sort('created', 'DESC')
        ->range(0, 3);
      $node_ids = $query->execute();
      // Iterating over each nodes to get fields value.
      foreach ($node_ids as $nid) {
        $node = Node::load($nid);
        // Load image by id.
        $image_id = File::load($node->get('field_thumbnail')->target_id);
        // Get url of the image.
        $image_path = $this->url->generateAbsoluteString($image_id->getFileUri());
        $options = ['absolute' => TRUE];
        // Get term name by id.
        $category = $this->entityTypeManager->getStorage('taxonomy_term')->load($node->get('field_category')->target_id);
        $tags = $this->entityTypeManager->getStorage('taxonomy_term')->load($node->get('field_hash_tags')->target_id);
        // Get node url from node id.
        $url = Url::fromRoute('entity.node.canonical', ['node' => $nid], $options);
        $news[$nid] = [
          'news_title' => $node->get('title')->value,
          'news_desc' => $node->get('field_news')->value,
          'news_img' => $image_path,
          'news_category' => $category->name->value,
          'news_hash_tags' => $tags->name->value,
          'news_url' => $url->toString(),
        ];
      }
    }
    return [
      '#theme' => "about",
      '#leadership_data' => $leader_data,
      '#user_data' => $user_data,
      '#user_news' => $news,
      '#cache' => [
        'tags' => ['config:dynamic_page_module.settings'],
      ],
    ];
  }

}
