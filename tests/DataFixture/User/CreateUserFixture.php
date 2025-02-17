<?php

declare(strict_types=1);

namespace App\Tests\DataFixture\User;

use App\Common\Domain\Exception\Validation\GreaterThanMaxLengthException;
use App\Common\Domain\Exception\Validation\GreaterThanMaxValueException;
use App\Common\Domain\Exception\Validation\InvalidEmailException;
use App\Common\Domain\Exception\Validation\LessThanMinLengthException;
use App\Common\Domain\Exception\Validation\LessThanMinValueException;
use App\Common\Domain\Exception\Validation\WrongLengthOfPhoneNumberException;
use App\Common\Domain\ValueObject\Email;
use App\Common\Domain\ValueObject\RuPhoneNumber;
use App\Role\Domain\Entity\Role;
use App\Tests\DataFixture\Role\CreateUserRoleFixture;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Delivery;
use App\User\Domain\ValueObject\Name;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\UuidV4;

final class CreateUserFixture extends AbstractFixture
{
    public const string USER_EMAIL_FOR_TESTS = 'test@example.com';
    public const string USER = 'user';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * @throws GreaterThanMaxLengthException
     * @throws GreaterThanMaxValueException
     * @throws InvalidEmailException
     * @throws LessThanMinLengthException
     * @throws LessThanMinValueException
     * @throws WrongLengthOfPhoneNumberException
     */
    public function load(ObjectManager $manager): void
    {
        /* @var Role $roleUser */
        $roleUser = $this->getReference(
            CreateUserRoleFixture::ROLE_USER_REFERENCE,
            Role::class,
        );

        $user = User::create(
            name: Name::fromString('test'),
            email: Email::fromString(self::USER_EMAIL_FOR_TESTS),
            phone: RuPhoneNumber::fromInt(1234567890),
            promoId: UuidV4::v4(),
            delivery: Delivery::create('Test', '123456789'),
            roles: new ArrayCollection([$roleUser]),
        );
        $userPassword = $this->passwordHasher->hashPassword($user, 'password');
        $user->setPassword($userPassword);
        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::USER, $user);
    }
}
