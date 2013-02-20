<?php

namespace WhiteOctober\MongoatBundle\Tests\Functional;

use WhiteOctober\MongoatBundle\Core\Mongoat;
use WhiteOctober\MongoatBundle\Core\Connection;
use WhiteOctober\MongoatBundle\Tests\Fixtures\User;

class SaveLoadTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mongoat = new Mongoat();
        $connection = new Connection('localhost', 'mongoat_tests');
        $this->mongoat->addConnection('local', $connection);
        $this->mongoat->modelNamespace('WhiteOctober\MongoatBundle\Tests\Fixtures');

        $this->model = new User();
        $this->model->mongoat($this->mongoat);

        $this->mongoat->delete('User')->all();
    }

    public function testEmptyTable()
    {
        $this->model->save();
        $this->mongoat->delete('User')->all();
        $users = $this->mongoat->find('User')->all();

        $this->assertEquals(array(), $users);
    }

    public function testSaveModel()
    {
        $this->model->save();
        $model = $this->mongoat->find('User')->one();

        $this->assertEquals($model->id(), $this->model->id());
    }
}
