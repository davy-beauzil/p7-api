<?php

namespace App\Tests;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AbstractWebTestCase extends WebTestCase
{
    public const EMAIL = 'test@test.fr';
    public const PASSWORD = 'test@1234';
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

    private function truncateEntities(): void
    {
        $purger = new ORMPurger($this->getEntityManager());
        $purger->purge();
    }

    protected function getEntityManager(): EntityManager
    {
        return self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
    }
}
