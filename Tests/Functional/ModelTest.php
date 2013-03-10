<?php

namespace WhiteOctober\MongoatBundle\Tests\Functional;

use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Connection;
use WhiteOctober\MongoatBundle\Tests\Fixtures\User;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mongoat = new Mongoat();
        $connection = new Connection('localhost', 'mongoat_test');
        $this->mongoat->addConnection('local', $connection);
        $this->mongoat->modelNamespace('WhiteOctober\MongoatBundle\Tests\Fixtures');

        $this->model = new User();
        $this->model->mongoat($this->mongoat);

        $this->mongoat->delete('User')->all();
    }

    public function testEmptyCollection()
    {
        $this->model->save();
        $this->mongoat->delete('User')->all();
        $users = $this->mongoat->find('User')->all();

        $this->assertEquals(array(), $users);
    }

    public function testSaveAndLoadDefaultsModel()
    {
        $this->model->save();
        $model = $this->mongoat->find('User')->one();

        $this->assertEquals($model->id(), $this->model->id());

        $this->assertEquals('Your name', $model->name());
        $this->assertEquals(null, $model->anything());
        $this->assertEquals(3, $model->count());
        $this->assertEquals(null, $model->createdAt());
        $this->assertEquals(true, $model->enabled());
        $this->assertEquals(null, $model->value());
        $this->assertEquals(null, $model->catId());
        $this->assertEquals(array('Fluffy', 'Chairman Miaow'), $model->catNames());
        $this->assertEquals(array(), $model->prices());
        $this->assertEquals(array(), $model->loginDates());
        $this->assertEquals(array(), $model->dogIds());
    }

    public function testSaveAndLoadModel()
    {
        $date = new \DateTime;

        $this->model->name('John Smith');
        $this->model->anything(73.2);
        $this->model->count(6.5);
        $this->model->createdAt($date);
        $this->model->catId('512421b646bd74b97a000009');
        $this->model->catNames(array('Tuna Cat-sir-roll', 'Calista Flockwhiskers'));

        $this->model->save();
        $model = $this->mongoat->find('User')->one();

        $this->assertEquals($model->id(), $this->model->id());

        $this->assertEquals('John Smith', $model->name());
        $this->assertEquals(73.2, $model->anything());
        $this->assertEquals(6, $model->count());
        $this->assertEquals($date, $model->createdAt());
        $this->assertEquals(true, $model->enabled());
        $this->assertEquals(null, $model->value());
        $this->assertEquals('512421b646bd74b97a000009', $model->catId());
        $this->assertEquals(array('Tuna Cat-sir-roll', 'Calista Flockwhiskers'), $model->catNames());
        $this->assertEquals(array(), $model->prices());
        $this->assertEquals(array(), $model->loginDates());
        $this->assertEquals(array(), $model->dogIds());
    }

    public function testDeleteModel()
    {
        $this->model->save();
        $this->model->delete();

        $model = $this->mongoat->find('User')->one();

        $this->assertEquals(null, $model);
    }

    public function testModelUnsavedMethod()
    {
        $this->assertEquals(true, $this->model->unsaved());

        $this->model->save();

        $this->assertEquals(false, $this->model->unsaved());

        $model = $this->mongoat->find('User')->one();

        $this->assertEquals(false, $this->model->unsaved());
    }
}
