<?php

namespace App\Entity\Pap;

use App\Entity\Adherent;
use App\Entity\AuthorInterface;
use App\Entity\EntityIdentityTrait;
use App\Repository\Pap\BuildingEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'pap_building_event')]
#[ORM\Entity(repositoryClass: BuildingEventRepository::class)]
class BuildingEvent implements AuthorInterface
{
    use EntityIdentityTrait;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(
     *     callback={"App\Pap\BuildingEventActionEnum", "toArray"}
     * )
     */
    #[Groups(['pap_building_event_write'])]
    #[ORM\Column(length: 25)]
    private ?string $action = null;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(
     *     callback={"App\Pap\BuildingEventTypeEnum", "toArray"}
     * )
     */
    #[Groups(['pap_building_event_write'])]
    #[ORM\Column(length: 25)]
    private ?string $type = null;

    #[Groups(['pap_building_event_write'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $identifier = null;

    /**
     * @Assert\NotNull
     */
    #[ORM\ManyToOne(targetEntity: Building::class)]
    private Building $building;

    /**
     * @Assert\NotNull
     */
    #[Groups(['pap_building_event_write'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Campaign::class)]
    private ?Campaign $campaign = null;

    /**
     * @Gedmo\Timestampable(on="create")
     */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, fetch: 'EAGER')]
    private $author;

    #[Groups(['pap_building_event_write'])]
    #[ORM\Column(nullable: true)]
    public ?string $closeType = null;

    #[Groups(['pap_building_event_write'])]
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    public ?int $programs = null;

    public function __construct(
        Building $building,
        ?Campaign $campaign = null,
        ?string $action = null,
        ?string $type = null,
        ?string $identifier = null
    ) {
        $this->uuid = Uuid::uuid4();
        $this->building = $building;
        $this->campaign = $campaign;
        $this->action = $action;
        $this->type = $type;
        $this->identifier = $identifier;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier = null): void
    {
        $this->identifier = $identifier;
    }

    public function getBuilding(): Building
    {
        return $this->building;
    }

    public function setBuilding(Building $building): void
    {
        $this->building = $building;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(?Adherent $author): void
    {
        $this->author = $author;
    }
}
