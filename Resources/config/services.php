<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $configurator)
{
    $services = $configurator->services()
      ->defaults()
      ->autowire()
      ->autoconfigure()
    ;
    
    $services->load('BaksDev\Users\User\Repository\\', __DIR__.'/../../Repository');
	
   
};

