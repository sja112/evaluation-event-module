<?php

namespace Drupal\event\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\user\UserDataInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\Request;

/**
 * Simple page controller for drupal.
 */
class EventSubscribe extends ControllerBase {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The current database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The user data service.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * To instantiate protected variables.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection object.
   * @param \Drupal\user\UserDataInterface $user_data
   *   The user manager.
   */
  public function __construct(Request $request, Connection $database, UserDataInterface $user_data) {
    $this->request = $request;
    $this->database = $database;
    $this->userData = $user_data;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('request_stack')->getCurrentRequest(), $container->get('database'), $container->get('user.data')
    );
  }

  /**
   * To add event subscribers information in the database.
   */
  public function eventSubscribe($id) {
    $dateTime = \DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
    // Get timestamp.
    $newDateString = $dateTime->format('Y-m-d\TH:i:s');
    $user = $this->currentUser();
    // Get current user id.
    $user_id = $user->id();
    if ($id !== NULL) {
      $parameters = $this->request->query->all();
      $flag = $parameters['flag'];
      if ($flag === 'subscribe') {
        $flag_value = 1;
      }
      $this->database->insert('event_subscription_table')
        ->fields([
          'nid',
          'event_subscription_success',
          'user_id',
          'timestamp',
        ])
        ->values([
          'nid' => $id,
          'event_subscription_success' => $flag_value,
          'user_id' => $user_id,
          'timestamp' => $newDateString,
        ])
        ->execute();
      $response = new AjaxResponse();
      $text = $this->t('Subscribed');
      $response->addCommand(new ReplaceCommand('.event-subscribe-link', $text));
      return $response;
    }
  }

}
