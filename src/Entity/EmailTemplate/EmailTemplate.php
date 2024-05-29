<?php

namespace App\Entity\EmailTemplate;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Collection\ZoneCollection;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\UnlayerJsonContentTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     routePrefix="/v3",
 *     attributes={
 *         "order": {"createdAt": "DESC"},
 *         "security": "is_granted('ROLE_MESSAGE_REDACTOR')",
 *         "normalization_context": {
 *             "groups": {"email_template_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"email_template_write"}
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/email_templates",
 *             "normalization_context": {
 *                 "groups": {"email_template_list_read"}
 *             },
 *         },
 *         "post": {
 *             "path": "/email_templates",
 *             "normalization_context": {
 *                 "groups": {"email_template_read_restricted"}
 *             },
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/email_templates/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_MESSAGE_REDACTOR') and is_granted('CAN_READ_EMAIL_TEMPLATE', object)",
 *         },
 *         "put": {
 *             "path": "/email_templates/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_MESSAGE_REDACTOR') and object.getCreatedByAdherent() and (object.getCreatedByAdherent() == user or user.hasDelegatedFromUser(object.getCreatedByAdherent(), 'messages'))",
 *             "normalization_context": {
 *                 "groups": {"email_template_read_restricted"}
 *             },
 *         },
 *         "delete": {
 *             "path": "/email_templates/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_MESSAGE_REDACTOR') and object.getCreatedByAdherent() and (object.getCreatedByAdherent() == user or user.hasDelegatedFromUser(object.getCreatedByAdherent(), 'messages'))",
 *         },
 *     },
 * )
 */
#[ORM\Table(name: 'email_templates')]
#[ORM\Entity]
class EmailTemplate implements EntityAdherentBlameableInterface, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use UnlayerJsonContentTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    #[Groups(['email_template_read', 'email_template_write', 'email_template_list_read'])]
    #[ORM\Column]
    private ?string $label = null;

    /**
     * @Assert\Length(max="255")
     */
    #[Groups(['email_template_read'])]
    #[ORM\Column(nullable: true)]
    public ?string $subject = null;

    #[Groups(['email_template_read'])]
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $subjectEditable = true;

    /**
     * @Assert\NotBlank
     */
    #[Groups(['email_template_read', 'email_template_write'])]
    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    /**
     * @var Collection|Zone[]
     */
    #[ORM\JoinTable(name: 'email_template_zone')]
    #[ORM\ManyToMany(targetEntity: Zone::class, cascade: ['persist'])]
    private Collection $zones;

    /**
     * @var string[]|null
     *
     * @Assert\NotBlank(groups={"Admin"})
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private ?array $scopes = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $isStatutory = false;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->zones = new ZoneCollection();
    }

    public function __toString(): string
    {
        return (string) $this->label;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getZones(): Collection
    {
        return $this->zones;
    }

    public function addZone(Zone $zone): void
    {
        if (!$this->zones->contains($zone)) {
            $this->zones->add($zone);
        }
    }

    public function removeZone(Zone $zone): void
    {
        $this->zones->removeElement($zone);
    }

    public function getScopes(): ?array
    {
        return $this->scopes;
    }

    public function setScopes(?array $scopes): void
    {
        $this->scopes = $scopes;
    }

    #[Groups(['email_template_read', 'email_template_list_read'])]
    public function isFromAdmin(): bool
    {
        return null !== $this->getCreatedByAdministrator();
    }
}
