<?php

namespace Kinulab\SequenceGeneratorBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CustomSequence
{
    /**
     * @var integer|null
     */
    private $id;

    /**
     * @var string
     */
    private $libelle;

    /**
     * @var string
     */
    private $sequence_name;

    /**
     * @var string|null
     */
    private $prefix;

    /**
     * @var string|null
     */
    private $suffix;

    /**
     * @var integer
     */
    private $increment_length;

    /**
     * @var integer
     */
    private $increment_by;

    /**
     * @var boolean
     */
    private $independant_complement = false;

    /**
     * @var ArrayCollection
     */
    private $sub_sequences;

    public function __construct(){
        $this->sub_sequences = new ArrayCollection();
        $this->increment_length = 5;
        $this->increment_by = 1;
    }

    public function __toString() {
        return (string) $this->libelle;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() :?int
    {
        return $this->id;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return CustomSequence
     */
    public function setLibelle(string $libelle) :CustomSequence
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle() :string
    {
        return $this->libelle;
    }

    /**
     * Set sequenceName
     *
     * @param string $sequenceName
     *
     * @return CustomSequence
     */
    public function setSequenceName(string $sequenceName) :CustomSequence
    {
        $this->sequence_name = $sequenceName;

        return $this;
    }

    /**
     * Get sequenceName
     *
     * @return string
     */
    public function getSequenceName() :string
    {
        return $this->sequence_name;
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     *
     * @return CustomSequence
     */
    public function setPrefix(?string $prefix) :CustomSequence
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get prefix
     *
     * @return string
     */
    public function getPrefix() :?string
    {
        return $this->prefix;
    }

    /**
     * Set suffix
     *
     * @param string $suffix
     *
     * @return CustomSequence
     */
    public function setSuffix(?string $suffix) :CustomSequence
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Get suffix
     *
     * @return string
     */
    public function getSuffix() :?string
    {
        return $this->suffix;
    }

    /**
     * Set incrementLength
     *
     * @param integer $incrementLength
     *
     * @return CustomSequence
     */
    public function setIncrementLength(int $incrementLength) :CustomSequence
    {
        $this->increment_length = $incrementLength;

        return $this;
    }

    /**
     * Get incrementLength
     *
     * @return integer
     */
    public function getIncrementLength() :int
    {
        return $this->increment_length;
    }

    /**
     * Set incrementBy
     *
     * @param integer $incrementBy
     *
     * @return CustomSequence
     */
    public function setIncrementBy(int $incrementBy) :CustomSequence
    {
        $this->increment_by = $incrementBy;

        return $this;
    }

    /**
     * Get incrementBy
     *
     * @return integer
     */
    public function getIncrementBy() :int
    {
        return $this->increment_by;
    }

    /**
     * Set independantComplement
     *
     * @param boolean $independantComplement
     *
     * @return CustomSequence
     */
    public function setIndependantComplement(bool $independantComplement) :CustomSequence
    {
        $this->independant_complement = $independantComplement;

        return $this;
    }

    /**
     * Get independantComplement
     *
     * @return boolean
     */
    public function getIndependantComplement() :bool
    {
        return $this->independant_complement;
    }

    /**
     * @return Collection|CustomSequenceSub[]
     */
    public function getSubSequences(): Collection
    {
        return $this->sub_sequences;
    }

    public function addSubSequence(CustomSequenceSub $customSequenceSub): self
    {
        if (!$this->sub_sequences->contains($customSequenceSub)) {
            $this->sub_sequences[] = $customSequenceSub;
            $customSequenceSub->setCustomSequence($this);
        }

        return $this;
    }

    public function removeSubSequence(CustomSequenceSub $customSequenceSub): self
    {
        if ($this->sub_sequences->contains($customSequenceSub)) {
            $this->sub_sequences->removeElement($customSequenceSub);
            // set the owning side to null (unless already changed)
            if ($customSequenceSub->getCustomSequence() === $this) {
                $customSequenceSub->setCustomSequence(null);
            }
        }

        return $this;
    }

}
