<?php

namespace App\Mailchimp\Synchronisation;

use App\Address\Address;
use App\Collection\CommitteeMembershipCollection;
use App\Entity\Adherent;
use App\Entity\ApplicationRequest\ApplicationRequest;
use App\Entity\ApplicationRequest\VolunteerRequest;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\Geo\Zone;
use App\Entity\Geo\ZoneTagEnum;
use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Entity\SubscriptionType;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\MailchimpSegment\MailchimpSegmentTagEnum;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Request\MemberRequest;
use App\Mailchimp\Synchronisation\Request\MemberTagsRequest;
use App\Repository\ReferentTagRepository;
use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Ramsey\Uuid\UuidInterface;

class RequestBuilder implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $email;
    private $gender;
    private $firstName;
    private $lastName;
    private ?\DateTimeInterface $birthDay = null;
    private $city;
    private $zipCode;
    private $countryName;
    private $adhesionDate;
    private ?bool $isCertified = null;
    private ?bool $isAdherent = null;
    private ?string $committeeUuid = null;

    private $interests;
    private ?\DateTime $lastMembershipDonation = null;
    private ?string $source = null;

    private $activeTags = [];
    private $inactiveTags = [];
    private $favoriteCities;
    private $favoriteCitiesCodes;
    private $takenForCity = false;
    private $mailchimpObjectIdMapping;
    private $electedRepresentativeTagsBuilder;
    private $isSubscribeRequest = true;
    private $referentTagsCodes = [];

    /** @var Zone[] */
    private array $subZones = [];
    /** @var Zone[] */
    private array $zones = [];

    private $teamCode;

    private $codeCanton;
    private $codeDepartment;
    private $codeRegion;

    private ?string $loginGroup = null;

    public function __construct(
        MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        ElectedRepresentativeTagsBuilder $electedRepresentativeTagsBuilder
    ) {
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
        $this->electedRepresentativeTagsBuilder = $electedRepresentativeTagsBuilder;
        $this->logger = new NullLogger();
    }

    public function createReplaceEmailRequest(string $oldEmail, string $newEmail): MemberRequest
    {
        $request = new MemberRequest($oldEmail);
        $request->setEmailAddress($newEmail);

        return $request;
    }

    public function updateFromAdherent(Adherent $adherent): self
    {
        $this
            ->setEmail($adherent->getEmailAddress())
            ->setGender($adherent->getGender())
            ->setFirstName($adherent->getFirstName())
            ->setLastName($adherent->getLastName())
            ->setBirthDay($adherent->getBirthdate())
            ->setZipCode($adherent->getPostalCode())
            ->setCity($adherent->getCityName())
            ->setCountryName($adherent->getCountryName())
            ->setAdhesionDate($adherent->getRegisteredAt())
            ->setLastMembershipDonation($adherent->getLastMembershipDonation())
            ->setSource($adherent->getSource())
            ->setActiveTags($this->getAdherentActiveTags($adherent))
            ->setInactiveTags($this->getInactiveTags($adherent))
            ->setIsSubscribeRequest($adherent->isEnabled() && $adherent->isEmailSubscribed())
            ->setZones($adherent->getZones())
            ->setCommitteeUuid($adherent->getCommitteeV2Membership()?->getCommitteeUuid())
        ;

        if (null === $adherent->getSource() || $adherent->isRenaissanceUser()) {
            $this
                ->setTeamCode($adherent)
                ->setIsCertified($adherent->isCertified())
                ->setLoginGroup($adherent->getLastLoginGroup())
                ->setInterests($this->buildInterestArray($adherent))
            ;
        }

        return $this;
    }

    public function updateFromElectedRepresentative(ElectedRepresentative $electedRepresentative): self
    {
        return $this
            ->setEmail($electedRepresentative->getEmailAddress())
            ->setGender($electedRepresentative->getGender())
            ->setFirstName($electedRepresentative->getFirstName())
            ->setLastName($electedRepresentative->getLastName())
            ->setBirthDay($electedRepresentative->getBirthDate())
            ->setIsAdherent($electedRepresentative->isAdherent())
            ->setActiveTags($this->electedRepresentativeTagsBuilder->buildTags($electedRepresentative))
            ->setIsSubscribeRequest(false === $electedRepresentative->isEmailUnsubscribed())
        ;
    }

    public function updateFromApplicationRequest(ApplicationRequest $applicationRequest): self
    {
        $activeTags = $applicationRequest instanceof VolunteerRequest ?
            [ApplicationRequestTagLabelEnum::VOLUNTEER_LABEL]
            : [ApplicationRequestTagLabelEnum::RUNNING_MATE_LABEL];

        if ($applicationRequest->isAdherent()) {
            $activeTags[] = ApplicationRequestTagLabelEnum::ADHERENT_LABEL;
        }

        return $this
            ->setEmail($applicationRequest->getEmailAddress())
            ->setGender($applicationRequest->getGender())
            ->setFirstName($applicationRequest->getFirstName())
            ->setLastName($applicationRequest->getLastName())
            ->setFavoriteCities($applicationRequest->getFavoriteCitiesNames())
            ->setFavoriteCitiesCodes($applicationRequest->getFavoriteCityPrefixedCodes())
            ->setReferentTagCodes($applicationRequest->getReferentTagsCodes())
            ->setTakenForCity($applicationRequest->getTakenForCity())
            ->setActiveTags($activeTags)
        ;
    }

    public function updateFromDataSurvey(JemarcheDataSurvey $dataSurvey, array $zones): self
    {
        $this
            ->setEmail($dataSurvey->getEmailAddress())
            ->setFirstName($dataSurvey->getFirstName())
            ->setLastName($dataSurvey->getLastName())
            ->setZipCode($dataSurvey->getPostalCode())
        ;

        foreach ($zones as $zone) {
            $this->setZoneCode($zone);
        }

        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function setBirthDay(?\DateTimeInterface $birthDay): self
    {
        $this->birthDay = $birthDay;

        return $this;
    }

    public function setIsCertified(bool $isCertified = null): self
    {
        $this->isCertified = $isCertified;

        return $this;
    }

    public function setCommitteeUuid(?UuidInterface $committeeUuid): self
    {
        $this->committeeUuid = $committeeUuid ? $committeeUuid->toString() : '';

        return $this;
    }

    public function setIsAdherent(bool $isAdherent = null): self
    {
        $this->isAdherent = $isAdherent;

        return $this;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function setCountryName(?string $countryName): self
    {
        $this->countryName = $countryName;

        return $this;
    }

    public function setAdhesionDate(?\DateTimeInterface $adhesionDate): self
    {
        $this->adhesionDate = $adhesionDate;

        return $this;
    }

    public function setLastMembershipDonation(?\DateTimeInterface $lastMembershipDonation): self
    {
        $this->lastMembershipDonation = $lastMembershipDonation;

        return $this;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function setInterests(array $interests): self
    {
        $this->interests = $interests;

        return $this;
    }

    public function setActiveTags(array $activeTags): self
    {
        $this->activeTags = $activeTags;

        return $this;
    }

    public function setInactiveTags(array $inactiveTags): self
    {
        $this->inactiveTags = $inactiveTags;

        return $this;
    }

    public function setIsSubscribeRequest(bool $isSubscribeRequest): self
    {
        $this->isSubscribeRequest = $isSubscribeRequest;

        return $this;
    }

    public function setFavoriteCities(array $favoriteCities): self
    {
        $this->favoriteCities = $favoriteCities;

        return $this;
    }

    public function setFavoriteCitiesCodes(array $favoriteCitiesCodes): self
    {
        $this->favoriteCitiesCodes = $favoriteCitiesCodes;

        return $this;
    }

    public function setReferentTagCodes(array $codes): self
    {
        $this->referentTagsCodes = $codes;

        return $this;
    }

    public function setTakenForCity(?string $takenForCity): self
    {
        $this->takenForCity = $takenForCity;

        return $this;
    }

    public function setLoginGroup(?string $loginGroup): self
    {
        $this->loginGroup = $loginGroup;

        return $this;
    }

    /**
     * @param Collection|Zone[] $zones
     */
    public function setZones(Collection $zones): self
    {
        foreach ($zones as $zone) {
            $this->setZone($zone);
        }

        foreach ($zones as $zone) {
            foreach ($zone->getParents() as $parent) {
                $this->setZone($parent);
            }
        }

        return $this;
    }

    private function setZone(Zone $zone): void
    {
        if (!$zone->isActive()) {
            return;
        }

        if ($zone->hasTag(ZoneTagEnum::SUB_ZONE)) {
            if (!isset($this->subZones[$zone->getType()])) {
                $this->subZones[$zone->getType()] = $zone;
            }

            return;
        }

        if (!isset($this->zones[$zone->getType()])) {
            $this->zones[$zone->getType()] = $zone;
        }
    }

    private function setZoneCode(Zone $zone): void
    {
        switch ($zone->getType()) {
            case Zone::CANTON:
                $this->codeCanton = $zone->getCode();

                break;
            case Zone::DEPARTMENT:
                $this->codeDepartment = $zone->getCode();

                break;
            case Zone::REGION:
                $this->codeRegion = $zone->getCode();

                break;
            default:
                break;
        }
    }

    public function setTeamCode(Adherent $adherent): self
    {
        if ($adherent->isParisResident()) {
            $zones = $adherent->getZonesOfType(Zone::BOROUGH);
        } else {
            $zones = $adherent->getParentZonesOfType($adherent->isForeignResident() ? Zone::FOREIGN_DISTRICT : Zone::DEPARTMENT);
        }

        $count = \count($zones);
        if ($count > 1) {
            $this->logger->warning(sprintf('Cannot find only one geo zone for Mailchimp for adherent with id "%s"', $adherent->getId()));
        }
        $this->teamCode = (1 === $count) ? current($zones)->getTeamCode() : null;

        return $this;
    }

    public function buildMemberRequest(string $memberIdentifier): MemberRequest
    {
        $request = new MemberRequest($memberIdentifier);

        if ($this->email) {
            $request->setEmailAddress($this->email);
        }

        if (false === $this->isSubscribeRequest) {
            $request->setUnsubscriptionRequest();
        }

        $request->setMergeFields($this->buildMergeFields());

        if ($this->interests) {
            $request->setInterests($this->interests);
        }

        return $request;
    }

    public function createMemberTagsRequest(string $memberIdentifier, array $removedTags = []): MemberTagsRequest
    {
        $request = new MemberTagsRequest($memberIdentifier);

        foreach (array_merge($removedTags, $this->inactiveTags) as $tagName) {
            $request->addTag($tagName, false);
        }

        foreach ($this->activeTags as $tagName) {
            $request->addTag($tagName);
        }

        return $request;
    }

    private function buildMergeFields(): array
    {
        $mergeFields = [];

        if ($this->gender) {
            $mergeFields[MemberRequest::MERGE_FIELD_GENDER] = $this->gender;
        }

        if ($this->firstName) {
            $mergeFields[MemberRequest::MERGE_FIELD_FIRST_NAME] = $this->firstName;
        }

        if ($this->lastName) {
            $mergeFields[MemberRequest::MERGE_FIELD_LAST_NAME] = $this->lastName;
        }

        if ($this->birthDay) {
            $mergeFields[MemberRequest::MERGE_FIELD_BIRTHDATE] = $this->birthDay->format(MemberRequest::DATE_FORMAT);
        }

        if (null !== $this->isCertified) {
            $mergeFields[MemberRequest::MERGE_FIELD_CERTIFIED] = $this->isCertified ? 'oui' : 'non';
        }

        if (null !== $this->committeeUuid) {
            $mergeFields[MemberRequest::MERGE_FIELD_COMMITTEE] = $this->committeeUuid;
        }

        if (null !== $this->isAdherent) {
            $mergeFields[MemberRequest::MERGE_FIELD_ADHERENT] = $this->isAdherent ? 'oui' : 'non';
        }

        if ($this->lastMembershipDonation) {
            $mergeFields[MemberRequest::MERGE_FIELD_LAST_MEMBERSHIP_DONATION] = $this->lastMembershipDonation->format(MemberRequest::DATE_FORMAT);
        }

        if ($this->source) {
            $mergeFields[MemberRequest::MERGE_FIELD_SOURCE] = $this->source;
        }

        if ($this->city) {
            $mergeFields[MemberRequest::MERGE_FIELD_CITY] = sprintf('%s (%s)', $this->city, $this->zipCode);
        }

        if ($this->zipCode) {
            $mergeFields[MemberRequest::MERGE_FIELD_ZIP_CODE] = $this->zipCode;
        }

        if ($this->countryName) {
            $mergeFields[MemberRequest::MERGE_FIELD_COUNTRY] = $this->countryName;
        }

        if ($this->adhesionDate) {
            $mergeFields[MemberRequest::MERGE_FIELD_ADHESION_DATE] = $this->adhesionDate->format(MemberRequest::DATE_FORMAT);
        }

        if ($this->favoriteCities) {
            $mergeFields[MemberRequest::MERGE_FIELD_FAVORITE_CITIES] = implode(',', $this->favoriteCities);
            $mergeFields[MemberRequest::MERGE_FIELD_FAVORITE_CITIES_CODES] = implode(',', $this->favoriteCitiesCodes);
        }

        if ($this->referentTagsCodes) {
            $mergeFields[MemberRequest::MERGE_FIELD_REFERENT_TAGS] = implode(',', $this->referentTagsCodes);
        }

        if (false !== $this->takenForCity) {
            $mergeFields[MemberRequest::MERGE_FIELD_MUNICIPAL_TEAM] = (string) $this->takenForCity;
        }

        // Fill Zone merge field
        foreach (MemberRequest::ZONE_MERGE_FIELD_BY_ZONE_TYPE as $mergeField => $zoneType) {
            $mergeFields[$mergeField] = (string) ($this->zones[$zoneType] ?? null);
        }

        // Complete Zone merge field with sub zones (zone uses Zone tag `sub_zone`)
        foreach ($this->subZones as $zone) {
            $mergeField = array_search($zone->getType(), MemberRequest::ZONE_MERGE_FIELD_BY_ZONE_TYPE);
            if (!$mergeField) {
                continue;
            }

            if (empty($mergeFields[$mergeField])) {
                $mergeFields[$mergeField] = (string) $zone;
            } else {
                $mergeFields[$mergeField] .= sprintf(' (%s)', $zone->getCode());
            }
        }

        if ($this->codeCanton) {
            $mergeFields[MemberRequest::MERGE_FIELD_CODE_CANTON] = $this->codeCanton;
        }

        if ($this->codeDepartment) {
            $mergeFields[MemberRequest::MERGE_FIELD_CODE_DEPARTMENT] = $this->codeDepartment;
        }

        if ($this->codeRegion) {
            $mergeFields[MemberRequest::MERGE_FIELD_CODE_REGION] = $this->codeRegion;
        }

        if ($this->teamCode) {
            $mergeFields[MemberRequest::MERGE_FIELD_TEAM_CODE] = (string) $this->teamCode;
        }

        if ($this->loginGroup) {
            $mergeFields[MemberRequest::MERGE_FIELD_LAST_LOGIN_GROUP] = $this->loginGroup;
        }

        return $mergeFields;
    }

    private function buildInterestArray(Adherent $adherent): array
    {
        return array_replace(
            // By default all interests are disabled (`false` value) for a member
            array_fill_keys($ids = $this->mailchimpObjectIdMapping->getInterestIds(), false),

            // Activate adherent's interests
            array_fill_keys(
                array_intersect_key(
                    $ids,
                    array_flip($adherent->getInterests())
                ),
                true
            ),

            /*
             * Activate Notification group interests.
             *
             * This is a hack to migrate progressively the ID stored
             * into DB (subscription_types.external_id column), after that we will be able to use this method:
             * array_fill_keys(array_intersect($this->mailchimpInterestIds, $adherent->getSubscriptionExternalIds()), true),
             */
            array_fill_keys(
                array_intersect_key(
                    $ids,
                    array_flip(
                        array_map(
                            static function (SubscriptionType $type) { return $type->getCode(); },
                            $adherent->getSubscriptionTypes()
                        )
                    )
                ),
                true
            ),

            // Activate Member group interest
            array_fill_keys(
                array_intersect_key(
                    $ids,
                    array_filter([
                        Manager::INTEREST_KEY_COMMITTEE_SUPERVISOR => $adherent->isSupervisor(false),
                        Manager::INTEREST_KEY_COMMITTEE_PROVISIONAL_SUPERVISOR => $adherent->isSupervisor(true),
                        Manager::INTEREST_KEY_COMMITTEE_HOST => !($memberships = $adherent->getMemberships())->getCommitteeHostMemberships(CommitteeMembershipCollection::EXCLUDE_SUPERVISORS)->isEmpty(),
                        Manager::INTEREST_KEY_COMMITTEE_FOLLOWER => $isFollower = !$memberships->getCommitteeFollowerMembershipsNotWaitingForApproval()->isEmpty(),
                        Manager::INTEREST_KEY_COMMITTEE_NO_FOLLOWER => !$isFollower,
                        Manager::INTEREST_KEY_REFERENT => $adherent->isReferent(),
                        Manager::INTEREST_KEY_DEPUTY => $adherent->isDeputy(),
                        Manager::INTEREST_KEY_COORDINATOR => $adherent->isRegionalCoordinator(),
                        Manager::INTEREST_KEY_PROCURATION_MANAGER => $adherent->isProcurationManager(),
                        Manager::INTEREST_KEY_ASSESSOR_MANAGER => $adherent->isAssessorManager(),
                        Manager::INTEREST_KEY_BOARD_MEMBER => $adherent->isBoardMember(),
                    ])
                ),
                true
            )
        );
    }

    private function getAdherentActiveTags(Adherent $adherent): array
    {
        $tags = $adherent->getReferentTagCodes();

        if (Address::FRANCE !== $adherent->getCountry()) {
            $tags[] = ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG;
        }

        if ($adherent->isCertified()) {
            $tags[] = MailchimpSegmentTagEnum::CERTIFIED;
        }

        if ($adherent->hasVotingCommitteeMembership()) {
            $tags[] = MailchimpSegmentTagEnum::COMMITTEE_VOTER;
        }

        if ($adherent->hasTerritorialCouncilMembership()) {
            $tags[] = $adherent->getTerritorialCouncilMembership()->getTerritorialCouncil()->getUuid()->toString();
        }

        return $tags;
    }

    private function getInactiveTags(Adherent $adherent): array
    {
        $tags = [];

        if (Address::FRANCE === $adherent->getCountry()) {
            $tags[] = ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG;
        }

        if (!$adherent->isCertified()) {
            $tags[] = MailchimpSegmentTagEnum::CERTIFIED;
        }

        if (!$adherent->hasVotingCommitteeMembership()) {
            $tags[] = MailchimpSegmentTagEnum::COMMITTEE_VOTER;
        }

        return $tags;
    }
}
