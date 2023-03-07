<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\PlumScanner\ScanStatusEvent;
use Noksi\SyliusPlumHubspotPlugin\Dto\BehavioralEventPayload;
use Noksi\SyliusPlumHubspotPlugin\Service\HubspotBehavioralEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class PlumScanner
{
    private HubspotBehavioralEvent $hubspotBehavioralEvent;
    private RouterInterface        $router;
    private string                 $scanStatusEventName;

    public function __construct(
        HubspotBehavioralEvent $hubspotBehavioralEvent,
        RouterInterface $router,
        string $scanStatusEventName
    ) {
        $this->hubspotBehavioralEvent = $hubspotBehavioralEvent;
        $this->router                 = $router;
        $this->scanStatusEventName    = $scanStatusEventName;
    }
    public function onScanStatus(ScanStatusEvent $event): void
    {
        $properties = [
            'scan_status' => $event->getStatus(),
            'scan_ko_reason' => $event->getStatus() === ScanStatusEvent::STATUS_OK ? '' : $event->getReason(),
            'ikp_url' => $event->getPlumScannerResult() ? ($event->getPlumScannerResult()['ikp_link'] ?? '') : '',
            'plum_project_url' => $this->router->generate('app_customer_project_show', [
                'token' => $event->getProject()->getToken()
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];
        $payload = new BehavioralEventPayload($this->scanStatusEventName, $properties);
        $payload->setEmail($event->getProject()->getCustomer()->getEmail());
        $this->hubspotBehavioralEvent->send($payload);
    }
}
