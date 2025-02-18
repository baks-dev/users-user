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

namespace BaksDev\Users\User\Repository\GetUserById;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\Entity\Role\ProfileRole;
use BaksDev\Users\Profile\Group\Entity\Role\Voter\ProfileVoter;
use BaksDev\Users\Profile\Group\Repository\ExistProfileGroup\ExistProfileGroupInterface;
use BaksDev\Users\Profile\Group\Repository\ProfileGroup\ProfileGroupByUserProfileInterface;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;

final readonly class GetUserByIdRepository implements GetUserByIdInterface
{
    public function __construct(
        private DBALQueryBuilder $DBALQueryBuilder,
        private ORMQueryBuilder $ORMQueryBuilder,
        private ProfileGroupByUserProfileInterface $profileGroupByUserProfile,
        private ExistProfileGroupInterface $existProfileGroup,
        private AppCacheInterface $cache
    ) {}

    /**
     * Метод возвращает сущность User
     */
    public function get(UserUid $userUid): User|false
    {

        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->select('users')
            ->from(User::class, 'users')
            ->where('users.id = :userUid')
            ->setParameter('userUid', $userUid, UserUid::TYPE)
            ->setMaxResults(1);


        if(class_exists(UserProfileInfo::class))
        {
            $select = sprintf(
                'new %s(users.id, info.profile)',
                User::class
            );

            $qb->select($select);

            $qb->leftJoin(
                UserProfileInfo::class,
                'info',
                'WITH',
                'info.usr = users.id AND info.active = true'
            );
        }

        /** @var User $usr */
        $usr = $qb
            ->enableCache((string) $userUid, 86400)
            ->getOneOrNullResult();

        if(false === ($usr instanceof User))
        {
            return false;
        }

        if(!class_exists(UserProfileUid::class) || false === ($usr->getProfile() instanceof UserProfileUid))
        {
            return $usr;
        }

        /** Получаем группу профиля пользователя */

        $AppCache = $this->cache->init('Authority');
        $authority = ($AppCache->getItem($usr->getUserIdentifier()))->get();

        if($authority)
        {
            $usr->setProfile($authority);
        }

        $roles = $this->fetchAllRoleUser($usr->getProfile());
        $usr->setRole($roles);

        return $usr;
    }

    public function fetchAllRoleUser(UserProfileUid $profile, UserProfileUid|bool|null $authority = null): ?array
    {
        /** Проверяем, имеется ли у пользователя группа либо доверенность */
        $existGroup = $this->existProfileGroup->isExistsProfileGroup($profile);

        /** Если пользователь не состоит в группе */
        if(false === $existGroup)
        {
            return ['ROLE_USER'];
        }

        /** Получаем префикс группы профиля
         * $authority = false - если администратор ресурса
         */
        $group = $this->profileGroupByUserProfile
            ->findProfileGroupByUserProfile($profile, $authority);

        if(false === ($group instanceof GroupPrefix))
        {
            return ['ROLE_USER', 'ROLE_ADMINISTRATION'];
        }

        if($group->equals('ROLE_ADMIN'))
        {
            return ['ROLE_ADMIN', 'ROLE_USER', 'ROLE_ADMINISTRATION'];
        }


        /** Получаем список ролей и правил группы */
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal->select("
                       ARRAY(SELECT DISTINCT UNNEST(
                            ARRAY_AGG(profile_role.prefix) || 
                            ARRAY_AGG(profile_voter.prefix)
                        )) AS roles
                    ");

        $dbal->from(ProfileGroup::class, 'profile_group');

        $dbal->leftJoin(
            'profile_group',
            ProfileRole::class,
            'profile_role',
            'profile_role.event = profile_group.event'
        );

        $dbal->leftJoin(
            'profile_role',
            ProfileVoter::class,
            'profile_voter',
            'profile_voter.role = profile_role.id'
        );

        $dbal
            ->andWhere('profile_group.prefix = :prefix')
            ->setParameter(
                key: 'prefix',
                value: $group,
                type: GroupPrefix::TYPE
            );

        $dbal
            ->andWhere('profile_role.prefix IS NOT NULL')
            ->andWhere('profile_voter.prefix IS NOT NULL');


        if($authority)
        {
            $dbal->andWhere('profile_group.profile = :authority')
                ->setParameter(
                    key: 'authority',
                    value: $authority,
                    type: UserProfileUid::TYPE
                );
        }

        $roles = $dbal
            ->enableCache('users-profile-group', 60)
            ->fetchOne();

        if($roles)
        {
            $roles = trim($roles, "{}");

            if(empty($roles))
            {
                return null;
            }

            $roles = explode(",", $roles);

            if($roles)
            {
                $roles[] = 'ROLE_ADMINISTRATION';
            }

            $roles[] = 'ROLE_USER';
        }

        return $roles;
    }
}
