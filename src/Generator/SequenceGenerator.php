<?php
declare(strict_types=1);

namespace Kinulab\SequenceGeneratorBundle\Generator;

use Doctrine\DBAL\Schema\Sequence;
use Kinulab\SequenceGeneratorBundle\Entity\CustomSequence;
use Kinulab\SequenceGeneratorBundle\Repository\CustomSequenceRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SequenceGenerator
{

    protected $object;
    protected $repository;

    public function __construct(CustomSequenceRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * Fetch and increment the next value of the sequence
     * @param string $sequence_name
     * @param null|object $object
     * @return string
     * @throws \Exception
     */
    public function getNextVal(string $sequence_name, ?object $object = null) :string
    {
        $this->object = $object;
        /** @var CustomSequence $Sequence */
        $Sequence = $this->repository->findOneBySequenceName($sequence_name);

        if(!$Sequence){
            throw new \Exception(sprintf("The sequence '%s' is not defined yet", $sequence_name));
        }

        $prefix = $this->formatString($Sequence->getPrefix());
        $suffix = $this->formatString($Sequence->getSuffix());

        $sql_sequence_name = $Sequence->getSequenceName();
        if($Sequence->getIndependantComplement()){
            $subSequence = $this->repository->getSubSequence($Sequence, $prefix, $suffix);
            $sql_sequence_name = $subSequence->getSqlSequenceName();
        }

        $increment = $this->repository->getNextValue($sql_sequence_name);

        return $prefix.str_pad(strval($increment), $Sequence->getIncrementLength(), '0', STR_PAD_LEFT).$suffix;
    }

    /**
     * Initialize or update a sequence
     * @param CustomSequence $CustomSequence
     * @param null|int $start
     */
    public function initializeSequence(CustomSequence $CustomSequence, ?int $start = null) :void
    {

        $Sequence = $this->repository->getSequenceSQLSchemaByName($CustomSequence->getSequenceName());

        // on crée la séquence sur la base quand c'est une nouvelle
        if(!$Sequence){
            $Sequence = new Sequence($CustomSequence->getSequenceName());
            $Sequence->setAllocationSize($CustomSequence->getIncrementBy());
            if($start){
                $Sequence->setInitialValue($start);
            }

            $this->repository->createSQLSequence($Sequence);
        }else{
            $Sequence->setAllocationSize($CustomSequence->getIncrementBy());
            $this->repository->alterSQLSequence($Sequence);
        }
    }

    /**
     * Remove a sequence
     * @param CustomSequence $CustomSequence
     */
    public function removeSequence(CustomSequence $CustomSequence) :void
    {
        $this->repository->removeSQLSequence($CustomSequence);
    }

    /**
     * Remplace a marker with it's values
     * @param type $matches
     * @return type
     */
    public function replace(array $matches) :string
    {
        switch ($matches[1]){
            case 'year':
                return date('Y');
            case 'y':
                return date('y');
            case 'month':
                return date('m');
            case 'day':
                return date('d');
            case 'doy':
                return date('z');
            case 'woy':
                return date('W');
            case 'weekday':
                return date('w');
            case 'h24':
                return date('H');
            case 'h12':
                return date('h');
            case 'min':
                return date('i');
            case 'sec':
                return date('s');
        }

        if($this->object && preg_match('#^object\.(.+)$#', $matches[1], $match_attrs)){
            $accessor = PropertyAccess::createPropertyAccessor();
            return $accessor->getValue($this->object, $match_attrs[1]);
        }

        return $matches[0];
    }

    /**
     * Replace all markers in string
     * @param type $string
     * @return type
     */
    private function formatString(?string $string) :?string
    {
        return $string === null ? null : preg_replace_callback('#\%([^\%]+)\%#', [$this, 'replace'], $string);
    }

}