<?php

namespace App\Adhesion\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use App\Notifier\AsyncNotificationInterface;
use Ramsey\Uuid\UuidInterface;

class SendNewPrimoCotisationNotificationCommand extends UuidDefaultAsyncMessage implements AsyncNotificationInterface
{
    public function __construct(UuidInterface $uuid, public readonly float $amount)
    {
        parent::__construct($uuid);
    }
}
