<?php

namespace Functional;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use App\Tests\Fixtures\CustomerFixtures;
use App\Tests\Fixtures\PhoneFixtures;
use App\Tests\Functional\AbstractAppCase;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class ApiPhonesTest extends AbstractAppCase
{
    private function __construct(
        private readonly PhoneFixtures $phoneFixtures,
        private readonly CustomerFixtures $customerFixtures,
    )
    {}

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Check if a customer logged in can access to phones list
     * @throws Exception
     * @test
     */
    public function customerCanSeePhonesList(): void
    {
        // Given
        $me = $this->customerFixtures->addCustomer('test@test.fr');
        $phones = [];
//        $this->addCustomer();
        $jwt = $this->getJWT();
        $this->phoneFixtures->addPhone()

        // When
        $this->client->request(Request::METHOD_GET, '/api/phones', server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);

        // Then
        $response = $this->client->getResponse()->getContent();
        static::assertResponseIsSuccessful();
        static::assertJson($response);
        static::assertGreaterThan(1, count(json_decode($response, true)));
    }

    /**
     * Check if customer logged in can access to phone details
     * @test
     * @throws Exception
     */
    public function customerCanSeePhonesDetails(): void
    {
        // Given
        $this->addCustomer();
        $jwt = $this->getJWT();
        $phone = $this->getPhone();

        // When
        $this->client->request(Request::METHOD_GET, sprintf('/api/phones/%s', $phone->getId()), server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);

        // Then
        $response = $this->client->getResponse()->getContent();
        static::assertResponseIsSuccessful();
        static::assertJson($response);
        static::assertGreaterThan(1, count(json_decode($response, true)));
    }

    /**
     * Check if not authenticated person get an Unauthorized error when try to access to phones list
     * @test
     */
    public function notAuthenticatedCannotSeePhonesList(): void
    {
        // Given

        // When
        $this->client->request(Request::METHOD_GET, '/api/phones');

        // Then
        $response = $this->client->getResponse()->getContent();
        static::assertResponseStatusCodeSame(401);
        static::assertJson($response);
    }

    /**
     * Check if not authenticated person get an Unauthorized error when try to access to phones details
     * @test
     */
    public function notAuthenticatedCannotSeePhonesDetails(): void
    {
        // Given
        $phone = $this->getPhone();

        // When
        $this->client->request(Request::METHOD_GET, sprintf('/api/phones/%s', $phone->getId()));

        // Then
        $response = $this->client->getResponse()->getContent();
        static::assertResponseStatusCodeSame(401);
        static::assertJson($response);
    }

    /**
     * Check if customer get a Not Found response if id from phone does not exist
     * @test
     */
    public function customerGetNotFoundResponseIfIdOfPhoneDoesNotExist(): void
    {
        // Given
        $this->addCustomer();
        $jwt = $this->getJWT();

        // When
        $this->client->request(Request::METHOD_GET, '/api/phones/phone-does-not-exist', server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);

        // Then
        $response = $this->client->getResponse()->getContent();
        static::assertResponseStatusCodeSame(404);
        static::assertJson($response);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    private function getPhone(): Phone
    {
        /** @var PhoneRepository $phoneRepository */
        $phoneRepository = $this->container->get(PhoneRepository::class);

        return $phoneRepository->createQueryBuilder('p')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}