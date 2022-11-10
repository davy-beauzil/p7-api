<?php

namespace App\Repository;

use App\Dto\QueryParameters;
use App\Entity\Phone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @extends ServiceEntityRepository<Phone>
 *
 * @method Phone|null find($id, $lockMode = null, $lockVersion = null)
 * @method Phone|null findOneBy(array $criteria, array $orderBy = null)
 * @method Phone[]    findAll()
 * @method Phone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhoneRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                         $registry,
        private readonly TagAwareCacheInterface $cache,
    ) {
        parent::__construct($registry, Phone::class);
    }

    public function add(Phone $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Phone $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param QueryParameters $parameters
     * @return Phone[]
     * @throws InvalidArgumentException
     */
    public function findWithPagination(QueryParameters $parameters): array
    {
        $cacheId = sprintf('getPhonesCollection-%d-%d', $parameters->page, $parameters->per_page);

        /** @var Phone[] $phones */
        $phones = $this->cache->get($cacheId, function (ItemInterface $item) use ($parameters) {
            $item->tag('phonesCache');
            $item->expiresAfter(600);
            return $this->createQueryBuilder('p')
                ->setFirstResult(($parameters->page - 1) * $parameters->per_page)
                ->setMaxResults($parameters->per_page)
                ->orderBy('p.createdAt', Criteria::DESC)
                ->getQuery()
                ->getResult()
            ;
        });
        return $phones;
    }

    /**
     * @param string $id
     * @return Phone
     * @throws InvalidArgumentException
     */
    public function findOneById(string $id): Phone
    {
        $cacheId = sprintf('getPhonesDetails-%s', $id);
        return $this->cache->get($cacheId, function (ItemInterface $item) use ($id) {
            $item->tag('phonesCache');
            $item->expiresAfter(600);
            return $this->findOneBy(['id' => $id]);
        });
    }
}
