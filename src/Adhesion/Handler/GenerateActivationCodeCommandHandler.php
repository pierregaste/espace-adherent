<?php

namespace App\Adhesion\Handler;

use App\Adhesion\ActivationCodeManager;
use App\Adhesion\Command\GenerateActivationCodeCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\AdhesionCodeValidationMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

#[AsMessageHandler]
class GenerateActivationCodeCommandHandler
{
    public function __construct(
        private readonly ActivationCodeManager $activationCodeGenerator,
        private readonly MailerService $transactionalMailer,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
    ) {
    }

    public function __invoke(GenerateActivationCodeCommand $command): void
    {
        $code = $this->activationCodeGenerator->generate($command->adherent, $command->force);

        $this->transactionalMailer->sendMessage(AdhesionCodeValidationMessage::create(
            $command->adherent,
            $code->value,
            $this->loginLinkHandler->createLoginLink($command->adherent)->getUrl()
        ));
    }
}
