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
    $result = $this->getNodeFromTitle($floss_id);
    if (is_null($result)) {
      return FALSE;
    }
    else {
      return [
        'status' => $result->get('field_dmc_inventory_status')->value,
        'count' => $result->get('field_dmc_quantity')->value,
      ];
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

  /**
   * Load a node of type dmc_thread_color with the given title.
   *
   * @param string $title The title to search on.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null|static
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
    return node::load($node_id);
  }
}
