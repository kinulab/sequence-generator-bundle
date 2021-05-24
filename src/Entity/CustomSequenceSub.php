<?php

namespace Kinulab\SequenceGeneratorBundle\Entity;

class CustomSequenceSub
{
    /**
     * @var integer|null
     */
    private $id;

    /**
     * @var CustomSequence
     */
    private $custom_sequence;

    /**
     * @var string|null
     */
    private $prefix;

    /**
     * @var string|null
     */
    private $suffix;

    public function getSqlSequenceName() :string
    {
        return $this->custom_sequence->getSequenceName().'_'.$this->id;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return CustomSequence
     */
    public function getCustomSequence(): CustomSequence
    {
        return $this->custom_sequence;
    }

    /**
     * @param CustomSequence $custom_sequence
     */
    public function setCustomSequence(CustomSequence $custom_sequence): void
    {
        $this->custom_sequence = $custom_sequence;
    }

    /**
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @param string|null $prefix
     */
    public function setPrefix(?string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string|null
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * @param string|null $suffix
     */
    public function setSuffix(?string $suffix): void
    {
        $this->suffix = $suffix;
    }

}
