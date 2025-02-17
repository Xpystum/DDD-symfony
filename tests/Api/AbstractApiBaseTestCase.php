<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Common\Application\Filesystem\FilesystemInterface;
use App\Common\Infrastructure\Repository\Flusher;
use App\Tests\DataFixture\Role\CreateUserRoleFixture;
use App\Tests\DataFixture\User\CreateUserFixture;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractApiBaseTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected ?EntityManagerInterface $entityManager;
    protected UserPasswordHasherInterface $passwordHasher;
    protected SerializerInterface $serializer;
    protected DecoderInterface $decoder;
    protected RouterInterface $router;
    protected Flusher $flusher;

    /**
     * @template T
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    protected static function getService(string $id): object
    {
        return self::getContainer()->get($id);
    }

    protected function setUp(): void
    {
        $this->injectDependencies();

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        if (!empty($metadata)) {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }

        $this->executeFixtures();
    }

    protected function injectDependencies(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);
        $this->serializer = $this->client->getContainer()->get(SerializerInterface::class);
        $this->decoder = $this->client->getContainer()->get(DecoderInterface::class);
        $this->router = $this->client->getContainer()->get(RouterInterface::class);
        $this->flusher = $this->client->getContainer()->get(Flusher::class);

        $this->client->getContainer()->set(FilesystemInterface::class, new FilesystemMock());
    }

    private function executeFixtures(): void
    {
        $fixtures = $this->getFixtures();

        $fixtureLoader = new Loader();
        /* @var AbstractFixture $fixture */
        foreach ($fixtures as $fixture) {
            $fixtureLoader->addFixture($fixture);
        }

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($fixtureLoader->getFixtures());
    }

    protected function getFixtures(): array
    {
        return [
            new CreateUserRoleFixture(),
            new CreateUserFixture($this->passwordHasher),
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->clear();
        $this->entityManager->close();
    }

    protected function sendRequestByControllerName(
        string $controllerRouteName,
        array $body = [],
        array $routeParams = [],
        array $getParams = [],
    ): void {
        $controller = $this->router->getRouteCollection()->get($controllerRouteName);

        $path = $controller->getPath();
        $uri = static function () use ($path, $routeParams): string {
            foreach ($routeParams as $key => $value) {
                $path = str_replace('{' . $key . '}', $value, $path);
            }

            return $path;
        };

        $this->client->request(
            current($controller->getMethods()),
            $uri(),
            $getParams,
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->serializer->serialize($body, 'json'),
        );
    }

    protected function checkJsonableResponseByHttpCode(int $statusCode = Response::HTTP_OK): void
    {
        $responseJson = $this->client->getResponse()->getContent();

        $this->assertEquals(
            $statusCode,
            $this->client->getResponse()->getStatusCode(),
        );
        $this->assertJson($responseJson);
    }
}
