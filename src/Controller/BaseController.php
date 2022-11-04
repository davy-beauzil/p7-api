<?php

namespace App\Controller;

use App\Dto\QueryParameters;
use App\Entity\Customer;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class BaseController extends AbstractController
{
    public Serializer $serializer;

    public function __construct()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $normalizers = [
            new DateTimeNormalizer(),
            new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter)
        ];
        $this->serializer = new Serializer($normalizers, []);
    }

    /**
     * @param Request $request
     * @return QueryParameters
     * @throws ExceptionInterface
     */
    public function getQueryParameters(Request $request): QueryParameters
    {
        /** @var QueryParameters $queryParameters */
        $queryParameters = $this->serializer->denormalize($request->query->all(), QueryParameters::class);
        return $queryParameters;
    }

    private function createResponse(string $message, int $code): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
            'code' => $code
        ], $code);
    }

    public function createNoContentResponse(string $message = 'No Content'): JsonResponse
    {
        return $this->createResponse($message, 204);
    }

    public function createNotFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->createResponse($message, 404);
    }

    public function createAccessDeniedResponse(string $message = 'Access denied'): JsonResponse
    {
        return $this->createResponse($message, 401);
    }

    public function denyAccessIfNotCustomer(): ?Response
    {
        if (!$this->getUser() instanceof Customer) {
            return $this->createAccessDeniedResponse();
        }
        return null;
    }
}
