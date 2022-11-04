<?php

namespace App\EventListener;

use App\Entity\Customer;
use App\Entity\Phone;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\Event\LifecycleEventArgs;

class FillEntities
{
    final public const ENTITIES_SUPPORTED = [
        Customer::class,
        Phone::class,
        User::class,
    ];

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (in_array($entity::class, self::ENTITIES_SUPPORTED)) {
            /** @var Customer $entity */
            $entity->setId(bin2hex(random_bytes(64)));
            $entity->setCreatedAt(new DateTimeImmutable());
            $entity->setUpdatedAt(new DateTimeImmutable());
        }
    }
}
