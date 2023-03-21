<?php

namespace App\Controller\EnMarche\Filesystem;

use App\AdherentSpace\AdherentSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_CANDIDATE') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_FILES'))")
 */
#[Route(path: '/espace-candidat', name: 'app_candidate_files_', methods: ['GET'])]
class CandidateFilesController extends AbstractFilesController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::CANDIDATE;
    }
}
