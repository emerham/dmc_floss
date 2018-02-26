<?php

namespace Drupal\dmc_floss;

/**
 * Interface DmcFlossContentInterface.
 */
interface DmcFlossContentInterface {

  /**
   * Check the inventory status for the given node.
   *
   * @param int $floss_id Floss ID number to check for inventory status.
   *
   * @return mixed
   */
  public function checkInventory($floss_id);

  /**
   * Update the Inventory count for the given node.
   *
   * @param int $floss_id Floss ID number to update Inventory on.
   * @param int $quantity Quantity to update for.
   *
   * @return mixed
   */
  public function updateInventory($floss_id, $quantity);

  /**
   * Update the Status for the given node.
   *
   * @param int $floss_id Floss ID number to update Status for.
   * @param string $status Status to update
   *
   * @return mixed
   */
  public function updateStatus($floss_id, $status);

}
