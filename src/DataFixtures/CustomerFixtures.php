<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Faker\Provider\fr_FR\Company;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CustomerFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $customer = new Customer();
        $customer->setName($faker->company())
            ->setSiret('000 000 000')
            ->setAddress($faker->address())
            ->setCity($faker->city())
            ->setZipCode($faker->postcode())
            ->setPhoneNumber($faker->phoneNumber())
            ->setRoles(['ROLE_USER'])
            ->setEmail('test@test.fr')
            ->setPassword($this->hasher->hashPassword($customer, 'test@1234'));
        $manager->persist($customer);

        for ($i = 1; $i < 10; $i++) {
            $customer = new Customer();
            $customer->setName($faker->company())
                ->setSiret('000 000 000')
                ->setAddress($faker->address())
                ->setCity($faker->city())
                ->setZipCode($faker->postcode())
                ->setPhoneNumber($faker->phoneNumber())
                ->setRoles(['ROLE_USER'])
                ->setEmail($faker->email())
                ->setPassword($this->hasher->hashPassword($customer, $faker->password()));
            $manager->persist($customer);
        }

        $manager->flush();
    }
}
