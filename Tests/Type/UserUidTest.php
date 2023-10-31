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

namespace BaksDev\Users\User\Tests\Type;

use BaksDev\Users\User\Type\Id\UserUid;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/** @group users-user */
#[When(env: 'test')]
final class UserUidTest extends KernelTestCase
{
    private const TEST_UUID = '0185494d-2ac6-7bbc-9bea-48af2709ac4d';

    public function newUid(mixed $uid = null, string $name = null): UserUid
    {
        return new UserUid($uid, $name);
    }

    public function testStringUid(): void
    {
        $UUID = $this->newUid(self::TEST_UUID);
        self::assertEquals(self::TEST_UUID, $UUID->getValue());
    }

    public function testBadStringUid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $usr = $this->newUid('string');
    }

    public function testObjectUid(): void
    {
        $UUID = $this->newUid(new UserUid(self::TEST_UUID));
        self::assertEquals(self::TEST_UUID, $UUID->getValue());
    }

    public function testNullUid(): void
    {
        $UUID = $this->newUid();
        self::assertNotEquals(self::TEST_UUID, $UUID->getValue());
    }

    public function testName(): void
    {
        $UUID = $this->newUid(name: self::TEST_UUID);
        self::assertEquals(self::TEST_UUID, $UUID->getOption());
    }

    public function testTrueEquals(): void
    {
        $UUID = $this->newUid(name: self::TEST_UUID);
        $equals = $UUID->equals($this->newUid(self::TEST_UUID));

        self::assertIsBool($equals);
        $this->isTrue();
    }

    public function testFalseEquals(): void
    {
        $UUID = $this->newUid(name: self::TEST_UUID);
        $equals = $UUID->equals($this->newUid());

        self::assertIsBool($equals);
        $this->isFalse();
    }
}
