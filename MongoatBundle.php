<?php

namespace WhiteOctober\MongoatBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WhiteOctober\MongoatBundle\DependencyInjection\MongoatExtension;

class MongoatBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		$this->extension = new MongoatExtension();
	}
}
