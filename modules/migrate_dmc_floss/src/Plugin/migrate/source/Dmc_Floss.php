<?php
/**
 * @file
 * Contains \Drupal\migrate_dmc_floss\Plugin\migrate\source
 * Created by PhpStorm.
 * User: brabhamm
 * Date: 2/13/18
 * Time: 8:42 PM
 */

namespace Drupal\migrate_dmc_floss\Plugin\migrate\source;

use \Drupal\migrate\row;
use \Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 7 DMC Floss Node
 *
 * @MigrateSource(
 *   id = "dmc_floss"
 * )
 */
class Dmc_Floss extends SqlBase {

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

  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');

    $result = $this->getDatabase()->query('
      SELECT
        fld.field_color_name_value,
        fld.field_color_name_format
      FROM
        {field_data_field_color_name} fld
      WHERE
        fld.entity_id = :nid
    ', [':nid' => $nid]);
    foreach ($result as $record) {
      $row->setSourceProperty('field_color_name', $record->field_color_name_value);
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
