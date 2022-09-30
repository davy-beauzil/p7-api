<?php

namespace App\EventListener;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener
{
    public function __construct(
        private readonly CustomerRepository $repository,
    ) {
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        $email = $payload['username'];

        /** @var ?Customer $customer */
        $customer = $this->repository->findOneBy(['email' => $email]);

        if ($customer !== null) {
            $payload['name'] = $customer->getName();
        }

        $event->setData($payload);
    }
}
