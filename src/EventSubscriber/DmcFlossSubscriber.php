<?php

namespace Drupal\dmc_floss\EventSubscriber;

use Alexa\Request\IntentRequest;
use Alexa\Request\SessionEndedRequest;
use Drupal\alexa\AlexaEvent;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\dmc_floss\DmcFlossContentInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * DMC Floss event subscriber.
 */
class DmcFlossSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\dmc_floss\DmcFlossContent definition.
   *
   * @var \Drupal\dmc_floss\DmcFlossContent
   */
  protected $dmcFlossContent;

  /**
   * The Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * DmcFlossSubscriber constructor.
   *
   * @param \Drupal\dmc_floss\DmcFlossContentInterface $dmc_floss_content
   *   The DmcFlossContent object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct(DmcFlossContentInterface $dmc_floss_content, LoggerChannelFactoryInterface $logger_factory) {
    $this->dmcFlossContent = $dmc_floss_content;
    $this->logger = $logger_factory->get('dmc_floss');
  }

  /**
   * Gets the event.
   */
  public static function getSubscribedEvents() {
    $events['alexaevent.request'][] = ['onRequest', 0];
    return $events;
  }

  /**
   * Called upon a request event.
   *
   * @param \Drupal\alexa\AlexaEvent $event
   *   The event object.
   */
  public function onRequest(AlexaEvent $event) {
    $request = $event->getRequest();
    $response = $event->getResponse();

    if ($request instanceof IntentRequest) {
      switch ($request->intentName) {
        case 'AMAZON.CancelIntent':
        case 'AMAZON.StopIntent':
          $response->respond('Goodbye')
            ->endSession();
          break;

        case 'AMAZON.HelpIntent':
          $response->respond('You can ask for inventory status, update the inventory count, and change the inventory status.')
            ->endSession();
          break;

        case 'CheckStatus':
          $floss_id = $request->getSlot('floss_id');
          $inventory = $this->dmcFlossContent->checkInventory($floss_id);
          $this->logger->warning('Check Status was called with slot of @floss_id and we found @count', [
            '@floss_id' => $floss_id,
            '@count' => $inventory['count'],
          ]);
          if ($inventory) {
            if ($inventory['count'] > 0 && $inventory['status'] == 'h') {
              $response->respond('You have ' . $inventory['count'] . ' of ' . $inventory['color'])
                ->withCard('Floss', 'You have ' . $inventory['count'] . ' of ' . $inventory['color'])
                ->endSession();
            }
            else {
              $response->respond('You do not have any in your inventory.')
                ->withCard('Floss', 'You do not have any in your inventory.')
                ->endSession();
            }
          }
          else {
            $response->respond('Sorry, no Floss with that ID found.')
              ->endSession();
          }
          break;

        case 'UpdateCount':
          $floss_id = $request->getSlot('floss_id');
          $count = $request->getSlot('AMAZON.NUMBER');
          $update_count = $this->dmcFlossContent->updateInventory($floss_id, $count);
          if ($update_count) {
            $response->respond('Update successful for ' . $floss_id . '. Set count to ' . $count)
              ->withCard('Floss', 'Update successful for ' . $floss_id . '. Set count to ' . $count)
              ->endSession();
          }
          else {
            $response->respond('Sorry, something went wrong.')
              ->withCard('Floss', 'Sorry, something went wrong.')
              ->endSession();
          }
          break;

        case 'UpdateStatus':
          $floss_id = $request->getSlot('floss_id');
          $status = $request->getSlot('status');
          $update_status = $this->dmcFlossContent->updateStatus($floss_id, $status);
          if ($update_status) {
            $response->respond('Update successful for ' . $floss_id . '. Status is now ' . $status)
              ->withCard('Floss', 'Update successful. Floss ' . $floss_id . ' has status of ' . $status)
              ->endSession();
          }
          else {
            $response->respond('Sorry, something went wrong.')
              ->withCard('Floss', 'Sorry, something went wrong.')
              ->endSession();
          }
          break;

        case 'InventoryReport':
          $report = $this->dmcFlossContent->inventoryReport();
          $missing_floss = implode(',', array_keys($report['floss']));
          $reply_text = 'You currently are missing ' . $report['count'] . '. They are ' . $missing_floss;
          $response->respond($reply_text)
            ->withCard('Floss', 'Current size of floss that is needed is ' . $report['count']);
          break;
      }
    }
    elseif ($request instanceof SessionEndedRequest) {
      // @todo: Clean up any saved session state here.
    }
    else {
      $this->logger->warning('Request was not an expected request type: @type', [
        '@type' => get_class($request),
      ]);
    }
  }

}
