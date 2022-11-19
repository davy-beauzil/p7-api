<?php

namespace Functional;

use App\Dto\QueryParameters;
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
    /** @var Phone[] $phones */
    private array $phones = [];

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        for ($i = 0; $i < 25; $i++) {
            $this->phones[] = $this->getPhoneFixtures()->addPhone();
        }
    }

    /**
     * Check if a customer logged in can access to phones list
     * @throws Exception
     * @test
     */
    public function customerCanSeePhonesList(): void
    {
        // Given
        $customer = $this->getCustomerFixtures()->addCustomer();
        $jwt = $this->getJWT($customer);

        // When
        $this->client->request(Request::METHOD_GET, '/api/phones', server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);

        // Then
        $response = $this->client->getResponse()->getContent();
        static::assertResponseIsSuccessful();
        static::assertJson($response);
        static::assertCount(QueryParameters::PER_PAGE, json_decode($response, true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Check if customer logged in can access to phone details
     * @test
     * @throws Exception
     */
    public function customerCanSeePhonesDetails(): void
    {
        // Given
        $customer = $this->getCustomerFixtures()->addCustomer();
        $jwt = $this->getJWT($customer);
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
     * @throws Exception
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
     * @throws \JsonException
     */
    public function customerGetNotFoundResponseIfIdOfPhoneDoesNotExist(): void
    {
        // Given
        $customer = $this->getCustomerFixtures()->addCustomer();
        $jwt = $this->getJWT($customer);

        // When
        $this->client->request(Request::METHOD_GET, '/api/phones/phone-does-not-exist', server: ['HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwt)]);

        // Then
        $response = $this->client->getResponse()->getContent();
        static::assertResponseStatusCodeSame(404);
        static::assertJson($response);
    }


    /**
     * @throws Exception
     */
    private function getPhone(): Phone
    {
        if (count($this->phones) <= 0) {
            for ($i = 0; $i < 25; $i++) {
                $this->phones[] = $this->getPhoneFixtures()->addPhone();
            }
        }
        $phones = array_values($this->phones);
        return array_shift($phones);
    }
}
