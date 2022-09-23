<?php

namespace App\Tests\Functional;

use App\Entity\Customer;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractAppCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient([], [
            'HTTPS' => 'on',
            'CONTENT_TYPE' => 'application/json'
        ]);
        $this->truncateEntities();
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