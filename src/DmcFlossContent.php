<?php

namespace Drupal\dmc_floss;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class DmcFlossContent.
 */
class DmcFlossContent implements DmcFlossContentInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * DmcFlossContent constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity type manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger factory service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger->get('dmc_floss');
  }

  /**
   * {@inheritdoc}
   */
  public function checkInventory($floss_id) {
    $node = $this->getNodeFromTitle($floss_id);
    if (is_null($node)) {
      return FALSE;
    }
    else {
      return [
        'status' => $node->get('field_dmc_inventory_status')->value,
        'count' => $node->get('field_dmc_quantity')->value,
        'color' => $node->get('field_dmc_color_name')->value,
      ];
    }
  }

  /**
   * Load a node of type dmc_thread_color with the given title.
   *
   * @param string $title
   *   The title to search on.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\dmc_floss\DmcFlossContent|null
   *   Return a node object loaded from the given floss ID, or null for
   *   nothing.
   */
  protected function getNodeFromTitle($title) {
    // Do to entityQuery being deprecated
    // $query->getStorage('node')->loadByProperties(['type' => 'dmc_thread_color', 'status' => 1, 'title' => '150']);.
    $node = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'type' => 'dmc_thread_color',
      'status' => 1,
      'title' => $title,
    ]);
    return reset($node);
  }

  /**
   * {@inheritdoc}
   */
  public function updateInventory($floss_id, $quantity) {
    // First check to make sure we have a valid node.
    $node = $this->getNodeFromTitle($floss_id);
    if (is_null($node)) {
      return FALSE;
    }
    else {
      // Update the field value for the given node.
      $node->setNewRevision(TRUE);
      $node->field_dmc_quantity->value = $quantity;
      // Save the node back to the DB.
      $node->save();
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateStatus($floss_id, $status) {
    // First check to make sure we have a valid node.
    $node = $this->getNodeFromTitle($floss_id);
    if (is_null($node)) {
      return FALSE;
    }
    else {
      if ($status === 'have') {
        $status = 'h';
      }
      else {
        $status = 'n';
      }
      // Update the status of the floss.
      $node->setNewRevision(TRUE);
      $node->field_dmc_inventory_status->value = $status;
      // Save the updated data back to the database.
      try {
        $node->save();
      }
      catch (\Exception $e) {
        $this->logger->error($e->getMessage());
      }
      // Return True.
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function inventoryReport() {
    $floss_nodes = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->condition('type', 'dmc_thread_color')
      ->condition('status', 1)
      ->condition('field_dmc_inventory_status', 'n')
      ->range(0, 5)
      ->execute();
    $size = count($floss_nodes);
    $floss_name_color = [];
    if ($size > 0) {
      $floss_nodes = $this->entityTypeManager
        ->getStorage('node')
        ->loadMultiple($floss_nodes);
      foreach ($floss_nodes as $node) {
        $floss_name_color[$node->get('title')->value] = $node->get('field_dmc_color_name')->value;
      }
    }
    return ['count' => $size, 'floss' => $floss_name_color];
  }

  /**
   * {@inheritdoc}
   */
  public function createFloss($floss_id, $count, $status, $color_name) {
    // Create an array of the values we will use for the entity.
    $values = [
      'type' => 'dmc_thread_color',
      'title' => $floss_id,
      'field_dmc_inventory_status' => $status,
      'field_dmc_quantity' => $count,
      'field_dmc_color_name' => $color_name,
      'uid' => 1,
    ];
    // Create the node object.
    // $node = node::create($values);
    try {
      $node = $this->entityTypeManager->getStorage('node')->create($values);
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
    // Save the node object to the database.
    try {
      $node->save();
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

}
