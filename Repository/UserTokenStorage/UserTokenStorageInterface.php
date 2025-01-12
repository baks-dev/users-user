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

namespace BaksDev\Users\User\Repository\UserTokenStorage;

use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Component\Security\Core\User\UserInterface;

interface UserTokenStorageInterface
{
    /**
     * Метод проверяет, является ли пользователь авторизованным
     */
    public function isUser(): bool;

    /**
     * Метод возвращает идентификатор текущего пользователя либо идентификатор олицетворенного
     */
    public function getUser(): UserUid|false;

    /**
     * Метод всегда возвращает идентификатор текущего пользователя вне зависимости от олицетворения
     */
    public function getUserCurrent(): UserUid|false;

    public function getUserInterface(): UserInterface|false;

    public function getCurrentUserInterface(): UserInterface|false;

}