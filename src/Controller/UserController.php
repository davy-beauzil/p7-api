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
        $this->denyAccessIfNotCustomer();
        /** @var Customer $customer */
        $customer = $this->getUser();

        $queryParameters = $this->getQueryParameters($request);
        $users = $this->userRepository->findUsersWithPaginationByCustomer($queryParameters, $customer);

        $users = $this->serializer->normalize($users, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['customer']
        ]);

        return new JsonResponse($users);
    }

    /**
     * @throws ExceptionInterface|InvalidArgumentException
     */
    #[Route('/users/{id}', name: 'get_users_item', methods: [Request::METHOD_GET])]
    public function getUsersItem(string $id, Request $request): JsonResponse
    {
        $this->denyAccessIfNotCustomer();
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
        $user['_links'] = [
            'collection' => $this->router->generate('get_users_collection')
        ];

        return new JsonResponse($user);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/users', name: 'post_users', methods: [Request::METHOD_POST])]
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $this->denyAccessIfNotCustomer();
        /** @var Customer $customer */
        $customer = $this->getUser();

        /** @var User $user */
        $user = $this->serializer->denormalize($request->request->all(), User::class);
        $user->setCustomer($customer);

        //        dd($user);
        if (count($validator->validate($user)) <= 0) {
            $this->userRepository->add($user, true);
        } else {
            return $this->createBadRequestResponse();
        }
        return new JsonResponse(
            [
                'message' => 'Created',
                'code' => 201,
                '_links' => [
                    'item' => $this->router->generate('get_users_item', ['id' => $user->getId()])
                ]
            ],
            201,
            [
                'Location' => $this->generateUrl('get_users_item', ['id' => $user->getId()])
            ]
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/users/{id}', name: 'delete_users', methods: [Request::METHOD_DELETE])]
    public function delete(string $id, Request $request): JsonResponse
    {
        $this->denyAccessIfNotCustomer();
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
