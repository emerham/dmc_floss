<?php

namespace Drupal\dmc_floss;

use Drupal\node\Entity\Node;

/**
 * Class DmcFlossContent.
 */
class DmcFlossContent implements DmcFlossContentInterface {

  /**
   * Constructs a new DmcFlossContent object.
   */
  public function __construct() {

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
      // Update the status of the floss.
      $node->field_dmc_inventory_status->value = $status;
      // Save the updated data back to the database.
      $node->save();
      // Return True.
      return TRUE;
    }
  }

  /**
   * Load a node of type dmc_thread_color with the given title.
   *
   * @param string $title The title to search on.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\dmc_floss\DmcFlossContent|null
   */
  protected function getNodeFromTitle($title) {
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
    } else {
      return NULL;
    }

  }
}
