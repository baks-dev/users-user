<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\User\Controller;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Repository\GetUserById\GetUserByIdInterface;
use BaksDev\Users\User\Type\Id\UserUid;
use InvalidArgumentException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

#[AsController]
#[RoleSecurity('ROLE_ADMIN')]
final class SwitchUserController extends AbstractController
{
    #[Route('/admin/switch/user/{id}', name: 'admin.switch', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity] User $User,
        TokenStorageInterface $tokenStorage,
        GetUserByIdInterface $getUserById,
        AppCacheInterface $cache
    ): Response
    {
        if(!$request->getSession()->get('_switch_user'))
        {
            /** Удаляем авторизацию пользователя */
            $authority = $User->getUserIdentifier();
            $AppCache = $cache->init('Authority');
            $AppCache->delete($authority);


            $CurrentUser = $getUserById->get($User->getId());

            if(!$this->isGranted('ROLE_ADMIN', $this->getUsr()))
            {
                throw new InvalidArgumentException('Access Denied');
            }

            $request->getSession()->set('_switch_user', (string) $this->getUsr()?->getId());

            $impersonationToken = new  UsernamePasswordToken(
                $CurrentUser,
                "user",
                $CurrentUser->getRoles()
            );

            $tokenStorage->setToken($impersonationToken);

            return $this->redirectToRoute('core:user.homepage');

        }

        $SwitchUser = $request->getSession()->get('_switch_user');

        if(!$SwitchUser)
        {
            return new Response('OK');
        }

        $CurrentUser = $getUserById->get(new UserUid($SwitchUser));

        // Олицетворение запрошенного пользователя
        $impersonationToken = new  UsernamePasswordToken(
            $CurrentUser,
            "user",
            $CurrentUser->getRoles()
        );

        $tokenStorage->setToken($impersonationToken);
        $request->getSession()->remove('_switch_user');

        return $this->redirectToRoute('core:admin.homepage');
    }
}
