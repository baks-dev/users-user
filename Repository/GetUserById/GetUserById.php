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


final class GetUserById implements GetUserByIdInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;
    private DBALQueryBuilder $DBALQueryBuilder;
    private ProfileGroupByUserProfileInterface $profileGroupByUserProfile;
    private ExistProfileGroupInterface $existProfileGroup;
    private AppCacheInterface $cache;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        ORMQueryBuilder $ORMQueryBuilder,
        ProfileGroupByUserProfileInterface $profileGroupByUserProfile,
        ExistProfileGroupInterface $existProfileGroup,
        AppCacheInterface $cache
    )
    {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
        $this->profileGroupByUserProfile = $profileGroupByUserProfile;
        $this->existProfileGroup = $existProfileGroup;
        $this->cache = $cache;
    }

    /**
     * Метод возвращает сущность User
     */
    public function get(UserUid $userUid): mixed
    {

        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $qb->select('users');
        $qb->from(User::class, 'users');

        if(class_exists(UserProfileInfo::class))
        {
            $select = sprintf('new %s(users.id, info.profile)',
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

        $qb->where('users.id = :userUid');
        $qb->setParameter('userUid', $userUid, UserUid::TYPE);
        $qb->setMaxResults(1);


        /** @var User $usr */
        $usr = $qb
            ->enableCache((string) $userUid, 86400)
            ->getOneOrNullResult();

        //dump((string) $usr->getProfile());


        //$usr = $qb->getOneOrNullResult();

        //        $token = new SwitchUserToken($user, $user->getRoles(), $this->getUser()->getOriginalToken());
        //        $this->tokenStorage->setToken($token);
        //

        //dump($usr);


        /** Получаем группу профиля пользователя */
        if($usr->getProfile() instanceof UserProfileUid)
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

        return $usr;
    }

    public function fetchAllRoleUser(UserProfileUid $profile, UserProfileUid|bool|null $authority = null): ?array
    {
        //$session = $this->requestStack->getSession();
        //$authority = $session->get($usr->getProfile(), null);


        //dump($profile);
        //dd($authority);

        //dump($authority);
        //        /dump($profile);

        //dd($profile);


        //dump($profile);

        /** Проверяем, имеется ли у пользователя группа либо доверенность */
        $existGroup = $this->existProfileGroup->isExistsProfileGroup($profile);

        //dump((string) $profile);

        if($existGroup)
        {
            /** Получаем префикс группы профиля
             * $authority = false - если администратор ресурса
             * */
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

                    $qb->from(ProfileGroup::TABLE, 'profile_group');

                    $qb->leftJoin(
                        'profile_group',
                        ProfileRole::TABLE,
                        'profile_role',
                        'profile_role.event = profile_group.event'
                    );

                    $qb->leftJoin(
                        'profile_role',
                        ProfileVoter::TABLE,
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
                        //->enableCache('UserGroup', 86400)
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