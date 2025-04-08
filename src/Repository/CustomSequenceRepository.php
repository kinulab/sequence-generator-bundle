<?php

declare(strict_types=1);

namespace Kinulab\SequenceGeneratorBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Kinulab\SequenceGeneratorBundle\Entity\CustomSequence;
use Kinulab\SequenceGeneratorBundle\Entity\CustomSequenceSub;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Schema\PostgreSqlSchemaManager;
use Doctrine\DBAL\Schema\Sequence;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomSequenceRepository>
 */
class CustomSequenceRepository extends EntityRepository
{

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(CustomSequence::class));
    }

    public function getSubSequence(CustomSequence $sequence, ?string $prefix, ?string $suffix) :CustomSequenceSub
    {
        if(!$sequence->getIndependantComplement()){
            throw new \LogicException("A sequence without independant complement doesn't have sub sequence.");
        }

        $repo = $this->_em->getRepository(CustomSequenceSub::class);
        $subSequence = $repo->findOneBy(['customSequence' => $sequence, 'prefix' => $prefix, 'suffix' => $suffix]);
        if(!$subSequence){ // on crée la subsequence
            $tableName = $this->_em->getClassMetadata(CustomSequenceSub::class)->getTableName();
            $sql = sprintf("INSERT INTO %s (id, custom_sequence_id, prefix, suffix) VALUES (nextval('%s_id_seq'), :sequence_id, :prefix, :suffix)", $tableName, $tableName);
            $this->_em->getConnection()->executeQuery($sql, ['sequence_id' => $sequence->getId(), 'prefix' => $prefix, 'suffix' => $suffix]);

            $subSequence = $repo->findOneBy(['customSequence' => $sequence, 'prefix' => $prefix, 'suffix' => $suffix]);

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
        $query = $this->_em
            ->getConnection()
            ->getDatabasePlatform()
            ->getSequenceNextValSQL($sequenceName);

        return (int) $this->_em->getConnection()->executeQuery($query)->fetchOne();
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
    public function createSQLSequence(Sequence $sequence) :void
    {
        $this->getSchemaManager()->createSequence($sequence);
    }

    /**
     * Modifie la valeur de démarrage d'une séquence
     * @param Sequence $sequence
     * @param int $start
     * @throws \Doctrine\DBAL\DBALException
     */
    public function alterSQLSequence(Sequence $sequence, int $start = null) : void
    {
        $alterSequenceSql = $this->_em
            ->getConnection()
            ->getDatabasePlatform()
            ->getAlterSequenceSQL($sequence);

        $this->_em->getConnection()->executeQuery($alterSequenceSql);

        if($start){
            // fonctionne seulement pour PostgreSQL
            $this->_em->getConnection()
                ->executeQuery("ALTER SEQUENCE ".$sequence->getName()." RESTART WITH $start");
        }
    }

     /**
     * Remove a sequence
     * @param CustomSequence $CustomSequence
     */
    public function removeSQLSequence(CustomSequence $CustomSequence) : void
    {
        $query = $this->_em->getConnection()->getDatabasePlatform()->getDropSequenceSQL($CustomSequence->getSequenceName());
        $this->_em->getConnection()->executeQuery($query);
    }

    /**
     * @return PostgreSqlSchemaManager
     */
    private function getSchemaManager() : PostgreSqlSchemaManager
    {
        return $this->_em->getConnection()->getSchemaManager();
    }
}