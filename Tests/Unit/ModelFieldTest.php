<?php

namespace WhiteOctober\MongoatBundle\Tests\Unit;
use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Schema\Schema;
use WhiteOctober\MongoatBundle\Core\Model;
use WhiteOctober\MongoatBundle\Tests\Fixtures\User;

use PHPUnit_Framework_TestCase;

class ModelFieldTest extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $this->mongoat = new Mongoat();
    }

    public function setUp()
    {
        $this->model = new User($this->mongoat);
        $this->class = get_class($this->model);
        $this->schema = $this->model->schema();
    }

    public function testGettingDefaultValues()
    {
        $this->assertSame('Your name', $this->model->get('name'));
        $this->assertSame(null, $this->model->get('anything'));
        $this->assertSame(3, $this->model->get('count'));
        $this->assertSame(true, $this->model->get('enabled'));
        $this->assertSame(null, $this->model->get('createdAt'));
        $this->assertSame(array(), $this->model->get('prices'));
        $this->assertSame(array('Fluffy', 'Chairman Miaow'), $this->model->get('catNames'));
    }

    public function testSettingGettingIdFields()
    {
        $id = new \MongoId();
        $this->model->set('_id', $id);

        $this->assertSame((string) $id, $this->model->get('_id'));
        $this->assertSame((string) $id, $this->model->get('id'));
        $this->assertSame((string) $id, $this->model->id());
        $this->assertSame((string) $id, $this->model->getId());

        $id2 = new \MongoId();

        $this->model->set('id', $id2);
        $this->assertSame((string) $id2, $this->model->get('_id'));

        $this->model->id($id);
        $this->assertSame((string) $id, $this->model->get('_id'));

        $this->model->setId($id2);
        $this->assertSame((string) $id2, $this->model->get('_id'));
    }

    public function testSettingGettingValuesByString()
    {
        $date = new \DateTime();

        $this->model->set('name', 'John');
        $this->model->set('anything', 'foo bar');
        $this->model->set('count', 3);
        $this->model->set('createdAt', $date);
        $this->model->set('catNames', array('Mr. Whiskers'));

        $this->assertSame('John', $this->model->get('name'));
        $this->assertSame('foo bar', $this->model->get('anything'));
        $this->assertSame(3, $this->model->get('count'));
        $this->assertSame($date, $this->model->get('createdAt'));
        $this->assertSame(array('Mr. Whiskers'), $this->model->get('catNames'));
    }

    public function testGetNonExistantField()
    {
        $this->setExpectedException('Exception', "Field 'fake' does not exist in $this->class");

        $this->model->get('fake');
    }

    public function testSetNonExistantField()
    {
        $this->setExpectedException('Exception',
            "Field 'fake' does not exist in $this->class"
        );

        $this->model->set('fake', 'something');
    }

    public function testGetNonExistantFieldSymfonyStyle()
    {
        $this->setExpectedException('Exception', "Field 'fake' does not exist in $this->class");

        $this->model->getFake();
    }

    public function testSetNonExistantFieldSymfonyStyle()
    {
        $this->setExpectedException('Exception',
            "Field 'fake' does not exist in $this->class"
        );

        $this->model->setFake('something');
    }

    public function testGetNonExistantFieldRubyStyle()
    {
        $this->setExpectedException('Exception',
            "Field 'fake' does not exist in $this->class"
        );

        $this->model->fake();
    }

    public function testSetNonExistantFieldRubyStyle()
    {

        $this->setExpectedException('Exception',
            "Field 'fake' does not exist in $this->class"
        );

        $this->model->fake('something');
    }

    public function testSettingGettingValuesSymfonyStyle()
    {
        $date = new \DateTime();

        $this->model->setName('John');
        $this->model->setAnything('foo bar');
        $this->model->setCount(3);
        $this->model->setCreatedAt($date);
        $this->model->setCatNames(array('Rawr'));

        $this->assertSame('John', $this->model->getName());
        $this->assertSame('foo bar', $this->model->getAnything());
        $this->assertSame(3, $this->model->getCount());
        $this->assertSame($date, $this->model->getCreatedAt());
        $this->assertSame(array('Rawr'), $this->model->getCatNames());
    }

    public function testSettingGettingValuesRubyStyle()
    {
        $date = new \DateTime();

        $this->model->name('John');
        $this->model->anything('foo bar');
        $this->model->count(3);
        $this->model->createdAt($date);
        $this->model->catNames(array('Tibbles', 'Fluffy'));

        $this->assertSame('John', $this->model->name());
        $this->assertSame('foo bar', $this->model->anything());
        $this->assertSame(3, $this->model->count());
        $this->assertSame($date, $this->model->createdAt());
        $this->assertSame(array('Tibbles', 'Fluffy'), $this->model->catNames());
    }
}
