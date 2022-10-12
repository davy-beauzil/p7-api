<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
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
        private readonly TagAwareCacheInterface $cache,
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
        $cacheId = sprintf('getPhonesCollection-%d-%d', $queryParameters->page, $queryParameters->per_page);
        $repository = $this->phoneRepository;

        $phones = $this->cache->get($cacheId, function (ItemInterface $item) use ($repository, $queryParameters) {
            $item->tag('phonesCache');
            $item->expiresAfter(600);
            return $repository->findWithPagination($queryParameters);
        });
        $phones = $this->serializer->normalize($phones, 'json', [AbstractNormalizer::GROUPS => ['get:collection']]);

        return new JsonResponse($phones);
    }

    /**
     * @throws ExceptionInterface|InvalidArgumentException
     */
    #[Route('/phones/{id}', name: 'phones_details', methods: Request::METHOD_GET)]
    public function getPhonesDetails(string $id): JsonResponse
    {
        $cacheId = sprintf('getPhonesDetails-%s', $id);
        $repository = $this->phoneRepository;
        $phone = $this->cache->get($cacheId, function (ItemInterface $item) use ($repository, $id) {
            $item->tag('phonesCache');
            $item->expiresAfter(600);
            return $repository->findOneBy(['id' => $id]);
        });

        if (!$phone instanceof Phone) {
            return $this->createNotFoundResponse();
        }

        /** @var array<array-key, mixed> $phone */
        $phone = $this->serializer->normalize($phone, 'json', [AbstractNormalizer::GROUPS => 'get:item']);
        $phone['_links'] = [
            'list' => $this->router->generate('phones_collection')
        ];

        return new JsonResponse($phone);
    }
}
