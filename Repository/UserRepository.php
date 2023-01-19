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

namespace BaksDev\Users\User\Repository;

use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Repository\GetUserById\GetUserByIdInterface;
use BaksDev\Users\User\Tests\TestUserAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class UserRepository extends ServiceEntityRepository
{
	private GetUserByIdInterface $getUserById;
	
	public function __construct(ManagerRegistry $registry, GetUserByIdInterface $getUserById)
	{
		parent::__construct($registry, User::class);
		$this->getUserById = $getUserById;
	}
	
	/** Переопределяем метод Doctrine\ORM find() */
	public function find($id, $lockMode = null, $lockVersion = null)
	{
		/* Если идентификатор тестового пользователя */
		if((string) $id['id'] === TestUserAccount::TEST_USER_UID)
		{
			return TestUserAccount::getUser();
		}
		
		/* Если идентификатор тестового администратора */
		if( (string) $id['id'] === TestUserAccount::TEST_ADMIN_UID)
		{
			return TestUserAccount::getAdmin();
		}
		
		return $this->getUserById->get($id['id']);
	}
}