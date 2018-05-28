<?php

namespace Drupal\dmc_floss;

/**
 * Interface DmcFlossContentInterface.
 */
interface DmcFlossContentInterface {

  /**
   * Check the inventory status for the given node.
   *
   * @param int $floss_id
   *   Floss ID number to check for inventory status.
   *
   * @return bool|array
   *   The array of Status and count or false if the id
   */
  public function checkInventory($floss_id);

  /**
   * Update the Inventory count for the given node.
   *
   * @param int $floss_id
   *   Floss ID number to update Inventory on.
   * @param int $quantity
   *   Quantity to update for.
   *
   * @return bool
   *   Returns True if it was able to update a Floss, False if not.
   */
  public function updateInventory($floss_id, $quantity);

  /**
   * Update the Status for the given node.
   *
   * @param int $floss_id
   *   Floss ID number to update Status for.
   * @param string $status
   *   Status to update.
   *
   * @return bool
   *   Returns True if it was able to update a Floss, False if not.
   */
  public function updateStatus($floss_id, $status);

  /**
   * Get a list of nodes that all have status of need.
   *
   * @return array
   *   Returns an array of node id's.
   */
  public function inventoryReport();

  /**
   * Create floss content in the database.
   *
   * @param int $floss_id
   *  The Floss ID to create
   * @param int $count
   *  The inventory amount to add.
   * @param string $status
   *  The status of if we have it or not.
   *
   * @return bool
   *  True if we succeeded in making the node, false if we ran into an issue.
   */
  public function createFloss($floss_id, $count, $status, $color_name);
}
