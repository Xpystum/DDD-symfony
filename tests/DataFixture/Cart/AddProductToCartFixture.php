<?php

declare(strict_types=1);

namespace App\Tests\DataFixture\Cart;

use App\Product\Domain\Entity\Product;
use App\Tests\DataFixture\Product\CreateProductFixture;
use App\Tests\DataFixture\User\CreateUserFixture;
use App\User\Domain\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class AddProductToCartFixture extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        /* @var User $user */
        $user = $this->getReference(CreateUserFixture::USER, User::class);
        /* @var Product $product */
        $product = $this->getReference(CreateProductFixture::PRODUCT, Product::class);

        $user->addProductToCart($product);

        $manager->flush();
    }
}
