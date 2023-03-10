<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\EntityListeners;

return static function(ContainerConfigurator $configurator) {
	$services = $configurator->services()
		->defaults()
		->autowire()
		->autoconfigure()
	;
	
	$services->set(EntityListeners\UserListenerDecorate::class)
		->decorate(\BaksDev\Users\User\Repository\UserProfile\UserProfileInterface::class, null, 9999)
		->args([service('.inner')])
		->tag(
			'doctrine.orm.entity_listener',
			['event' => 'postLoad', 'lazy' => true, 'entity' => User::class]
		)
	;
	
	$services->set('EntityListeners\UserListener')
		->class(EntityListeners\UserListener::class)
		->tag(
			'doctrine.orm.entity_listener',
			['event' => 'postLoad', 'lazy' => true, 'entity' => User::class]
		)
	;
	
};
