<?php

declare(strict_types=1);

namespace App\Tests\Api\Report\Infrastructure\Controller;

use App\Common\Domain\Exception\Validation\GreaterThanMaxLengthException;
use App\Common\Domain\Exception\Validation\GreaterThanMaxValueException;
use App\Common\Domain\Exception\Validation\LessThanMinValueException;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\Repository\DeliveryTypeRepositoryInterface;
use App\Order\Domain\Repository\OrderStatusRepositoryInterface;
use App\Tests\Api\AbstractApiBaseTestCase;
use App\Tests\DataFixture\Cart\AddProductToCartFixture;
use App\Tests\DataFixture\Order\DeliveryType\CreateDeliveryTypePickupFixture;
use App\Tests\DataFixture\Order\OrderStatus\CreateOrderStatusPaymentRequiredFixture;
use App\Tests\DataFixture\Product\CreateProductFixture;
use App\Tests\DataFixture\User\CreateUserFixture;
use App\User\Application\Exception\ProductAlreadyAddedToCartException;
use App\User\Application\Exception\UserNotFoundException;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Delivery;
use Symfony\Component\HttpFoundation\Response;

final class GenerateSoldProductsReportTest extends AbstractApiBaseTestCase
{
    private const string CONTROLLER_ROUTE_NAME = 'report.sold_products.generate';
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

    /**
     * @throws ProductAlreadyAddedToCartException
     * @throws GreaterThanMaxLengthException
     * @throws LessThanMinValueException
     * @throws GreaterThanMaxValueException
     */
    public function testSuccessGenerateSoldProductsReport(): void
    {
        $orderStatusRepository = self::getService(OrderStatusRepositoryInterface::class);
        $orderStatus = $orderStatusRepository->getPaymentRequiredOrderStatus();

        $deliveryTypeRepository = self::getService(DeliveryTypeRepositoryInterface::class);
        $deliveryType = $deliveryTypeRepository->getBySlug(
            CreateDeliveryTypePickupFixture::DELIVERY_TYPE_SLUG_FOR_TESTS
        );

        $order = Order::create(
            user: $this->user,
            phone: $this->user->getPhone()->getPhone(),
            status: $orderStatus,
            delivery: Delivery::create('Address', '9999999999999'),
            deliveryType: $deliveryType,
        );
        $this->user->checkoutOrder($order);

        $this->flusher->flush();


        $this->sendRequestByControllerName(self::CONTROLLER_ROUTE_NAME);

        $this->checkJsonableResponseByHttpCode(Response::HTTP_CREATED);
    }

    public function testFailedGenerateSoldProductsReportDueToNotFoundAnyProductsSoldInLast24Hours(): void
    {
        $this->sendRequestByControllerName(self::CONTROLLER_ROUTE_NAME);

        $responseJson = $this->client->getResponse()->getContent();
        $responseData = $this->decoder->decode($responseJson, 'json');

        $this->checkJsonableResponseByHttpCode(Response::HTTP_NOT_FOUND);
        $this->assertJson($responseJson);
        $this->assertEquals('fail', $responseData['result'] ?? null);
    }
}
