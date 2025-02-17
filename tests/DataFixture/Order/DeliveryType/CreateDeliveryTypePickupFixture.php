<?php

declare(strict_types=1);

namespace App\Tests\DataFixture\Order\DeliveryType;

use App\Order\Domain\Entity\DeliveryType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class CreateDeliveryTypePickupFixture extends AbstractFixture
{
    public const DELIVERY_TYPE_SLUG_FOR_TESTS = 'pickup';
    public const DELIVERY_TYPE = 'delivery-type';

    public function load(ObjectManager $manager): void
    {
        $deliveryType = DeliveryType::create(
            self::DELIVERY_TYPE_SLUG_FOR_TESTS,
            'Самовывоз',
        );
        $manager->persist($deliveryType);
        $manager->flush();

        $this->addReference(self::DELIVERY_TYPE, $deliveryType);
    }
}
