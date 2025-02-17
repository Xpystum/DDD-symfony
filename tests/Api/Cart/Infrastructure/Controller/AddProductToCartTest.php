<?php

declare(strict_types=1);

namespace App\Tests\Api\Cart\Infrastructure\Controller;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepositoryInterface;
use App\Tests\Api\AbstractApiBaseTestCase;
use App\Tests\DataFixture\Product\CreateProductFixture;
use App\Tests\DataFixture\User\CreateUserFixture;
use App\User\Domain\Repository\UserRepositoryInterface;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\UuidV4;

final class AddProductToCartTest extends AbstractApiBaseTestCase
{
    private const string CONTROLLER_ROUTE_NAME = 'cart.addProductToCart';
    private string $productIdToAddToCart;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = self::getService(UserRepositoryInterface::class);
        $user = $userRepository->getByEmail(CreateUserFixture::USER_EMAIL_FOR_TESTS);

        $productRepository = self::getService(ProductRepositoryInterface::class);

        /* @var Product $product */
        $product = $productRepository->findById(CreateProductFixture::PRODUCT_ID_FOR_TESTS);
        if (null === $product) {
            throw new Exception('There are no products');
        }

        $this->productIdToAddToCart = $product->getId()->toString();

        $this->client->loginUser($user);
    }

    public function getFixtures(): array
    {
        $fixtures = parent::getFixtures();
        $fixtures[] = new CreateProductFixture();

        return $fixtures;
    }

    public function testSuccessAddProductToCart(): void
    {
        $this->sendRequestByControllerName(
            controllerRouteName: self::CONTROLLER_ROUTE_NAME,
            routeParams: [
                'productId' => $this->productIdToAddToCart,
            ],
        );

        $this->checkJsonableResponseByHttpCode();
    }

    public function testFailedAddProductToCartDueToWrongProductId(): void
    {
        $this->sendRequestByControllerName(
            controllerRouteName: self::CONTROLLER_ROUTE_NAME,
            routeParams: [
                'productId' => UuidV4::v4()->toString(),
            ],
        );

        $this->checkJsonableResponseByHttpCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
