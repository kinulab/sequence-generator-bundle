<?php

declare(strict_types=1);

namespace Kinulab\SequenceGeneratorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(name: 'sys_custom_sequence_sub')]
class CustomSequenceSub
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CustomSequence::class, inversedBy: 'subSequences')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?CustomSequence $customSequence = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prefix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $suffix = null;

    public function getSqlSequenceName() :string
    {
        return $this->customSequence->getSequenceName().'_'.$this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomSequence(): ?CustomSequence
    {
        return $this->customSequence;
    }

    public function setCustomSequence(CustomSequence $customSequence): static
    {
        $this->customSequence = $customSequence;

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(?string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function setSuffix(?string $suffix): static
    {
        $this->suffix = $suffix;

        return $this;
    }

}
