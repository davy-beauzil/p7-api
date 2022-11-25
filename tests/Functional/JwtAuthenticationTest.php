<?php

namespace App\Tests\Functional;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Tests\Fixtures\CustomerFixtures;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class JwtAuthenticationTest extends AbstractAppCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @throws JWTDecodeFailureException
     * @throws \Exception
     */
    public function goodCredentialsOfferJWT(): void
    {
        // given
        $this->getCustomerFixtures()->addCustomer();
        $body = sprintf('{"username": "%s", "password": "%s"}', CustomerFixtures::EMAIL, CustomerFixtures::PASSWORD);
        /** @var JWTEncoderInterface $encoder */
        $encoder = $this->container->get(JWTEncoderInterface::class);

        // when
        $this->client->request(Request::METHOD_POST, '/api/login_check', content: $body);

        // then
        static::assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('token', $response);
        $payload = $encoder->decode($response['token']);
        static::assertJson($this->client->getResponse()->getContent());
        static::assertSame('Random Customer', $payload['name']);
        static::assertSame('test@test.fr', $payload['username']);
        static::assertArrayNotHasKey('password', $payload);
        static::assertGreaterThan(time(), (int)$payload['exp']);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function badCredentialsReturn401(): void
    {
        // given
        $body = sprintf('{"username": "%s", "password": "%s"}', 'bad@email.fr', 'bad-password');

        // when
        $this->client->request(Request::METHOD_POST, '/api/login_check', content: $body);
        $response = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // then
        static::assertResponseStatusCodeSame(401);
        static::assertSame('Invalid credentials.', $response['message']);
    }
}
