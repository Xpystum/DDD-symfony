<?php

declare(strict_types=1);

namespace App\Tests\DataFixture\Order;

use App\Common\Domain\Exception\Validation\GreaterThanMaxLengthException;
use App\Common\Domain\Exception\Validation\GreaterThanMaxValueException;
use App\Common\Domain\Exception\Validation\LessThanMinValueException;
use App\Order\Domain\Entity\DeliveryType;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\Entity\OrderStatus;
use App\Tests\DataFixture\Order\DeliveryType\CreateDeliveryTypePickupFixture;
use App\Tests\DataFixture\Order\OrderStatus\CreateOrderStatusPaymentRequiredFixture;
use App\Tests\DataFixture\User\CreateUserFixture;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Delivery;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class CreateOrderFixture extends AbstractFixture
{
    /**
     * @throws GreaterThanMaxLengthException
     * @throws GreaterThanMaxValueException
     * @throws LessThanMinValueException
     */
    public function load(ObjectManager $manager): void
    {
        /* @var User $user */
        $user = $this->getReference(CreateUserFixture::USER, User::class);
        /* @var OrderStatus $orderStatusPaymentRequired */
        $orderStatusPaymentRequired = $this->getReference(
            CreateOrderStatusPaymentRequiredFixture::ORDER_STATUS,
            OrderStatus::class,
        );
        /* @var DeliveryType $deliveryTypePickup */
        $deliveryTypePickup = $this->getReference(
            CreateDeliveryTypePickupFixture::DELIVERY_TYPE,
            DeliveryType::class,
        );

        $order = Order::create(
            user: $user,
            phone: $user->getPhone()->getPhone(),
            status: $orderStatusPaymentRequired,
            delivery: Delivery::create('Address', '9999999999999'),
            deliveryType: $deliveryTypePickup,
        );
        $user->checkoutOrder($order);

        $manager->persist($user);
        $manager->flush();
    }
}
