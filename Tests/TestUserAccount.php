<?php

namespace BaksDev\Users\User\Tests;

use BaksDev\Users\User\Entity\User;

final class TestUserAccount
{
	public const TEST_USER_UID = '018549ea-0ff7-7439-872e-dbda9993e413';
	public const TEST_ADMIN_UID = '018549ee-24ba-7177-9622-d6d8f7b721ee';
	
	
	public static function getUser() : User
	{
		$user = new User(self::TEST_USER_UID);
		$user->setRole(["ROLE_USER"]);
		
		return $user;
	}
	
	
	public static function getAdmin() : User
	{
		$user = new User(self::TEST_ADMIN_UID);
		$user->setRole(["ROLE_USER", "ROLE_ADMIN"]);
		
		return $user;
	}
	
}