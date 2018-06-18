<?php

namespace Drupal\event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;

/**
 * Provides a 'Event Subscribers List' block.
 *
 * @Block(
 *   id = "event_subscribers_block",
 *   admin_label = @Translation("Event Subscribers List")
 * )
 */
class EventSubscribersListBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Creates a HelpBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager->getStorage('node');
    $this->userStorage = $entity_type_manager->getStorage('user');
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration, $plugin_id, $plugin_definition, $container->get('entity_type.manager'), $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   *
   * The return value of the build() method is a renderable array. Returning an
   * empty array will result in empty block contents. The front end will not
   * display empty blocks.
   */
  public function build() {
    $query = $this->database->select('event_subscription_table', 'event');
    $query->fields('event', ['id', 'nid', 'event_subscription_success', 'user_id']);
    $data = $query->execute()->fetchAllAssoc('id');
    if (!empty($data)) {
      $node_id = [];
      $event_data = [];
      foreach ($data as $key => $value) {
        $node_id = $value->nid;
        $user_id = $value->user_id;
        if (!empty($node_id)) {
          $node = $this->entityTypeManager->load($node_id);
          $event_data[$key]['node_title'] = $node->get('title')->first()->get('value')->getString();
          $event_data[$key]['node_id'] = $node_id;
        }
        if (!empty($user_id)) {
          $user_data = $this->userStorage->load($user_id);
          $event_data[$key]['user_name'] = $user_data->get('name')->first()->get('value')->getString();
          $event_data[$key]['user_mail'] = $user_data->get('mail')->first()->get('value')->getString();
        }
      }
    }
    return [
      '#theme' => 'block__event_subscribers_list',
      '#events' => $event_data,
    ];
  }

}
