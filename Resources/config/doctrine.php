<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\User\Entity\UserProfile\UserProfile;
use BaksDev\Users\User\Entity\UserProfile\UserProfileInterface;
use BaksDev\Users\User\Type\Id\UserUid;
use BaksDev\Users\User\Type\Id\UserUidType;
use Symfony\Config\DoctrineConfig;

return static function(DoctrineConfig $doctrine, ContainerConfigurator $configurator) {
	$services = $configurator->services()
		->defaults()
		->autowire()
		->autoconfigure()
	;
	
	$doctrine->dbal()->type(UserUid::TYPE)->class(UserUidType::class);
	$orm = $doctrine->orm();
	
	/** Интерфейс профиля пользователя */
	$services->set(UserProfileInterface::class);
	$orm->resolveTargetEntity(UserProfileInterface::class, UserProfile::class);
	
	//$orm->resolveTargetEntity(UserProfileInterface::class, UserProfileInfo::class);
	
	$emDefault = $orm->entityManager('default');
	$emDefault->autoMapping(true);
	
	$emDefault->mapping('User')
		->type('attribute')
		->dir(__DIR__.'/../../Entity')
		->isBundle(false)
		->prefix('BaksDev\Users\User\Entity')
		->alias('User')
	;
};