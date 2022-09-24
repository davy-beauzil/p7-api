<?php

namespace App\EventListener;

use App\Entity\Customer;
use App\Entity\Phone;
use DateTimeImmutable;
use Doctrine\ORM\Event\LifecycleEventArgs;

class FillEntities
{
    final const ENTITIES_SUPPORTED = [
        Customer::class,
        Phone::class,
    ];

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if(in_array($entity::class, self::ENTITIES_SUPPORTED)){
            /** @var Customer $entity */
            $entity->setId(bin2hex(random_bytes(64)));
            $entity->setCreatedAt(new DateTimeImmutable());
            $entity->setUpdatedAt(new DateTimeImmutable());
        }
    }
}