<?php

namespace App\Controller\Renaissance\ElectedRepresentative\Contribution;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-elus/cotisation/montant", name="app_renaissance_elected_representative_contribution_see_amount", methods={"GET"})
 */
class SeeAmountController extends AbstractContributionController
{
    public function __invoke(Request $request): Response
    {
        $this->checkContributionsEnabled();

        $command = $this->getCommand();

        if (!$this->processor->canSeeContributionAmount($command)) {
            return $this->redirectToRoute('app_renaissance_elected_representative_contribution_fill_revenue');
        }

        $this->processor->doSeeContributionAmount($command);

        return $this->render('renaissance/elected_representative/contribution/see_amount.html.twig', [
            'command' => $command,
        ]);
    }
}
