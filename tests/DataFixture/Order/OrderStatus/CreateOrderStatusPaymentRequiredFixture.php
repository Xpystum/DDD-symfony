<?php

declare(strict_types=1);

namespace App\Tests\DataFixture\Order\OrderStatus;

use App\Order\Domain\Entity\OrderStatus;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class CreateOrderStatusPaymentRequiredFixture extends AbstractFixture
{
    public const ORDER_STATUS = 'order-status';

    public function load(ObjectManager $manager): void
    {
        $orderStatus = OrderStatus::create(
            'payment_required',
            'Ожидается оплата',
        );
        $manager->persist($orderStatus);
        $manager->flush();

        $this->addReference(self::ORDER_STATUS, $orderStatus);
    }
}
