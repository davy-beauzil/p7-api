<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Customer;
use App\Entity\Phone;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\PhoneRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function addUser(
        Customer $customer,
        string $firstname = 'Jean',
        string $lastname = 'Moulin',
        string $email = 'jean.moulin@test.fr',
        string $phoneNumber = '06 12 34 56 78',
    ): User {
        $user = new User();
        $user->setFirstname($firstname)
            ->setLastname($lastname)
            ->setEmail($email)
            ->setPhoneNumber($phoneNumber)
            ->setCustomer($customer);
        $customer->addUser($user);
        $this->userRepository->add($user, true);

        return $user;
    }
}
