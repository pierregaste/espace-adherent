<?php

namespace App\Controller\EnMarche\Jecoute\Personalization;

use App\Entity\Adherent;
use App\Entity\Jecoute\Region;
use App\Jecoute\JecouteSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_JECOUTE_REGION') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_JECOUTE_REGION'))")
 */
#[Route(path: '/espace-candidat/campagne', name: 'app_jecoute_candidate_region_')]
class JecouteCandidateRegionController extends AbstractPersonalizationController
{
    protected function getSpaceName(): string
    {
        return JecouteSpaceEnum::CANDIDATE_SPACE;
    }

    protected function getZones(Adherent $adherent): array
    {
        return [$adherent->getCandidateManagedArea()->getZone()];
    }
}
