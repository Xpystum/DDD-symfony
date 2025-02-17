<?php

declare(strict_types=1);

namespace App\Tests\DataFixture\Product;

use App\Product\Domain\Entity\Product;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\UuidV4;

final class CreateProductFixture extends AbstractFixture
{
    public const string PRODUCT_ID_FOR_TESTS = '2eee847d-c877-4804-93f7-c27e34161060';
    public const string PRODUCT = 'product';

    public function load(ObjectManager $manager): void
    {
        $product = Product::create(
            name: 'Product 1',
            weight: 10,
            height: 10,
            width: 10,
            length: 10,
            description: 'Description',
            cost: 100,
            tax: 0,
            version: 1,
            id: UuidV4::fromString(self::PRODUCT_ID_FOR_TESTS),
        );
        $manager->persist($product);
        $manager->flush();

        $this->addReference(self::PRODUCT, $product);
    }
}
