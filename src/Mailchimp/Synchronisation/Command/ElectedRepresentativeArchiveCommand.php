<?php

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\SynchronizeMessageInterface;

class ElectedRepresentativeArchiveCommand implements SynchronizeMessageInterface
{
    private $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
