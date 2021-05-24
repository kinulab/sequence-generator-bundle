<?php

namespace Test\Kinulab\SequenceGeneratorBundle\Generator;

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Schema\Sequence;
use Kinulab\SequenceGeneratorBundle\Generator\SequenceGenerator;
use Kinulab\SequenceGeneratorBundle\Entity\CustomSequence;
use Kinulab\SequenceGeneratorBundle\Repository\SequenceRepository;

class SequenceGeneratorTest extends TestCase
{

    public function testGetNextValIsChangingTheLastRun()
    {
        //setup
        $repository = $this->createMock(SequenceRepository::class);
        $generator = new SequenceGenerator($repository);

        $repository
            ->method('getLastYearlyRun')
            ->willReturn('0');

        //verify
        $repository->expects($this->once())
            ->method('updateLastRun')
            ->with(date('Y'));

        //act
        $generator->getNextVal('test_sequence');
    }

    public function testGetNextValIsNotChangingTheLastRun()
    {
        //setup
        $repository = $this->createMock(SequenceRepository::class);
        $generator = new SequenceGenerator($repository);

        $repository
            ->method('getLastYearlyRun')
            ->willReturn(date('Y'));

        //verify
        $repository->expects($this->never())
            ->method('updateLastRun')
        ;

        //act
        $generator->getNextVal('test_sequence');
    }

    public function testInitialiseSequenceIsUpdatingSequence()
    {
        //setup
        $sequence = $this->getCustomSequence();
        $sequence->setIncrementBy(rand());

        $SQLSequence = $this->createMock(Sequence::class);

        $repository = $this->createMock(SequenceRepository::class);
        $generator = new SequenceGenerator($repository);

        $repository
            ->method('getSQLSequenceByName')
            ->willReturn($SQLSequence);

        //verify
        $repository->expects($this->never())
            ->method('createSQLSequence')
        ;

        $repository->expects($this->once())
            ->method('alterSQLSequence')
            ->with($SQLSequence)
        ;
        //act
        $generator->initializeSequence($sequence);
    }

    public function testInitialiseSequenceIsCreatingSequence()
    {
        //setup
        $startValue = 6;

        $sequence = $this->getCustomSequence();
        $sequence->setIncrementBy(rand());

        $repository = $this->createMock(SequenceRepository::class);
        $generator = new SequenceGenerator($repository);

        $repository
            ->method('getSQLSequenceByName')
            ->willReturn(null);

        //verify
        $repository->expects($this->once())
            ->method('createSQLSequence')
            ->with($this->callback(function($subject) use ($startValue, $sequence){
                return $subject->getAllocationSize() == $sequence->getIncrementBy()
                    && $subject->getInitialValue() == $startValue;
                })
            )
        ;

        //act
        $generator->initializeSequence($sequence, $startValue);

    }

     /**
     * Test le comportement de l'itérateur des séquences
     */
    public function testNextvalue()
    {

        $sequence = $this->getCustomSequence();

        $repository = $this->createMock(SequenceRepository::class);
        $generator = new SequenceGenerator($repository);

        $repository
            ->method('getNextValue')
            ->willReturn(1);

        $repository->expects($this->once())
            ->method('getNextValue')
        ;

        $generator->getNextVal($sequence->getSequenceName());
    }

    public function testIteratorSequences()
    {
        //setup
        $sequence = $this->getCustomSequence();
        $repository = $this->createMock(SequenceRepository::class);
        $generator = new SequenceGenerator($repository);

        $repository
            ->method('getNextValue')
            ->willReturn(1);

        //verify
        $repository->expects($this->once())
            ->method('getNextValue')
        ;

        //act
        $generator->getNextVal($sequence->getSequenceName());
    }

    public function testRemoveSequence()
    {
        //setup
        $sequence = $this->getCustomSequence();
        $repository = $this->createMock(SequenceRepository::class);
        $generator = new SequenceGenerator($repository);

        //verify
        $repository->expects($this->once())
            ->method('removeSQLSequence')
            ->with($this->callback(function($subject) use($sequence){
                return $subject->getSequenceName() == $sequence->getSequenceName();
            }))
        ;

        //act
        $generator->removeSequence($sequence);
    }

