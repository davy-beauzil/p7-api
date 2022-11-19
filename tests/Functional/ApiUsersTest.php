<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Fixtures\CustomerFixtures;
use App\Tests\Fixtures\UserFixtures;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class ApiUsersTest extends AbstractAppCase
{
    /**
     * $firstUser = $this->addCustomer();
     * $secondUser = $this->addCustomer('new-user@test.fr', 'test');
     * $this->addUsersToCustomer($firstUser);
     * $this->addUsersToCustomer($secondUser);
     */

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @throws \Exception
     */
    public function customerCanSeeLinkedUsersListTest(): void
    {
        // Given
        $customer = $this->getCustomerFixtures()->addCustomer();
        for ($i = 0; $i < 5; $i++) {
            $this->getUserFixtures()->addUser($customer);
        }
        $jwt = $this->getJWT($customer);

        // When
        $this->client->request(Request::METHOD_GET, '/api/users', server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);
        $response = $this->client->getResponse();
        $content = $response->getContent();
        $arrayContent = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        // Then
        static::assertResponseIsSuccessful();
        static::assertCount(5, $arrayContent);
    }

    /**
     * @test
     */
    public function anonymousCannotSeeUsersListTest(): void
    {
        // Given
        // When
        $this->client->request(Request::METHOD_GET, '/api/users');
        $response = $this->client->getResponse();
        $content = $response->getContent();

        // Then
        static::assertResponseStatusCodeSame(401);
        static::assertStringContainsString('JWT Token not found', $response->getContent());
    }

    /**
     * @test
     */
    public function customerCanSeeDetailFromLinkedUserTest(): void
    {
        // Given
        $customer = $this->getCustomerFixtures()->addCustomer();
        $user = $this->getUserFixtures()->addUser($customer);
        $jwt = $this->getJWT($customer);

        // When
        $this->client->request(Request::METHOD_GET, sprintf('/api/users/%s', $user->getId()), server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);
        $response = $this->client->getResponse();
        $content = $response->getContent();

        // Then
        static::assertResponseIsSuccessful();
        static::assertStringContainsString($user->getId(), $content);
        static::assertStringContainsString($user->getFirstname(), $content);
        static::assertStringContainsString($user->getLastname(), $content);
        static::assertStringContainsString($user->getEmail(), $content);
        static::assertStringContainsString($user->getPhoneNumber(), $content);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function customerCannotSeeDetailFromUnlinkedUserTest(): void
    {
        // Given
        $fixtures = $this->getCustomerFixtures();
        $customer = $fixtures->addCustomer();
        $customer2 = $fixtures->addCustomer('customer2@test.fr');
        $user = $this->getUserFixtures()->addUser($customer2);
        $jwt = $this->getJWT($customer);

        // When
        $this->client->request(Request::METHOD_GET, sprintf('/api/users/%s', $user->getId()), server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);
        $response = $this->client->getResponse();
        $content = $response->getContent();

        // Then
        static::assertResponseStatusCodeSame(404);
        static::assertStringContainsString('Resource not found', $response->getContent());
    }

    /**
     * @test
     */
    public function anonymousCannotSeeDetailFromUserTest(): void
    {
        // Given
        $customer = $this->getCustomerFixtures()->addCustomer();
        $user = $this->getUserFixtures()->addUser($customer);

        // When
        $this->client->request(Request::METHOD_GET, sprintf('/api/users/%s', $user->getId()));
        $response = $this->client->getResponse();

        // Then
        static::assertResponseStatusCodeSame(401);
        static::assertStringContainsString('JWT Token not found', $response->getContent());
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function customerCanCreateANewUserTest(): void
    {
        // Given
        $customer = $this->getCustomerFixtures()->addCustomer();
        $jwt = $this->getJWT($customer);

        // When
        $this->client->request(Request::METHOD_POST, '/api/users', ['firstname' => 'firstname', 'lastname' => 'lastname', 'email' => 'test@test.fr', 'phoneNumber' => '00 00 00 00 00'], server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);
        $response = $this->client->getResponse();

        // Then
        static::assertResponseStatusCodeSame(201);
        static::assertStringContainsString('Created', $response->getContent());
        static::assertStringContainsString('/api/users/', $response->headers->get('Location'));
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function anonymousCannotCreateNewUserTest(): void
    {
        // Given
        // When
        $this->client->request(Request::METHOD_POST, '/api/users', ['firstname' => 'firstname', 'lastname' => 'lastname', 'email' => 'test@test.fr', 'phoneNumber' => '00 00 00 00 00']);
        $response = $this->client->getResponse();

        // Then
        static::assertResponseStatusCodeSame(401);
        static::assertStringContainsString('JWT Token not found', $response->getContent());
    }

    /**
     * @test
     * @dataProvider customerGetBadRequestIfHeDontRespectUserValidations_dataprovider
     * @throws \JsonException
     */
    public function customerGetBadRequestIfHeDontRespectUserValidations(User $user, int $statusCode, string $message): void
    {
        // Given
        $customer = $this->getCustomerFixtures()->addCustomer();
        $jwt = $this->getJWT($customer);
        $parameters = [];

        $rp = new \ReflectionProperty(User::class, 'firstname');
        if ($rp->isInitialized($user)) {
            $parameters['firstname'] = $user->getFirstname();
        }
        $rp = new \ReflectionProperty(User::class, 'lastname');
        if ($rp->isInitialized($user)) {
            $parameters['lastname'] = $user->getLastname();
        }
        $rp = new \ReflectionProperty(User::class, 'email');
        if ($rp->isInitialized($user)) {
            $parameters['email'] = $user->getEmail();
        }
        $rp = new \ReflectionProperty(User::class, 'phoneNumber');
        if ($rp->isInitialized($user)) {
            $parameters['phoneNumber'] = $user->getPhoneNumber();
        }

        // When
        $this->client->request(Request::METHOD_POST, '/api/users', $parameters, server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);
        $response = $this->client->getResponse();

        // Then
        static::assertResponseStatusCodeSame($statusCode);
        static::assertStringContainsString($message, $response->getContent());
    }

    private function customerGetBadRequestIfHeDontRespectUserValidations_dataprovider(): array
    {
        return [
            [(new User())->setFirstname('test')->setLastname('test')->setEmail('test'), 400, 'Bad Request'],
            [(new User())->setFirstname('test')->setLastname('test')->setPhoneNumber('test'), 400, 'Bad Request'],
            [(new User())->setFirstname('test')->setPhoneNumber('test')->setEmail('test'), 400, 'Bad Request'],
            [(new User())->setPhoneNumber('test')->setLastname('test')->setEmail('test'), 400, 'Bad Request'],
            [(new User())->setFirstname('test')->setLastname('test')->setEmail('test')->setPhoneNumber('test'), 201, 'Created'],
        ];
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function customerCanRemoveLinkedUserTest(): void
    {
        // Given
        $customer = $this->getCustomerFixtures()->addCustomer();
        $user = $this->getUserFixtures()->addUser($customer);
        $jwt = $this->getJWT($customer);

        // When
        $this->client->request(Request::METHOD_DELETE, sprintf('/api/users/%s', $user->getId()), server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);
        $response = $this->client->getResponse();

        // Then
        static::assertResponseStatusCodeSame(204);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function customerCannotRemoveUserNotLinkedTest(): void
    {
        // Given
        $fixtures = $this->getCustomerFixtures();
        $customer = $fixtures->addCustomer();
        $customer2 = $fixtures->addCustomer('customer2@test.fr');
        $user = $this->getUserFixtures()->addUser($customer2);
        $jwt = $this->getJWT($customer);

        // When
        $this->client->request(Request::METHOD_DELETE, sprintf('/api/users/%s', $user->getId()), server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);
        $response = $this->client->getResponse();

        // Then
        static::assertResponseStatusCodeSame(404);
        static::assertStringContainsString('Resource not found', $response->getContent());
    }
}
