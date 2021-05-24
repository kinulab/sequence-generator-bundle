<?php

namespace Test\Kinulab\SequenceGeneratorBundle\Repository;

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Sequence;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Kinulab\SequenceGeneratorBundle\Generator\SequenceGenerator;
use Kinulab\SequenceGeneratorBundle\Entity\CustomSequence;
use Kinulab\SequenceGeneratorBundle\Repository\SequenceRepository;
use Test\Kinulab\SequenceGeneratorBundle\TestHelper\InvokedAtLeastOnceWithParam;

class SequenceRepositoryTest extends TestCase
{

    private $services;
    private $lastRunTableName = 'tableName';

    public function setUp()
    {
        $this->services  = [
            'connection' => $this->createMock(Connection::class),
            'doctrine' => $this->createMock(EntityManager::class),
            'repository' => $this->createMock(EntityRepository::class),
            'statement' => $this->createMock(Statement::class),
            'schemaManager' => $this->createMock(AbstractSchemaManager::class),
            'metadata' => $this->createMock(ClassMetadata::class),
            'platform' => $this->createMock(AbstractPlatform::class),
        ];

        $this->services['connection']
            ->method('getDatabasePlatform')
            ->willReturn($this->services['platform']);

        $this->services['connection']
            ->method('getSchemaManager')
            ->willReturn($this->services['schemaManager']);

        $this->services['schemaManager']
            ->method('getDatabasePlatform')
            ->willReturn($this->services['platform']);

        $this->services['connection']
            ->method('query')
            ->willReturn($this->services['statement']);

        $this->services['doctrine']
            ->method('getRepository')
            ->willReturn($this->services['repository']);

        $this->services['metadata']
            ->method('getTableName')
            ->willReturn($this->lastRunTableName);

        $this->services['doctrine']
            ->method('getClassMetadata')
            ->willReturn($this->services['metadata']);

        $this->services['doctrine']
            ->method('getConnection')
            ->willReturn($this->services['connection']);

    }

    public function testGetNextValue()
    {
        //setup
        $doctrine = $this->services['doctrine'];
        $databasePlatform = $this->services['platform'];
        $repository = new SequenceRepository($doctrine);
        $query = uniqid();
        $expectedValue = rand();

        $databasePlatform->method('getSequenceNextValSQL')
            ->willReturn($query);
        ;

        $this->services['connection']
            ->method('fetchColumn')
            ->with($query)
            ->willReturn($expectedValue);

        //verfiy
        $databasePlatform->expects($this->once())
            ->method('getSequenceNextValSQL')
        ;
        //act
        $actual = $repository->getNextValue('sequenceName');
        //verify
        $this->assertEquals($expectedValue, $actual);
    }

    public function testCreateSQLSequence()
    {
        $repository = new SequenceRepository($this->services['doctrine']);
        $sequence = $this->createMock(Sequence::class);

        $this->services['schemaManager']
            ->expects($this->once())
            ->method('createSequence')
            ->with($sequence)
        ;

        $repository->createSQLSequence($sequence);
    }

    public function testAlterSQLSequenceWithoutStart()
    {
        //setup
        $doctrine = $this->services['doctrine'];
        $sequence = new Sequence('sequence_name');
        $repository = new SequenceRepository($doctrine);
        $query = uniqid();

        $this->services['platform']->method('getAlterSequenceSQL')
            ->willReturn($query);
        ;
        //verfiy
        $this->services['platform']
            ->expects($this->once())
            ->method('getAlterSequenceSQL')
            ->with($sequence)
        ;
        $this->services['connection']
            ->expects($this->once())
            ->method('exec')
            ->with($query)
        ;
        //act
        $repository->alterSQLSequence($sequence);
    }

    public function testAlterSQLSequenceWithStart()
    {
        //setup
        $doctrine = $this->services['doctrine'];
        $sequence = new Sequence('sequence_name');
        $repository = new SequenceRepository($doctrine);
        $start = rand();

        //verfiy
        $this->services['connection']
            ->expects($this->at(2))
            ->method('exec')
            ->with("ALTER SEQUENCE ".$sequence->getName()." RESTART WITH $start")
        ;
        //act
        $repository->alterSQLSequence($sequence, $start);
    }

    public function testRemoveSQLSequence()
    {
        //setup
        $doctrine = $this->services['doctrine'];
        $sequence = new CustomSequence();
        $sequence->setSequenceName(uniqid());
        $repository = new SequenceRepository($doctrine);
        $query = uniqid();

        $this->services['platform']->method('getDropSequenceSQL')
            ->willReturn($query);
        ;
        //verfiy
        $this->services['platform']
            ->expects($this->once())
            ->method('getDropSequenceSQL')
            ->with($sequence->getSequenceName())
        ;

        $this->services['connection']
            ->expects($this->once())
            ->method('exec')
            ->with($query)
        ;
        //act
        $repository->removeSQLSequence($sequence);
    }



}