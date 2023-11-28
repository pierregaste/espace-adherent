<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Adhesion\Command\PersistAdhesionEmailCommand;
use App\Adhesion\EmailValidationRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/persist-email', name: 'app_persist_email', methods: ['POST'])]
class PersistEmailController extends AbstractController
{
    use HandleTrait;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer,
        MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }

    public function __invoke(Request $request): Response
    {
        $emailValidationRequest = $this->serializer->deserialize($request->getContent(), EmailValidationRequest::class, JsonEncoder::FORMAT, [
            'groups' => ['adhesion-email:persist'],
        ]);

        $errors = $this->validator->validate($emailValidationRequest, null, ['adhesion-email:persist']);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        if ($this->handle(new PersistAdhesionEmailCommand($emailValidationRequest->getEmail()))) {
            return $this->json([
               'message' => 'OK',
               'status' => 'success',
            ], Response::HTTP_CREATED);
        }

        return $this->json([
            'message' => 'Un email de confirmation vient d’être envoyé à votre adresse email. Cliquer sur le lien de validation qu’il contient pour continuer votre adhésion.',
            'status' => 'validation',
        ], Response::HTTP_OK);
    }
}
