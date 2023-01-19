<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $configurator)
{
    $services = $configurator->services()
      ->defaults()
      ->autowire()
      ->autoconfigure()
    ;
	
	$namespace = 'BaksDev\Users\User';
    
    $services->load($namespace.'\Repository\\', __DIR__.'/../../Repository');
	
};

