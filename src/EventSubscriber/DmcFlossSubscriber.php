<?php

namespace Drupal\dmc_floss\EventSubscriber;

use Alexa\Request\IntentRequest;
use Alexa\Request\LaunchRequest;
use Alexa\Request\SessionEndedRequest;
use Drupal\alexa\AlexaEvent;
use Drupal\dmc_floss\DmcFlossContentInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * DMC Floss event subscriber.
 */
class DmcFlossSubscriber implements EventSubscriberInterface {

  /**
   * \Drupal\dmc_floss\DmcFlossContent definition.
   *
   * @var \Drupal\dmc_floss\DmcFlossContent
   */
  protected $dmcFlossContent;

  /**
   * DmcFlossSubscriber constructor.
   *
   * @param \Drupal\dmc_floss\DmcFlossContent $dmc_floss_content
   */
  public function __construct(DmcFlossContentInterface $dmc_floss_content) {
    $this->dmcFlossContent = $dmc_floss_content;
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
          // TODO add help text.
          $response->respond('Help text goes here.')
            ->endSession();
          break;
        case 'CheckStatus':
          $floss_id = $request->getSlot('floss_id');
          $inventory = $this->dmcFlossContent->checkInventory($floss_id);
          \Drupal::logger('dmc_floss')
            ->warning('Check Status was called with slot of @floss_id and we found @count', [
              '@floss_id' => $floss_id,
              '@count' => $inventory['count'],
            ]);
          if ($inventory) {
            if ($inventory['count'] > 0 && $inventory['status'] == 'h') {
              $response->respond('You have ' . $inventory['count'])
                ->withCard('Floss', 'You have ' . $inventory['count'])
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
      }
    }
    elseif ($request instanceof SessionEndedRequest) {
      // @todo: Clean up any saved session state here.
    }
    else {
      \Drupal::logger('dmc_floss')
        ->warning('Request was not an expected request type: @type', [
          '@type' => get_class($request),
        ]);
    }
  }

}
