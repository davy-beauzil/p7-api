<?php

namespace App\Tests\Functional;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
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
        $this->addCustomer();
        $body = sprintf('{"username": "%s", "password": "%s"}', AbstractAppCase::EMAIL, AbstractAppCase::PASSWORD);
        /** @var JWTEncoderInterface $encoder */
        $encoder = $this->container->get(JWTEncoderInterface::class);

        // when
        $this->client->request(Request::METHOD_POST, '/api/login_check', content: $body);
        $response = $this->client->getResponse();
        $token = json_decode($response->getContent(), true)['token'];
        $payload = $encoder->decode($token);

        // then
        static::assertResponseIsSuccessful();
        static::assertJson($response->getContent());
        static::assertSame('Test company', $payload['name']);
        static::assertSame('test@test.fr', $payload['username']);
        static::assertArrayNotHasKey('password', $payload);
        static::assertGreaterThan(time(), intval($payload['exp']));
    }

    /**
     * @test
     */
    public function badCredentialsReturn401(): void
    {
        // given
        $body = sprintf('{"username": "%s", "password": "%s"}', 'bad@email.fr', 'bad-password');

        // when
        $this->client->request(Request::METHOD_POST, '/api/login_check', content: $body);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        // then
        static::assertResponseStatusCodeSame(401);
        static::assertSame('Invalid credentials.', $response['message']);
    }
}