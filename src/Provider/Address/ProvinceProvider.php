<?php

declare(strict_types=1);

namespace App\Provider\Address;

use App\Entity\Addressing\Country;
use App\Entity\Addressing\Province;
use App\Exception\Address\ProvinceNotFoundException;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use function mb_strlen;
use function mb_substr;
use function str_pad;
use const STR_PAD_LEFT;

class ProvinceProvider
{
    private RepositoryInterface $countryRepository;

    public function __construct(RepositoryInterface $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function getProvinceFromPostCode(string $postCode, string $countryCode): Province
    {
        $departmentCode = $this->getDepartmentCodeFromPostCode($postCode);
        /** @var Country|null $country */
        $country = $this->countryRepository->findOneBy(['code' => $countryCode]);
        if (null === $country) {
            throw new ProvinceNotFoundException();
        }

        /** @var Province[] $provinces */
        $provinces = $country->getProvinces();

        // define default province .
        $defaultProvince = null;

        foreach ($provinces as $province) {
            $provinceDepartmentCode = $this->getDepartmentCodeFromProvinceCode($province->getCode());
            // save default province (code 00)
            if ($provinceDepartmentCode === "00") {
                $defaultProvince = $province;
            }

            if ($departmentCode === $provinceDepartmentCode) {
                return $province;
            }
        }
        // if default province exists return it.
        if (null !== $defaultProvince) {
            return $defaultProvince;
        }

        // no match and no default : failed.
        throw new ProvinceNotFoundException();
    }

    private function getDepartmentCodeFromPostCode(string $postCode): string
    {
        // If the number is only 4 length long, we assume it is `1000 --> 9000` postCode
        if (4 === mb_strlen($postCode)) {
            return str_pad(mb_substr($postCode, 0, 1), 2, '0', STR_PAD_LEFT);
        }

        // Otherwise, it can be any 01000 --> 99000
        return mb_substr($postCode, 0, 2);
    }

    private function getDepartmentCodeFromProvinceCode(string $provinceCode): string
    {
        return substr($provinceCode, -2);
    }
}
