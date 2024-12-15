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
    public function get(UserUid $userUid): mixed
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


        if(!class_exists(UserProfileUid::class))
        {
            return $usr;
        }

        /** Получаем группу профиля пользователя */
        if($usr && $usr->getProfile() instanceof UserProfileUid)
        {
            $AppCache = $this->cache->init('Authority');
            $authority = ($AppCache->getItem($usr->getUserIdentifier()))->get();

            if($authority)
            {
                $usr->setProfile($authority);
            }

            $roles = $this->fetchAllRoleUser($usr->getProfile());
            $usr->setRole($roles);
        }

        //dump($usr);

        return $usr;
    }

    public function fetchAllRoleUser(UserProfileUid $profile, UserProfileUid|bool|null $authority = null): ?array
    {
        /** Проверяем, имеется ли у пользователя группа либо доверенность */
        $existGroup = $this->existProfileGroup->isExistsProfileGroup($profile);

        if($existGroup)
        {
            /** Получаем префикс группы профиля
             * $authority = false - если администратор ресурса
             */
            $group = $this->profileGroupByUserProfile
                ->findProfileGroupByUserProfile($profile, $authority);

            $roles = null;

            if($group)
            {
                if($group->equals('ROLE_ADMIN'))
                {
                    $roles = null;
                    $roles[] = 'ROLE_ADMINISTRATION';
                    $roles[] = 'ROLE_ADMIN';
                    $roles[] = 'ROLE_USER';
                }
                else
                {

                    /** Получаем список ролей и правил группы */
                    $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

                    $qb->select("
                       ARRAY(SELECT DISTINCT UNNEST(
                            ARRAY_AGG(profile_role.prefix) || 
                            ARRAY_AGG(profile_voter.prefix)
                        )) AS roles
                    ");

                    $qb->from(ProfileGroup::class, 'profile_group');

                    $qb->leftJoin(
                        'profile_group',
                        ProfileRole::class,
                        'profile_role',
                        'profile_role.event = profile_group.event'
                    );

                    $qb->leftJoin(
                        'profile_role',
                        ProfileVoter::class,
                        'profile_voter',
                        'profile_voter.role = profile_role.id'
                    );

                    $qb->andWhere('profile_group.prefix = :prefix')
                        ->setParameter('prefix', $group, GroupPrefix::TYPE);

                    $qb->andWhere('profile_role.prefix IS NOT NULL');
                    $qb->andWhere('profile_voter.prefix IS NOT NULL');


                    if($authority)
                    {
                        $qb->andWhere('profile_group.profile = :authority')
                            ->setParameter('authority', $authority, UserProfileUid::TYPE);
                    }

                    $roles = $qb
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
                }

                return array_filter($roles);
            }

            $roles[] = 'ROLE_ADMINISTRATION';

        }

        $roles[] = 'ROLE_USER';


        return $roles;
    }
}
