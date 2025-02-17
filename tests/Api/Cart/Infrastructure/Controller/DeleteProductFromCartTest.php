<?php

declare(strict_types=1);

namespace App\Tests\Api\Cart\Infrastructure\Controller;

use App\Cart\Application\Exception\ProductWasNotAddedToCartException;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepositoryInterface;
use App\Tests\Api\AbstractApiBaseTestCase;
use App\Tests\DataFixture\Cart\AddProductToCartFixture;
use App\Tests\DataFixture\Product\CreateProductFixture;
use App\Tests\DataFixture\User\CreateUserFixture;
use App\User\Application\Exception\UserNotFoundException;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\UuidV4;

final class DeleteProductFromCartTest extends AbstractApiBaseTestCase
{
    private const string CONTROLLER_ROUTE_NAME = 'cart.deleteProductFromCart';
    private string $productIdToDeleteFromCart;
    private User $user;

    /**
     * @throws UserNotFoundException
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = self::getService(UserRepositoryInterface::class);
        $this->user = $userRepository->getByEmail(CreateUserFixture::USER_EMAIL_FOR_TESTS);

        $productRepository = self::getService(ProductRepositoryInterface::class);

        /* @var Product $product */
        $product = $productRepository->findById(CreateProductFixture::PRODUCT_ID_FOR_TESTS);
        if (null === $product) {
            throw new Exception('There are no products');
        }

        $this->productIdToDeleteFromCart = $product->getId()->toString();

        $this->client->loginUser($this->user);
    }

    public function getFixtures(): array
    {
        $fixtures = parent::getFixtures();
        $fixtures[] = new CreateProductFixture();
        $fixtures[] = new AddProductToCartFixture();

        return $fixtures;
    }

    public function testSuccessDeleteProductFromCart(): void
    {
        $this->sendRequestByControllerName(
            controllerRouteName: self::CONTROLLER_ROUTE_NAME,
            routeParams: [
                'productId' => $this->productIdToDeleteFromCart,
            ],
        );

        $this->checkJsonableResponseByHttpCode();
        $this->expectException(ProductWasNotAddedToCartException::class);

        $this->user->getProductByIdFromCart($this->productIdToDeleteFromCart);
    }

    public function testFailedDeleteProductFromCartDueToWrongProductId(): void
    {
        $this->sendRequestByControllerName(
            controllerRouteName: self::CONTROLLER_ROUTE_NAME,
            routeParams: [
                'productId' => UuidV4::v4()->toString(),
            ],
        );

        $this->checkJsonableResponseByHttpCode(Response::HTTP_NOT_FOUND);
    }
}
