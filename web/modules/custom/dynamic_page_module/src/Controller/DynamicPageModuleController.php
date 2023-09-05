<?php

namespace Drupal\dynamic_page_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Dynamic page module routes.
 */
class DynamicPageModuleController extends ControllerBase {

  /**
   * Database connection object.
   *
   * @var \Drupal\Core\Database $connection
   */
  protected $connection;

  /**
   * Constructor to instantiate database connection.
   *
   * @param \Drupal\Core\Database $connection
   *   Stores database connection instance.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
    );
  }

  /**
   * Builds the response.
   */
  public function aboutPage() {

    $query = $this->connection->query("SELECT name as name,  designation as dg,  linkedin_url as url
      FROM {leaders}");
    $leader_data = $query->fetchAll();

    return [
      '#theme' => "about",
      '#leadership_data' => $leader_data,
    ];
  }

}
