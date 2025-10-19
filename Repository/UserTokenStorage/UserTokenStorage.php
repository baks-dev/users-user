<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\User\Repository\UserTokenStorage;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserTokenStorage implements UserTokenStorageInterface
{
    private TokenInterface|false|null $token = null;

    private UserInterface|false|null $UserInterface = null;

    private UserInterface|false|null $CurrentUserInterface = null;

    private UserUid|false|null $user = null;

    private UserUid|false|null $current = null;

    public function __construct(private readonly TokenStorageInterface $tokenStorage) {}

    public function authorization(User|UserUid $user): void
    {
        $UserUid = (string) $user;
        $token = new UsernamePasswordToken(new User($UserUid), 'user', ['ROLE_USER']);
        $this->tokenStorage->setToken($token);
    }

    /**
     * Метод проверяет, является ли пользователь авторизованным
     */
    public function isUser(): bool
    {
        return $this->getUserInterface() instanceof UserInterface;
    }

    /**
     * Метод возвращает идентификатор текущего пользователя либо идентификатор олицетворенного
     */
    public function getUser(): UserUid|false
    {
        $this->getUserInterface();

        if(is_null($this->user))
        {
            $this->user = new UserUid($this->UserInterface->getUserIdentifier());
        }

        if($this->user === false)
        {
            throw new UserAccessDeniedException('Для доступа к ресурсу необходима полная аутентификация');
        }

        return $this->user;
    }

    /**
     * Метод всегда возвращает идентификатор текущего пользователя вне зависимости от олицетворения
     */
    public function getUserCurrent(): UserUid|false
    {
        $this->getCurrentUserInterface();

        if(is_null($this->current))
        {
            $this->current = new UserUid($this->CurrentUserInterface->getUserIdentifier());
        }

        if($this->current === false)
        {
            throw new UserAccessDeniedException('Для доступа к ресурсу необходима полная аутентификация');
        }

        return $this->current;
    }


    /**
     * UserInterface
     */
    public function getUserInterface(): UserInterface|false
    {
        if(is_null($this->token))
        {
            $token = $this->tokenStorage->getToken();
            $this->token = $token instanceof TokenInterface ? $token : false;
        }

        if($this->token === false)
        {
            $this->UserInterface = false;
            $this->user = false;
        }

        if(is_null($this->UserInterface))
        {
            $user = $this->token->getUser();
            $this->UserInterface = $user instanceof UserInterface ? $user : false;

            if($this->UserInterface === false)
            {
                $this->user = false;
            }
        }

        return $this->UserInterface;
    }

    /**
     * CurrentUserInterface
     */
    public function getCurrentUserInterface(): UserInterface|false
    {
        if(is_null($this->token))
        {
            $token = $this->tokenStorage->getToken();
            $this->token = $token instanceof TokenInterface ? $token : false;
        }

        if($this->token === false)
        {
            $this->CurrentUserInterface = false;
        }

        if(is_null($this->CurrentUserInterface))
        {
            $user = $this->token instanceof SwitchUserToken ? $this->token->getOriginalToken()->getUser() : $this->token->getUser();

            $this->CurrentUserInterface = $user instanceof UserInterface ? $user : false;

            if($this->CurrentUserInterface === false)
            {
                $this->current = false;
            }
        }

        return $this->CurrentUserInterface;
    }

}
