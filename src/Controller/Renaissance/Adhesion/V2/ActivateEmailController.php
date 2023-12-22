<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use App\Adhesion\ActivationCodeManager;
use App\Adhesion\AdhesionStepEnum;
use App\Adhesion\Command\GenerateActivationCodeCommand;
use App\Adhesion\Exception\ActivationCodeExceptionInterface;
use App\Adhesion\Request\ValidateAccountRequest;
use App\Entity\Adherent;
use App\Form\ActivateEmailByCodeType;
use App\Form\ConfirmActionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ActivateEmailController extends AbstractController
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    #[Route(path: '/v2/adhesion/confirmation-email', name: 'app_adhesion_confirm_email', methods: ['GET', 'POST'])]
    public function validateAction(Request $request, ActivationCodeManager $activationCodeManager, EntityManagerInterface $entityManager): Response
    {
        if (!($adherent = $this->getUser()) instanceof Adherent) {
            return $this->redirectToRoute('app_adhesion_index');
        }

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::ACTIVATION)) {
            return $this->redirectToRoute('app_renaissance_adherent_space');
        }

        $validateAccountRequest = new ValidateAccountRequest();

        $form = $this
            ->createForm(ActivateEmailByCodeType::class, $validateAccountRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('validate')->isClicked()) {
                try {
                    $activationCodeManager->validate((string) $validateAccountRequest->code, $adherent);
                    $this->addFlash('success', 'Votre adresse email a bien été validée !');

                    return $this->redirectToRoute('app_renaissance_adherent_space');
                } catch (ActivationCodeExceptionInterface $e) {
                    $form->get('code')->addError(new FormError($e->getMessage()));
                }
            } elseif ($form->get('changeEmail')->isClicked()) {
                $adherent->setEmailAddress($validateAccountRequest->emailAddress);
                $entityManager->flush();
                $this->bus->dispatch(new GenerateActivationCodeCommand($adherent, true));

                $this->addFlash('success', 'Votre adresse email a bien été modifiée ! Veuillez saisir le nouveau code reçu par email.');

                return $this->redirectToRoute('app_adhesion_confirm_email');
            }
        }

        return $this->renderForm('renaissance/adhesion/confirmation_email.html.twig', [
            'code_ttl' => ActivationCodeManager::CODE_TTL,
            'request' => $validateAccountRequest,
            'form' => $form,
            'new_code_form' => $this->createForm(ConfirmActionType::class, null, ['with_deny' => false, 'allow_label' => 'Renvoyer le code']),
        ]);
    }

    #[Route(path: '/v2/adhesion/nouveau-code', name: 'app_adhesion_request_new_activation_code', methods: ['POST'])]
    public function requestNewCodeAction(Request $request): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $form = $this->createForm(ConfirmActionType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(new GenerateActivationCodeCommand($adherent));
                $this->addFlash('success', 'Un nouveau code vous a été envoyé par email.');
            } catch (HandlerFailedException $e) {
                if ($exceptions = $e->getNestedExceptionOfClass(ActivationCodeExceptionInterface::class)) {
                    $this->addFlash('error', $exceptions[0]->getMessage());
                } else {
                    throw $e;
                }
            }
        }

        return $this->redirectToRoute('app_adhesion_confirm_email');
    }
}