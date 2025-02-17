<?php

declare(strict_types=1);

namespace App\Tests\DataFixture\Role;

use App\Role\Domain\Entity\Role;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class CreateUserRoleFixture extends AbstractFixture
{
    public const string ROLE_USER_REFERENCE = 'role-user';

    public function load(ObjectManager $manager): void
    {
        $role = Role::create(
            'ROLE_USER',
            'Пользователь',
        );
        $manager->persist($role);
        $manager->flush();

        $this->addReference(self::ROLE_USER_REFERENCE, $role);
    }
}
