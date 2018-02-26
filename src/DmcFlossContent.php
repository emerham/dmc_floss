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
    // Look through our content for a node that matches;
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'dmc_thread_color');
    $query->condition('status', 1);
    // Pass the floss number for the title.
    $query->condition('title', $floss_id);
    // Gets all the NID's that match our query.
    $node_id = $query->execute();
    // Load all the data from the node.
    $result = node::load($node_id);
    if (is_null($result)) {
      return False;
    } else {
      return $result;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateInventory($floss_id, $quantity) {
    // TODO: Implement updateInventory() method.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function updateStatus($floss_id, $status) {
    // TODO: Implement updateStatus() method.
    return FALSE;
  }

}
