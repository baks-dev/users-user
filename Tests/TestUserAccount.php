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

namespace BaksDev\Users\User\Tests;

use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\User\Entity\User;
use Symfony\Component\Dotenv\Dotenv;

final class TestUserAccount
{
    //    public const string TEST _USER_UID = '018549ea-0ff7-7439-872e-dbda9993e413';
    //
    //    public const string TEST _ADMIN_UID = '018549ee-24ba-7177-9622-d6d8f7b721ee';
    //
    //    public const string TEST _MODER_UID = '0187a420-e616-7c12-8ddd-ff0527f3cba1';

    public static ?string $ROLE = null;

    public static ?UserProfile $PROFILE = null;

    public static function getDevice(): array
    {
        return ['Ubuntu', 'iPhone', 'iPad'];
    }


    private static $envLoaded = false;
    private static $envValues = [];


    public static function getUsr(): User
    {
        $usr = new User(null, new UserProfileUid(self::get('TEST_USER_PROFILE_UID')));
        $usr->setRole(self::$ROLE ? [self::$ROLE, 'ROLE_USER'] : ['ROLE_USER']);

        return $usr;
    }

    public static function getModer(?string $role = null): User
    {
        if($role)
        {
            self::$ROLE = $role;
        }

        $usr = new User(self::get('TEST_MODER_UID'), new UserProfileUid(self::get('TEST_MODER_PROFILE_UID')));
        $usr->setRole(self::$ROLE ? [self::$ROLE, 'ROLE_USER'] : ['ROLE_USER']);

        if(!$role)
        {
            self::$ROLE = null;
        }

        return $usr;
    }

    public static function getAdmin(): User
    {
        $usr = new User(self::get('TEST_ADMIN_UID'), new UserProfileUid(self::get('TEST_ADMIN_PROFILE_UID')));
        $usr->setRole(['ROLE_USER', 'ROLE_ADMIN']);

        return $usr;
    }

    private static function get(string $key)
    {
        if(!self::$envLoaded)
        {
            $dotenv = new Dotenv();
            $dotenv->load(__DIR__.'/../../../../.env.test');
            self::$envValues = $_ENV;
            self::$envLoaded = true;
        }

        return self::$envValues[$key];
    }
}
