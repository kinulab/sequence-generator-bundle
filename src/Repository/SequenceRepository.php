<?php

declare(strict_types=1);

namespace Kinulab\SequenceGeneratorBundle\Repository;

use Kinulab\SequenceGeneratorBundle\Entity\CustomSequence;
use Kinulab\SequenceGeneratorBundle\Entity\CustomSequenceSub;
use Kinulab\SequenceGeneratorBundle\Entity\LastRun;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Schema\PostgreSqlSchemaManager;
use Doctrine\DBAL\Schema\Sequence;


class SequenceRepository
{

    protected $doctrine;

    function __construct(EntityManagerInterface $doctrine) {
        $this->doctrine = $doctrine;
    }

    public function findOneBySequenceName(string $sequenceName) :CustomSequence
    {
        return $this->doctrine->getRepository(CustomSequence::class)->findOneBy(['sequence_name' => $sequenceName]);
    }

    public function getSubSequence(CustomSequence $sequence, ?string $prefix, ?string $suffix) :CustomSequenceSub
    {
        if(!$sequence->getIndependantComplement()){
            throw new \LogicException("A sequence without independant complement doesn't have sub sequence.");
        }

        $repo = $this->doctrine->getRepository(CustomSequenceSub::class);
        $subSequence = $repo->findOneBy(['custom_sequence' => $sequence, 'prefix' => $prefix, 'suffix' => $suffix]);
        if(!$subSequence){ // on crée la subsequence
            $tableName = $this->doctrine->getClassMetadata(CustomSequenceSub::class)->getTableName();
            $sql = sprintf("INSERT INTO %s (id, custom_sequence_id, prefix, suffix) VALUES (nextval('%s_id_seq'), :sequence_id, :prefix, :suffix)", $tableName, $tableName);
            $this->doctrine->getConnection()->executeQuery($sql, ['sequence_id' => $sequence->getId(), 'prefix' => $prefix, 'suffix' => $suffix]);

            $subSequence = $repo->findOneBy(['custom_sequence' => $sequence, 'prefix' => $prefix, 'suffix' => $suffix]);

            $sqlSequence = new Sequence( $subSequence->getSqlSequenceName() );
            $sqlSequence->setAllocationSize( $sequence->getIncrementBy() );
            $this->createSQLSequence( $sqlSequence );
        }
        return $subSequence;
    }

    /**
     * Incrémente la séquence donnée
     *
     * @param string $sequenceName
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getNextValue(string $sequenceName) :int
    {
        $query = $this->doctrine
            ->getConnection()
            ->getDatabasePlatform()
            ->getSequenceNextValSQL($sequenceName);

        return (int) $this->doctrine->getConnection()->executeQuery($query)->fetchOne();
    }

    /**
     * Retourne le schéma doctrine d'une séquence SQL via son nom
     * @param string $name
     * @return Sequence|null
     */
    public function getSequenceSQLSchemaByName(string $name): ?Sequence
    {
        foreach($this->getSchemaManager()->listSequences() as $sequence){
            if($sequence->getName() == $name){
                return $sequence;
            }
        }
        return null;
    }

    /**
     * Crée une séquence SQL
     * @param Sequence $sequence
     */
    public function createSQLSequence(Sequence $sequence)
    {
        $this->getSchemaManager()->createSequence($sequence);
    }

    /**
     * Modifie la valeur de démarrage d'une séquence
     * @param Sequence $sequence
     * @param int $start
     * @throws \Doctrine\DBAL\DBALException
     */
    public function alterSQLSequence(Sequence $sequence, int $start = null)
    {
        $alterSequenceSql = $this->doctrine->getConnection()
            ->getDatabasePlatform()
            ->getAlterSequenceSQL($sequence);

        $this->doctrine->getConnection()->executeQuery($alterSequenceSql);

        if($start){
            // fonctionne seulement pour PostgreSQL
            $this->doctrine->getConnection()
                ->executeQuery("ALTER SEQUENCE ".$sequence->getName()." RESTART WITH $start");
        }
    }

     /**
     * Remove a sequence
     * @param CustomSequence $CustomSequence
     */
    public function removeSQLSequence(CustomSequence $CustomSequence){
        $query = $this->doctrine->getConnection()->getDatabasePlatform()->getDropSequenceSQL($CustomSequence->getSequenceName());
        $this->doctrine->getConnection()->executeQuery($query);
    }

    /**
     * @return PostgreSqlSchemaManager
     */
    private function getSchemaManager(){
        return $this->doctrine->getConnection()->createSchemaManager();
    }
}