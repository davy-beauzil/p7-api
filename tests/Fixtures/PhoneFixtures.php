<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use App\Tests\AbstractWebTestCase;

class PhoneFixtures
{
    public function __construct(
        private PhoneRepository $phoneRepository,
    ) {
    }

    public const BOOLEANS = [true, false];
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

    /**
     * @throws \Exception
     */
    public function addPhone(): Phone
    {
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
            ->setScreenSize(random_int(50, 67) / 10)
            ->setBluetooth($this->getItem(self::BOOLEANS))
            ->setWifi($this->getItem(self::BOOLEANS))
            ->setPriceExclTax(random_int(200, 1400));

        $this->phoneRepository->add($phone, true);
        return $phone;
    }

    private function getItem(array $datas): mixed
    {
        return $datas[array_rand($datas)];
    }
}
