<?php

namespace Tests\App\Validator\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\PostAddress;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Membership\ActivityPositions;
use App\Repository\AdherentRepository;
use App\Validator\TerritorialCouncil\UniqueTerritorialCouncilMember;
use App\Validator\TerritorialCouncil\UniqueTerritorialCouncilMemberValidator;
use App\ValueObject\Genders;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class UniqueTerritorialCouncilMemberValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testInvalidQualitiesThrowsException(): void
    {
        $this->validator->validate($this->createAdherent(), new UniqueTerritorialCouncilMember([
            'qualities' => 'referent',
        ]));
    }

    public function testSkipValidationIfNullValue(): void
    {
        $this->validator->validate(null, new UniqueTerritorialCouncilMember([
            'qualities' => ['referent'],
        ]));

        $this->assertNoViolation();
    }

    public function testSkipValidationIfNoTerritorialCouncilMembership(): void
    {
        $this->validator->validate(new Adherent(), new UniqueTerritorialCouncilMember([
            'qualities' => ['referent'],
        ]));

        $this->assertNoViolation();
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testUnknownQualityThrowsException(): void
    {
        $this->validator->validate($this->createAdherent(), new UniqueTerritorialCouncilMember([
            'qualities' => ['invalid', 'lre'],
        ]));
    }

    public function testReferentMemberIsAlreadySet(): void
    {
        $adherent = new Adherent();
        $territorialCouncil = new TerritorialCouncil('New TC');
        $quality = new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::REFERENT, 'My zone');
        $territorialCouncilMemberInfo = new TerritorialCouncilMembership($territorialCouncil);
        $territorialCouncilMemberInfo->addQuality($quality);
        $adherent->setTerritorialCouncilMembership($territorialCouncilMemberInfo);
        $foundAdherent = Adherent::create(
            Uuid::uuid4(),
            'test@email.com',
            'bonsoir',
            Genders::MALE,
            'Pierre',
            'SL',
            new \DateTime('1990-01-01'),
            ActivityPositions::EMPLOYED,
            PostAddress::createFrenchAddress('92-98 Boulevard Victor Hugo', '92110-92024')
        );

        $this->validator = $this->createCustomValidatorSuccess($territorialCouncil, $adherent, $foundAdherent, 'referent');
        $this->validator->initialize($this->context);
        $this->validator->validate($adherent, new UniqueTerritorialCouncilMember([
            'qualities' => ['referent'],
        ]));

        $this
            ->buildViolation('territorial_council.member.unique')
            ->setParameter('{{ adherent }}', 'Pierre SL (test@email.com)')
            ->setParameter(
                '{{ quality }}', 'Membre en qualité de référent'
            )
            ->setParameter('{{ territorialCouncil }}', $territorialCouncil)
            ->assertRaised()
        ;
    }

    protected function createValidator()
    {
        return $this->createCustomValidatorFail();
    }

    protected function createCustomValidatorFail()
    {
        $adherentRepository = $this->createMock(AdherentRepository::class);
        $translator = $this->createMock(TranslatorInterface::class);

        return new UniqueTerritorialCouncilMemberValidator($adherentRepository, $translator);
    }

    protected function createCustomValidatorSuccess(
        TerritorialCouncil $territorialCouncil,
        Adherent $adherent,
        Adherent $foundAdherent,
        string $quality
    ): UniqueTerritorialCouncilMemberValidator {
        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository
            ->expects($this->once())
            ->method('findByTerritorialCouncilAndQuality')
            ->with($territorialCouncil, 'referent', $adherent)
            ->willReturn($foundAdherent)
        ;
        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->once())
            ->method('trans')
            ->with(UniqueTerritorialCouncilMember::QUALITIES_LABELS[$quality], [], 'forms')
            ->willReturn('Membre en qualité de référent')
        ;

        return new UniqueTerritorialCouncilMemberValidator($adherentRepository, $translator);
    }

    private function createAdherent(): Adherent
    {
        $adherent = new Adherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership());

        return $adherent;
    }
}
