<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Users\User\BaksDevUsersUserBundle;

return static function(ContainerConfigurator $configurator) {

    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $NAMESPACE = BaksDevUsersUserBundle::NAMESPACE;
    $PATH = BaksDevUsersUserBundle::PATH;

    $services->load($NAMESPACE, $PATH)
        ->exclude([
            $PATH.'{Entity,Resources,Type}',
            $PATH.'**/*Message.php',
            $PATH.'**/*DTO.php',
        ])
    ;


};

