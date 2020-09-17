<?php

namespace App\VotingPlatform\Designation;

use MyCLabs\Enum\Enum;

final class DesignationStatusEnum extends Enum
{
    public const NOT_STARTED = 'not_started';
    public const SCHEDULED = 'scheduled';
    public const OPENED = 'opened';
    public const IN_PROGRESS = 'in_progress';
    public const CLOSED = 'closed';

    public const ACTIVE_STATUSES = [
        self::OPENED,
        self::SCHEDULED,
        self::IN_PROGRESS,
    ];
}
