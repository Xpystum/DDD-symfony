<?php

declare(strict_types=1);

namespace App\Tests\Api\Cart\Infrastructure\Controller;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepositoryInterface;
use App\Tests\Api\AbstractApiBaseTestCase;
use App\Tests\DataFixture\Product\CreateProductFixture;
use App\Tests\DataFixture\User\CreateUserFixture;
use App\User\Application\Exception\ProductAlreadyAddedToCartException;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use Exception;

final class CartProductListTest extends AbstractApiBaseTestCase
{
    private const string CONTROLLER_ROUTE_NAME = 'cart.productList';
    private User $user;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = self::getService(UserRepositoryInterface::class);
        $this->user = $userRepository->getByEmail(CreateUserFixture::USER_EMAIL_FOR_TESTS);

        $this->client->loginUser($this->user);
    }

    public function getFixtures(): array
    {
        $fixtures = parent::getFixtures();
        $fixtures[] = new CreateProductFixture();

        return $fixtures;
    }

    /**
     * @throws ProductAlreadyAddedToCartException
     * @throws Exception
     */
    public function testSuccessNotEmptyCartProductList(): void
    {
        $productRepository = self::getService(ProductRepositoryInterface::class);

        /* @var Product $product */
        $product = $productRepository->findById(CreateProductFixture::PRODUCT_ID_FOR_TESTS);
        if (null === $product) {
            throw new Exception('There are no products');
        }

        $this->user->addProductToCart($product);
        $this->entityManager->flush();

        $this->sendRequestByControllerName(self::CONTROLLER_ROUTE_NAME);

        $responseData = $this->getResponseData();
        $this->assertNotEmpty($responseData);
    }

    public function testSuccessEmptyCartProductList(): void
    {
        $this->sendRequestByControllerName(self::CONTROLLER_ROUTE_NAME);

        $this->checkJsonableResponseByHttpCode();

        $responseData = $this->getResponseData();
        $this->assertEmpty($responseData);
    }

    private function getResponseData(): mixed
    {
        $responseData = $this->client->getResponse()->getContent();

        return $this->decoder->decode($responseData, 'json');
    }
}
