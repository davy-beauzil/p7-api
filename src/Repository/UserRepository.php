<?php

namespace App\Repository;

use App\Dto\QueryParameters;
use App\Entity\Customer;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private TagAwareCacheInterface $cache
    ) {
        parent::__construct($registry, User::class);
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws InvalidArgumentException
     * @return User[]
     */
    public function findUsersWithPaginationByCustomer(QueryParameters $parameters, Customer $customer): array
    {
        $cacheId = sprintf('findUsersWithPaginationByCustomer-%s-%d-%d', $customer->getId(), $parameters->page, $parameters->per_page);

        /** @var User[] $users */
        $users = $this->cache->get($cacheId, function (ItemInterface $item) use ($parameters, $customer) {
            $item->tag('usersCache');
            $item->expiresAfter(600);

            return $this->createQueryBuilder('u')
                ->where('u.customer = :customer')
                ->setParameter('customer', $customer)
                ->setFirstResult(($parameters->page - 1) * $parameters->per_page)
                ->setMaxResults($parameters->per_page)
                ->orderBy('u.createdAt', Criteria::DESC)
                ->getQuery()
                ->getResult();
        });

        return $users;
    }


    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     * @throws InvalidArgumentException
     */
    public function findUserWithIdAndCustomer(string $id, Customer $customer, bool $useCache = true): User
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.customer = :customer')
            ->andWhere('u.id = :id')
            ->setParameters(['customer' => $customer, 'id' => $id])
            ->getQuery();

        if ($useCache) {
            $cacheId = sprintf('findUserWithIdAndCustomer-%s-%s', $id, $customer->getId());
            $user = $this->cache->get($cacheId, function (ItemInterface $item) use ($query) {
                $item->tag('usersCache');
                $item->expiresAfter(600);
                return $query->getSingleResult();
            });
        } else {
            $user = $query->getSingleResult();
        }

        if (!$user instanceof User) {
            throw new ResourceNotFoundException();
        }

        return $user;
    }
}
