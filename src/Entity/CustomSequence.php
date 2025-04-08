<?php

declare(strict_types=1);

namespace Kinulab\SequenceGeneratorBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Kinulab\SequenceGeneratorBundle\Repository\SequenceRepository;

#[ORM\Entity(repositoryClass: SequenceRepository::class)]
#[ORM\Table(name: 'sys_custom_sequence')]
class CustomSequence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $sequenceName =null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prefix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $suffix = null;

    #[ORM\Column(type: 'integer')]
    private int $incrementLength = 5;

    #[ORM\Column(type: 'integer')]
    private int $incrementBy = 1;

    #[ORM\Column(type: 'boolean')]
    private bool $independantComplement = false;

    #[ORM\OneToMany(mappedBy: 'customSequence', targetEntity: CustomSequenceSub::class)]
    private Collection $subSequences;

    public function __construct(){
        $this->subSequences = new ArrayCollection();
    }

    public function __toString() :string
    {
        return (string) $this->libelle;
    }

    public function getId() :?int
    {
        return $this->id;
    }

    public function setLibelle(string $libelle) :static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getLibelle() :?string
    {
        return $this->libelle;
    }

    public function setSequenceName(string $sequenceName) :static
    {
        $this->sequenceName = $sequenceName;

        return $this;
    }

    public function getSequenceName() :?string
    {
        return $this->sequenceName;
    }

    public function setPrefix(?string $prefix) :static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getPrefix() :?string
    {
        return $this->prefix;
    }

    public function setSuffix(?string $suffix) :static
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function getSuffix() :?string
    {
        return $this->suffix;
    }

    public function setIncrementLength(int $incrementLength) :static
    {
        $this->incrementLength = $incrementLength;

        return $this;
    }

    public function getIncrementLength() :int
    {
        return $this->incrementLength;
    }

    public function setIncrementBy(int $incrementBy) :static
    {
        $this->incrementBy = $incrementBy;

        return $this;
    }

    public function getIncrementBy() :int
    {
        return $this->incrementBy;
    }

    public function setIndependantComplement(bool $independantComplement) :static
    {
        $this->independantComplement = $independantComplement;

        return $this;
    }

    public function getIndependantComplement() :bool
    {
        return $this->independantComplement;
    }

    public function getSubSequences(): Collection
    {
        return $this->subSequences;
    }

    public function addSubSequence(CustomSequenceSub $customSequenceSub): static
    {
        if (!$this->subSequences->contains($customSequenceSub)) {
            $this->subSequences[] = $customSequenceSub;
            $customSequenceSub->setCustomSequence($this);
        }

        return $this;
    }

    public function removeSubSequence(CustomSequenceSub $customSequenceSub): static
    {
        if ($this->subSequences->contains($customSequenceSub)) {
            $this->subSequences->removeElement($customSequenceSub);
            // set the owning side to null (unless already changed)
            if ($customSequenceSub->getCustomSequence() === $this) {
                $customSequenceSub->setCustomSequence(null);
            }
        }

        return $this;
    }

}
