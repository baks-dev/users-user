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

namespace BaksDev\Users\User\EntityListeners;

use BaksDev\Users\User\Repository\UserProfile\UserProfileInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


final class UserListenerDecorate implements UserProfileInterface
{
	public $user;
	
	private TranslatorInterface $translator;
	
	public function __construct(UserProfileInterface $profile, TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}
	
	public function postLoad(UserInterface $data, LifecycleEventArgs $event) : void
    {
		$this->user = $data->getId();
    }
	
	/**  Username пользователя */
	public function getUsername() : ?string
	{
		return null;
	}
	
	/** Контакт */
	public function getContact() : ?string
	{
		return null;
	}
	
	/** Тип пользователя */
	public function getType() : ?string
	{
		return $this->translator->trans('user.profile.type', domain: 'user.account');
	}
	
	/** Адрес персональной страницы */
	public function getPage() : ?string
	{
		return null;
	}
	
	/** Адрес страницы редактирвоания */
	public function getEdiPath() : ?string
	{
		return null;
	}
	
	/** Аватарка */
	public function getImage() : ?string
	{
		return null;
	}
	
	/** Идентификатор страницы редактирвоания */
	public function getEvent() : ?string
	{
		return null;
	}
}