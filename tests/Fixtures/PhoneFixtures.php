<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use App\Tests\AbstractWebTestCase;

class PhoneFixtures extends AbstractWebTestCase
{
    public function addPhone(
        string $brand,
        string $model,
        string $operatingSystem,
        string $processor,
        int $storage,
        int $ram,
        int $screenWidth,
        int $screenHeight,
        float $screenSize,
        bool $bluetooth,
        bool $wifi,
        float $priceExclTax
    ): Phone {
        $phone = new Phone();
        $phone->setBrand($brand)
            ->setModel($model)
            ->setOperatingSystem($operatingSystem)
            ->setProcessor($processor)
            ->setStorage($storage)
            ->setRam($ram)
            ->setScreenWidth($screenWidth)
            ->setScreenHeight($screenHeight)
            ->setScreenSize($screenSize)
            ->setBluetooth($bluetooth)
            ->setWifi($wifi)
            ->setPriceExclTax($priceExclTax);

        /** @var PhoneRepository $phoneReposiroty */
        $phoneReposiroty = $this->getEntityManager()->getRepository(Phone::class);
        $phoneReposiroty->add($phone, true);
        return $phone;
    }
}
