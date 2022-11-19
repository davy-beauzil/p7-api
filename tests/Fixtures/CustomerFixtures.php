<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Customer;
use App\Entity\Phone;
use App\Repository\CustomerRepository;
use App\Repository\PhoneRepository;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CustomerFixtures
{
    public const EMAIL = 'test@test.fr';
    public const PASSWORD = 'test@1234';

    public function __construct(
        private CustomerRepository $customerRepository,
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    public function addCustomer(
        string $email = self::EMAIL,
        string $name = 'Random Customer',
        string $siret = '000 000 000',
        string $address = '10 chemin des moulins',
        string $city = 'Paris',
        string $zipCode = '75000',
        string $phoneNumber = '06 12 34 56 78',
        array $roles = ['ROLE_USER'],
    ): Customer {
        $customer = new Customer();
        $customer->setEmail($email)
            ->setPassword($this->hasher->hashPassword($customer, self::PASSWORD))
            ->setName($name)
            ->setSiret($siret)
            ->setAddress($address)
            ->setCity($city)
            ->setZipCode($zipCode)
            ->setPhoneNumber($phoneNumber)
            ->setRoles($roles);

        $this->customerRepository->add($customer, true);
        return $customer;
    }
}
