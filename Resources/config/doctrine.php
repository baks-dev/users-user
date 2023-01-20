<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Users\User\Type\Id\UserUid;
use BaksDev\Users\User\Type\Id\UserUidType;
use Symfony\Config\DoctrineConfig;


return static function (DoctrineConfig $doctrine)
{
	$doctrine->dbal()->type(UserUid::TYPE)->class(UserUidType::class);
	
	$emDefault = $doctrine->orm()->entityManager('default');
	
	$emDefault->autoMapping(true);
	
	$emDefault->mapping('User')
		->type('attribute')
		->dir(__DIR__.'/../../Entity')
		->isBundle(false)
		->prefix('BaksDev\Users\User\Entity')
		->alias('User');
};