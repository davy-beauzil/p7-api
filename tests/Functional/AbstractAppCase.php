<?php

namespace App\Tests\Functional;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractAppCase extends WebTestCase
{
    const EMAIL = 'test@test.fr';
    const PASSWORD = 'test@1234';
    protected KernelBrowser $client;
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        $this->client = self::createClient([], [
            'HTTPS' => 'on',
            'CONTENT_TYPE' => 'application/json'
        ]);
        $this->truncateEntities();
        $this->container = static::getContainer();
    }

    /**
     * @throws Exception
     */
    protected function addCustomer(string $email = self::EMAIL, string $password = self::PASSWORD): void
    {
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $this->container->get(UserPasswordHasherInterface::class);

        /** @var CustomerRepository $customerRepository */
        $customerRepository = $this->container->get(CustomerRepository::class);

        $customer = new Customer();
        $customer->setEmail($email)
            ->setPassword($hasher->hashPassword($customer, $password))
            ->setRoles(['ROLE_USER'])
            ->setPhoneNumber('0612345678')
            ->setZipCode('99000')
            ->setSiret('XXX XXX XXX')
            ->setName('Test company')
            ->setAddress('test')
            ->setCity('test');

        $customerRepository->add($customer, true);
    }

    protected function getJWT(string $email = self::EMAIL, string $password = self::PASSWORD): ?string
    {
        $body = sprintf('{"username": "%s", "password": "%s"}', $email, $password);
        $this->client->request(Request::METHOD_POST, '/api/login_check', content: $body);
        $response = $this->client->getResponse();
        return json_decode($response->getContent(), true)['token'] ?? null;
    }

    private function truncateEntities(): void
    {
        $entities = [
            Customer::class,
        ];
        self::bootKernel();
        $connection = $this->getEntityManager()
            ->getConnection()
        ;
        $databasePlatform = $connection->getDatabasePlatform();

        foreach ($entities as $entity) {
            $query = $databasePlatform->getTruncateTableSQL(
                $this->getEntityManager()
                    ->getClassMetadata($entity)
                    ->getTableName(),
                true,
            );
            $connection->executeUpdate($query);
        }
    }

    private function getEntityManager(): EntityManager
    {
        return self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ;
    }
}