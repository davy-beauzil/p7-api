<?php

namespace App\Tests\Functional;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTestCase;
use App\Tests\Fixtures\CustomerFixtures;
use App\Tests\Fixtures\UserFixtures;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractAppCase extends AbstractWebTestCase
{
    protected KernelBrowser $client;
    protected ContainerInterface $container;
    private ?CustomerFixtures $customerFixtures = null;
    private ?UserFixtures $userFixtures = null;

    /**
     * @throws \JsonException
     */
    public function getJWT(Customer $customer): string
    {
        $body = sprintf('{"username": "%s", "password": "%s"}', $customer->getEmail(), CustomerFixtures::PASSWORD);
        $this->client->request(Request::METHOD_POST, '/api/login_check', content: $body);
        $response = $this->client->getResponse();
        $arrayResponse = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('token', $arrayResponse)) {
            return $arrayResponse['token'];
        }
        throw new \RuntimeException('Aucun JWT n\'a été reçu. Vérifiez que l\'utilisateur avec lequel vous essayez de vous connecter existe bien.');
    }

    public function getUserFixtures(): UserFixtures
    {
        if (!$this->userFixtures) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->getEntityManager()->getRepository(User::class);
            $this->userFixtures = new UserFixtures($userRepository);
        }
        return $this->userFixtures;
    }

    public function getCustomerFixtures(): CustomerFixtures
    {
        if (!$this->customerFixtures instanceof CustomerFixtures) {
            /** @var CustomerRepository $customerRepository */
            $customerRepository = $this->getEntityManager()->getRepository(Customer::class);
            /** @var UserPasswordHasherInterface $hasher */
            $hasher = $this->container->get(UserPasswordHasherInterface::class);
            $this->customerFixtures = new CustomerFixtures($customerRepository, $hasher);
        }
        return $this->customerFixtures;
    }
}
