<?php

namespace BaksDev\Users\User\Type\Id;

use BaksDev\Core\Type\UidType\UidType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

final class UserUidType extends UidType
{
    public function getClassType(): string
    {
        return UserUid::class;
    }


    public function getName(): string
    {
        return UserUid::TYPE;
    }

}