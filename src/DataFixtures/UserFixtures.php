<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        /** @var Customer[] $customers */
        $customers = $manager->getRepository(Customer::class)->findAll();

        foreach ($customers as $customer) {
            for ($i = 1; $i < 5; $i++) {
                $user = new User();
                $user->setFirstname($faker->firstName())
                    ->setLastname($faker->lastName())
                    ->setEmail($faker->email())
                    ->setPhoneNumber($faker->phoneNumber())
                    ->setCustomer($customer);
                $manager->persist($user);
            }
        }
        $manager->flush();
    }
}
