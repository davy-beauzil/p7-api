<?php

namespace App\Controller;

use App\Dto\QueryParameters;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
}