    public function testFormatSequencePrefixSufix(){

        //setup
        $sequence = $this->getCustomSequence();
        $sequence_name = $sequence->getSequenceName();
        $repository = $this->createMock(SequenceRepository::class);
        $generator = new SequenceGenerator($repository);
        $nextValue = rand();

        $repository
            ->method('getNextValue')
            ->willReturn($nextValue);

        $repository
            ->method('getSequenceByName')
            ->willReturn($sequence);

        // Test simple des préfix et suffix
        $sequence->setPrefix("TEST");
        $this->assertRegExp('/^TEST'.$nextValue.'$/', $generator->getNextVal($sequence_name));
        $sequence->setSuffix("PAF");
        $this->assertRegExp('/^TEST'.$nextValue.'PAF$/', $generator->getNextVal($sequence_name));
        $sequence->setPrefix("");
        $this->assertRegExp('/^'.$nextValue.'PAF$/', $generator->getNextVal($sequence_name));
    }

    public function testFormatSequenceIncrementLength(){

        //setup
        $sequence = $this->getCustomSequence();
        $sequence_name = $sequence->getSequenceName();
        $repository = $this->createMock(SequenceRepository::class);

        $generator = new SequenceGenerator($repository);

        $sequence->setPrefix("");
        $sequence->setSuffix("");
        $sequence->setIncrementLength(2);

        $repository
            ->method('getNextValue')
            ->willReturn(1);

        $repository
            ->method('getSequenceByName')
            ->willReturn($sequence);

        $generator = new SequenceGenerator($repository);

        $this->assertEquals('01', $generator->getNextVal($sequence_name));
        $sequence->setIncrementLength(3);
        $this->assertEquals('001', $generator->getNextVal($sequence_name));

    }

    public function testFormatSequenceIncrementLengthWithLongValue(){

        //setup
        $sequence = $this->getCustomSequence();
        $sequence->setIncrementLength(3);

        $sequence_name = $sequence->getSequenceName();
        $repository = $this->createMock(SequenceRepository::class);
        $generator = new SequenceGenerator($repository);

        $repository
            ->method('getNextValue')
            ->willReturn(10000);

        $repository
            ->method('getSequenceByName')
            ->willReturn($sequence);

        $this->assertEquals('10000', $generator->getNextVal($sequence_name));
    }

    public function testFormatSequenceVariableInPrefixSuffix(){

        //setup
        $sequence = $this->getCustomSequence();
        $sequence->setPrefix("date-%year%-");
        $sequence->setSuffix("-%y%-fin");
        $sequence->setIncrementLength(3);

        $repository = $this->createMock(SequenceRepository::class);
        $generator = new SequenceGenerator($repository);

        $repository
            ->method('getNextValue')
            ->willReturn(1);

        $repository
            ->method('getSequenceByName')
            ->willReturn($sequence);

        //act and verify
        $this->assertEquals(
            'date-'.date('Y').'-001-'.date('y').'-fin',
            $generator->getNextVal($sequence->getSequenceName())
        );
    }

    public function testFormatSequenceWithObject()
    {
        //setup
        $sequence = $this->getCustomSequence();
        $sequence->setPrefix("%object.attr1%-%object.character[0]%-");
        $sequence->setSuffix('-%object.character[1]%');
        $sequence->setIncrementLength(3);

        $object = new \stdClass();
        $object->attr1 = '123';
        $object->character = ['Homer Simpson', 'Marge Simpson'];

        $repository = $this->createMock(SequenceRepository::class);
        $generator = new SequenceGenerator($repository);

        $repository
            ->method('getNextValue')
            ->willReturn(2);

        $repository
            ->method('getSequenceByName')
            ->willReturn($sequence);

        //act and verify
        $this->assertEquals(
            '123-Homer Simpson-002-Marge Simpson',
            $generator->getNextVal($sequence->getSequenceName(), $object)
        );
    }

    public function testFormatSequenceWithUnusualStrings()
    {
        //setup
        $sequence = $this->getCustomSequence();
        $sequence->setPrefix("%innexistant%%year%%%%colé%");
        $sequence->setSuffix("%innexistant%%year%%%%colé%");
        $sequence->setIncrementLength(3);

        $repository = $this->createMock(SequenceRepository::class);
        $generator = new SequenceGenerator($repository);

        $repository
            ->method('getNextValue')
            ->willReturn(1);

        $repository
            ->method('getSequenceByName')
            ->willReturn($sequence);

        //act and verify
        $this->assertEquals(
            '%innexistant%'.date('Y').'%%%colé%001%innexistant%'.date('Y').'%%%colé%',
            $generator->getNextVal($sequence->getSequenceName())
        );
    }

    public function get($serviceId)
    {
        return $this->services[$serviceId];
    }

    private function getCustomSequence(): CustomSequence
    {
        $sequence_name = 'test_sequence_'.uniqid();
        $sequence = new CustomSequence();
        $sequence->setLibelle(uniqid());
        $sequence->setSequenceName($sequence_name);
        return $sequence;
    }



}