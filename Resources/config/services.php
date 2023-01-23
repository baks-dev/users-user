<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Users\User\Entity\UserProfile;

return static function (ContainerConfigurator $configurator)
{
    $services = $configurator->services()
      ->defaults()
      ->autowire()
      ->autoconfigure()
    ;
	
	$namespace = 'BaksDev\Users\User';
	
    $services->load($namespace.'\Repository\\', __DIR__.'/../../Repository');

//	$services->set(UserProfile::class)
//		->decorate(\BaksDev\Users\User\Repository\UserProfile\UserProfileInterface::class, null, 99)
//		->args([service('.inner')])
//	;
};

