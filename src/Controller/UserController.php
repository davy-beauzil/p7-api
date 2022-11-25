<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Customer;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api')]
class UserController extends BaseController
{
    public function __construct(
        private readonly UserRepository         $userRepository,
        private readonly TagAwareCacheInterface $cache,
        private readonly RouterInterface        $router,
    ) {
        parent::__construct();
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    #[Route('/users', name: 'get_users_collection', methods: [Request::METHOD_GET])]
    public function getUsersCollection(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $this->getUser();

        $queryParameters = $this->getQueryParameters($request);
        $users = $this->userRepository->findUsersWithPaginationByCustomer($queryParameters, $customer);

        /** @var array<array-key, mixed> $users */
        $users = $this->serializer->normalize($users, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['customer']
        ]);
        return $this->createJson($users, queryParameters: $queryParameters);
    }

    /**
     * @throws ExceptionInterface|InvalidArgumentException
     */
    #[Route('/users/{id}', name: 'get_users_item', methods: [Request::METHOD_GET])]
    public function getUsersItem(string $id, Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $this->getUser();

        try {
            $user = $this->userRepository->findUserWithIdAndCustomer($id, $customer);
        } catch (\Exception $e) {
            return $this->createNotFoundResponse();
        }

        /** @var array<array-key, mixed> $user */
        $user = $this->serializer->normalize($user, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['customer']
        ]);
        $links = [
            'collection' => $this->router->generate('get_users_collection'),
            'delete' => $this->router->generate('delete_users', ['id' => $id])
        ];

        return $this->createJSON($user, $links);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/users', name: 'post_users', methods: [Request::METHOD_POST])]
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $this->getUser();

        /** @var User $user */
        $user = $this->serializer->denormalize($request->request->all(), User::class);
        $user->setCustomer($customer);

        if (count($validator->validate($user)) <= 0) {
            $this->userRepository->add($user, true);
        } else {
            return $this->createBadRequestResponse();
        }

        $link = $this->router->generate('get_users_item', ['id' => $user->getId()]);
        return $this->createCreatedResponse($link);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/users/{id}', name: 'delete_users', methods: [Request::METHOD_DELETE])]
    public function delete(string $id, Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $this->getUser();

        try {
            $user = $this->userRepository->findUserWithIdAndCustomer($id, $customer, false);
            $this->userRepository->remove($user, true);
            $this->cache->invalidateTags(['usersCache']);
            return $this->createNoContentResponse();
        } catch (\Exception $e) {
            return $this->createNotFoundResponse();
        }
    }
}
