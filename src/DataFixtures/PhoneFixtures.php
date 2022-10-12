<?php

namespace App\DataFixtures;

use App\Entity\Phone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PhoneFixtures extends Fixture
{
    public const STORAGES = [32, 64, 128, 256, 512, 1024];
    public const MODELS = ['Mini', 'Max', 'Pro Max', 'Galaxy'];
    public const PROCESSORS = ['Snapdragon', 'Exynos', 'HiSilicon Kirin'];
    public const BRAND = ['Samsung', 'Apple', 'Huawei', 'Xiaomi', 'Oppo'];
    public const RAM = [2, 3, 4, 6, 8, 12];
    public const SCREEN_DIMENSIONS = [
        ['width' => 480, 'height' => 848],
        ['width' => 720, 'height' => 1280],
        ['width' => 1080, 'height' => 1920],
        ['width' => 1520, 'height' => 2704],
        ['width' => 2160, 'height' => 3840],
    ];
    public const OS = ['Android', 'IOS'];

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 20; $i++) {
            $phone = new Phone();
            $dimensions = $this->getItem(self::SCREEN_DIMENSIONS);
            $phone->setBrand($this->getItem(self::BRAND))
                ->setModel($this->getItem(self::MODELS))
                ->setOperatingSystem($this->getItem(self::OS))
                ->setProcessor($this->getItem(self::PROCESSORS))
                ->setStorage($this->getItem(self::STORAGES))
                ->setRam($this->getItem(self::RAM))
                ->setScreenWidth($dimensions['width'])
                ->setScreenHeight($dimensions['height'])
                ->setScreenSize($faker->randomFloat(2, 5, 6.7))
                ->setBluetooth($faker->boolean())
                ->setWifi($faker->boolean())
                ->setPriceExclTax($faker->randomFloat(2, 200, 1400));
            $manager->persist($phone);
        }

        $manager->flush();
    }

    private function getItem(array $datas): mixed
    {
        return $datas[array_rand($datas)];
    }
}
