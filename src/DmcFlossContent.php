<?php

namespace Drupal\dmc_floss;


use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;

/**
 * Class DmcFlossContent.
 */
class DmcFlossContent implements DmcFlossContentInterface {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
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
      $node->save();
      // Return True.
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function inventoryReport() {
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'dmc_thread_color');
    $query->condition('status', 1);
    $query->condition('field_dmc_inventory_status', 'n');
    $query->range(0, 5);
    $nodes = node::loadMultiple($query->execute());
    return $nodes;
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
    //$node = node::create($values);
    $node = $this->entityTypeManager->getStorage('node')->create($values);
    // Save the node object to the database.
    $node->save();
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
    // Do to entityQuery being depricated
    // $query->getStorage('node')->loadByProperties(['type' => 'dmc_thread_color', 'status' => 1, 'title' => '150']);
    $node = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'type' => 'dmc_thread_color',
      'status' => 1,
      'title' => $title,
    ]);
    return reset($node);
    /*
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'dmc_thread_color');
    $query->condition('status', 1);
    // Pass the floss number for the title.
    $query->condition('title', $title);
    // Gets all the NID's that match our query.
    $node_id = $query->execute();
    // Load all the data from the node.
    if (!empty($node_id)) {
      return node::load(current($node_id));
    }
    else {
      return NULL;
    }
    */

  }

}
