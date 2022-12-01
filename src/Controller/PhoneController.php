<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api')]
class PhoneController extends BaseController
{
    public function __construct(
        private readonly PhoneRepository        $phoneRepository,
        private readonly RouterInterface        $router,
    ) {
        parent::__construct();
    }

    /**
     * @throws ExceptionInterface|InvalidArgumentException
     */
    #[Route('/phones', name: 'phones_collection', methods: Request::METHOD_GET)]
    public function getPhonesCollection(Request $request): JsonResponse
    {
        $queryParameters = $this->getQueryParameters($request);
        $phones = $this->phoneRepository->findWithPagination($queryParameters);
        $phones = $this->serializer->normalize($phones, 'json', [AbstractNormalizer::GROUPS => ['get:collection']]);
        return $this->createJSON($phones, queryParameters: $queryParameters);
    }

    /**
     * @throws ExceptionInterface|InvalidArgumentException
     */
    #[Route('/phones/{id}', name: 'phones_details', methods: Request::METHOD_GET)]
    public function getPhonesDetails(string $id): JsonResponse
    {
        try {
            $phone = $this->phoneRepository->findOneById($id);
        } catch (ResourceNotFoundException) {
            return $this->createNotFoundResponse();
        }

        /** @var array<array-key, mixed> $response */
        $phone = $this->serializer->normalize($phone, 'json', [AbstractNormalizer::GROUPS => 'get:item']);
        $links = [
            'list' => $this->router->generate('phones_collection')
        ];
        return $this->createJSON($phone, $links);
    }
}
