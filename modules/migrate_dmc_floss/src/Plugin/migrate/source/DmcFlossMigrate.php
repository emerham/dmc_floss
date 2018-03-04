<?php

namespace Drupal\migrate_dmc_floss\Plugin\migrate\source;

use Drupal\migrate\row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 7 DMC Floss Node.
 *
 * @MigrateSource(
 *   id = "dmc_floss"
 * )
 */
class DmcFlossMigrate extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('node', 'n')
      ->condition('n.type', 'dmc_thread_color')
      ->fields('n', [
        'nid',
        'vid',
        'type',
        'language',
        'title',
        'uid',
        'status',
        'created',
        'changed',
        'promote',
        'sticky',
      ]);
    $query->orderBy('nid');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');
    // Floss Color name.
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_color_name_value
      FROM
        {field_data_field_color_name} fld
      WHERE
        fld.entity_id = :nid
    ', [':nid' => $nid]);
    foreach ($result as $record) {
      $row->setSourceProperty('field_color_name', $record->field_color_name_value);
    }
    // Quantity field.
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_quantity_value
      FROM
        {field_data_field_quantity} fld
      WHERE
        fld.entity_id = :nid
    ', [':nid' => $nid]);
    foreach ($result as $record) {
      $row->setSourceProperty('field_quantity', $record->field_quantity_value);
    }
    // Inventory Status field.
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_have_need_value
      FROM
        {field_data_field_have_need} fld
      WHERE
        fld.entity_id = :nid
    ', [':nid' => $nid]);
    foreach ($result as $record) {
      $row->setSourceProperty('field_have_need', $record->field_have_need_value);
    }
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'nid' => $this->t('Node ID'),
      'vid' => $this->t('Version ID'),
      'type' => $this->t('Type'),
      'title' => $this->t('Title'),
      'format' => $this->t('Format'),
      'teaser' => $this->t('Teaser'),
      'uid' => $this->t('Authored by (uid)'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Modified timestamp'),
      'status' => $this->t('Published'),
      'promote' => $this->t('Promoted to front page'),
      'sticky' => $this->t('Sticky at top of lists'),
      'language' => $this->t('Language (en)'),
      'field_color_name' => $this->t('Floss Color'),
      'field_quantity' => $this->t('Floss Quantity'),
      'field_have_need' => $this->t('Inventory Status'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['nid']['type'] = 'integer';
    $ids['nid']['alias'] = 'n';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return FALSE;
  }

}
