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

declare(strict_types=1);

namespace BaksDev\Users\User\Repository;

use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Repository\GetUserById\GetUserByIdInterface;
use BaksDev\Users\User\Tests\TestUserAccount;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class UserRepository extends ServiceEntityRepository
{
    private GetUserByIdInterface $getUserById;

    public function __construct(ManagerRegistry $registry, GetUserByIdInterface $getUserById)
    {
        parent::__construct($registry, User::class);
        $this->getUserById = $getUserById;
    }

    /** Переопределяем метод Doctrine\ORM find() */
    public function find($id, $lockMode = null, $lockVersion = null): ?object
    {

        $id = $id['id'] ?? $id;

        // Если идентификатор тестового пользователя
        if(TestUserAccount::getUsr()->getUserIdentifier() === (string) $id)
        {
            return TestUserAccount::getUsr();
        }

        // Если идентификатор тестового модератора
        if(TestUserAccount::getModer()->getUserIdentifier() === (string) $id)
        {
            return TestUserAccount::getModer();
        }

        // Если идентификатор тестового администратора
        if(TestUserAccount::getAdmin()->getUserIdentifier() === (string) $id)
        {
            return TestUserAccount::getAdmin();
        }


        if(is_string($id))
        {
            $id = new UserUid($id);
        }

        return $this->getUserById->get($id);
    }
}