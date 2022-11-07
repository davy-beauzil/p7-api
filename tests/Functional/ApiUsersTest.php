<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class ApiUsersTest extends AbstractAppCase
{
    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->makeFixtures();
    }

    /**
     * @test
     * @throws \Exception
     */
    public function customerCanSeeLinkedUsersListTest(): void
    {
        // Given
        $jwt = $this->getJWT();

        // When
        $this->client->request(Request::METHOD_GET, '/api/users', server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);
        $response = $this->client->getResponse();
        $content = $response->getContent();

        // Then
        static::assertResponseIsSuccessful();
        static::assertStringContainsString('test@test.fr0', $content);
        static::assertStringContainsString('test@test.fr1', $content);
        static::assertStringContainsString('test@test.fr2', $content);
        static::assertStringContainsString('test@test.fr3', $content);
        static::assertStringContainsString('test@test.fr4', $content);
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
        $jwt = $this->getJWT();
        $em = $this->getEntityManager();
        /** @var Customer $customer */
        $customer = $em->getRepository(Customer::class)
            ->findOneBy(['email' => 'test@test.fr']);
        $user = $customer->getUsers()->first();

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
        $jwt = $this->getJWT();
        $em = $this->getEntityManager();
        /** @var Customer $customer */
        $customer = $em->getRepository(Customer::class)
            ->findOneBy(['email' => 'new-user@test.fr']);
        $user = $customer->getUsers()->first();

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
        $em = $this->getEntityManager();
        /** @var Customer $customer */
        $customer = $em->getRepository(Customer::class)
            ->findOneBy(['email' => 'new-user@test.fr']);
        $user = $customer->getUsers()->first();

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
        $jwt = $this->getJWT();

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
        $jwt = $this->getJWT();
        $parameters = [];

        $rp = new \ReflectionProperty(User::class, 'firstname');
        if($rp->isInitialized($user)){
            $parameters['firstname'] = $user->getFirstname();
        }
        $rp = new \ReflectionProperty(User::class, 'lastname');
        if($rp->isInitialized($user)){
            $parameters['lastname'] = $user->getLastname();
        }
        $rp = new \ReflectionProperty(User::class, 'email');
        if($rp->isInitialized($user)){
            $parameters['email'] = $user->getEmail();
        }
        $rp = new \ReflectionProperty(User::class, 'phoneNumber');
        if($rp->isInitialized($user)){
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
        $jwt = $this->getJWT();
        /** @var Customer $customer */
        $customer = $this->getEntityManager()->getRepository(Customer::class)
            ->findOneBy(['email' => 'test@test.fr']);
        $user = $customer->getUsers()->first();

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
        $jwt = $this->getJWT();
        /** @var Customer $customer */
        $customer = $this->getEntityManager()->getRepository(Customer::class)
            ->findOneBy(['email' => 'new-user@test.fr']);
        $user = $customer->getUsers()->first();

        // When
        $this->client->request(Request::METHOD_DELETE, sprintf('/api/users/%s', $user->getId()), server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);
        $response = $this->client->getResponse();

        // Then
        static::assertResponseStatusCodeSame(404);
        static::assertStringContainsString('Resource not found', $response->getContent());
    }

    /**
     * @throws \Exception
     */
    private function makeFixtures(): void
    {
        $firstUser = $this->addCustomer();
        $secondUser = $this->addCustomer('new-user@test.fr', 'test');

        $this->addUsersToCustomer($firstUser);
        $this->addUsersToCustomer($secondUser);
    }

    /**
     * @throws \Exception
     */
    private function addUsersToCustomer(Customer $customer, int $nbUsers = 5): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get(UserRepository::class);

        for($i = 0 ; $i < $nbUsers ; $i++){
            $user = new User();
            $user->setFirstname($customer->getEmail() . $i)
                ->setLastname($customer->getEmail() . $i)
                ->setEmail($customer->getEmail() . $i)
                ->setPhoneNumber($customer->getEmail() . $i)
                ->setCustomer($customer);
            $customer->addUser($user);
            $userRepository->add($user, true);
        }
    }
}