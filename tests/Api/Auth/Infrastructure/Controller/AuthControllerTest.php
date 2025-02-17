<?php

declare(strict_types=1);

namespace App\Tests\Api\Auth\Infrastructure\Controller;

use App\Tests\Api\AbstractApiBaseTestCase;
use Symfony\Component\HttpFoundation\Response;

final class AuthControllerTest extends AbstractApiBaseTestCase
{
    private const string CONTROLLER_ROUTE_NAME = 'auth.signUp';

    public function testSuccessSignUp(): void
    {
        $this->sendRequestByControllerName(self::CONTROLLER_ROUTE_NAME, [
            'name' => 'Less Grossman',
            'email' => 'less-grossman@test.com',
            'phone' => 8005553535,
            'kladrId' => '1000000000',
            'address' => 'New York',
            'password' => 'LKdkf291DSxz!?S',
        ]);

        $this->checkJsonableResponseByHttpCode(Response::HTTP_CREATED);
    }

    public function testFailedSignUpDueToEmptyBody(): void
    {
        $this->sendRequestByControllerName(self::CONTROLLER_ROUTE_NAME, [
            'name' => '',
            'email' => '',
            'phone' => '',
            'password' => '',
        ]);

        $this->checkJsonableResponseByHttpCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
