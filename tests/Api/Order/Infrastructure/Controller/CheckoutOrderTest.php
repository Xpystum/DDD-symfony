<?php

declare(strict_types=1);

namespace App\Tests\Api\Order\Infrastructure\Controller;

use App\Tests\Api\AbstractApiBaseTestCase;
use App\Tests\DataFixture\Cart\AddProductToCartFixture;
use App\Tests\DataFixture\Order\DeliveryType\CreateDeliveryTypePickupFixture;
use App\Tests\DataFixture\Order\OrderStatus\CreateOrderStatusPaymentRequiredFixture;
use App\Tests\DataFixture\Product\CreateProductFixture;
use App\Tests\DataFixture\User\CreateUserFixture;
use App\User\Application\Exception\UserNotFoundException;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

final class CheckoutOrderTest extends AbstractApiBaseTestCase
{
    private const string CONTROLLER_ROUTE_NAME = 'order.checkout';
    private User $user;

    /**
     * @throws UserNotFoundException
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
        $fixtures[] = new AddProductToCartFixture();
        $fixtures[] = new CreateDeliveryTypePickupFixture();
        $fixtures[] = new CreateOrderStatusPaymentRequiredFixture();

        return $fixtures;
    }

    public function testSuccessCheckoutOrderForPickupDeliveryType(): void
    {
        $this->sendRequestByControllerName(self::CONTROLLER_ROUTE_NAME, [
            'deliveryType' => CreateDeliveryTypePickupFixture::DELIVERY_TYPE_SLUG_FOR_TESTS,
        ]);

        $this->checkJsonableResponseByHttpCode(Response::HTTP_CREATED);
    }

    public function testFailedCheckoutOrderDueToWrongDeliveryType(): void
    {
        $this->sendRequestByControllerName(self::CONTROLLER_ROUTE_NAME, [
            'deliveryType' => 'wrong-type',
        ]);

        $this->checkJsonableResponseByHttpCode(Response::HTTP_NOT_FOUND);
    }
}
