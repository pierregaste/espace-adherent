<?php

namespace App\Entity;

use App\EntityListener\RedirectionListener;
use App\Repository\RedirectionRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'redirections')]
#[ORM\Entity(repositoryClass: RedirectionRepository::class)]
#[ORM\EntityListeners([RedirectionListener::class])]
class Redirection
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Length(max=10000)
     */
    #[ORM\Column(name: 'url_from', type: 'text')]
    private $from;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Length(max=10000)
     */
    #[ORM\Column(name: 'url_to', type: 'text')]
    private $to;

    /**
     * @var int|null
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={301, 302})
     */
    #[ORM\Column(type: 'integer')]
    private $type;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     */
    #[ORM\Column(type: 'datetime')]
    private $updatedAt;

    public function __toString()
    {
        return 'Redirection from '.$this->from.' to '.$this->to;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(?string $from)
    {
        $this->from = $from;
    }

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function setTo(?string $to)
    {
        $this->to = $to;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type)
    {
        $this->type = $type;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}
