<?php

namespace App\Adherent\Campus;

use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AdherentMembershipSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $bus, private readonly string $campusWebhookHost)
    {
    }

    public function onRegistrationComplete(UserEvent $event): void
    {
        if (!$this->isSubscriberEnabled()) {
            return;
        }

        $this->bus->dispatch(new AdherentRegistrationCommand($event->getUser()->getUuid()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_MEMBERSHIP_COMPLETED => ['onRegistrationComplete', -256],
        ];
    }

    private function isSubscriberEnabled(): bool
    {
        return !empty($this->campusWebhookHost);
    }
}