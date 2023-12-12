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

namespace BaksDev\Users\User\Entity;

use BaksDev\Users\User\Repository\UserRepository;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface
{
    public const TABLE = 'users';

    /** Идентификатор */
    #[ORM\Id]
    #[ORM\Column(name: 'usr', type: UserUid::TYPE)]
    private UserUid $id;

    /** Роли пользователя */
    private array $role = ['ROLE_USER'];

    private mixed $profile = null;

    public function __construct(string $id = null, mixed $profile = null)
    {
        $this->id = new UserUid($id);
        $this->profile = $profile;
        
    }

    public function __toString(): string
    {
       return (string) $this->id;
    }

    /** Идентификатор */
    public function getId(): UserUid
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }

    /** Роли пользователя */
    public function setRole(?array $role): void
    {
        if($role)
        {
            $this->role = $role;
        }
    }


    public function getRoles(): array
    {
        return $this->role;
    }

    /**
     * Если у пользователя больше одной роли - имеет доступ к администрированию
     */
    public function isAdministration(): bool
    {
        return count($this->role) > 1;
    }

    /**
     * Идентификатор профиля пользователя
     */
    public function getProfile(): mixed
    {
        return $this->profile;
    }

    public function setProfile(mixed $profile): self
    {
        $this->profile = $profile;
        return $this;
    }
    


    //    public function credentials(): string
    //    {
    //        return (string) $this->id;
    //    }


    /**
     * Удаляет конфиденциальные данные пользователя.
     *
     * Это важно, если в любой момент конфиденциальная информация, такая как
     * текстовый пароль хранится в этом объекте.
     *
     * @return void
     */
    public function eraseCredentials() : void {}
}
