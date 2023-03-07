<?php

declare(strict_types=1);

namespace App\Transformer\Locale;

use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;

class LocaleModelTransformer implements DataTransformerInterface
{
    private RepositoryInterface $localeRepository;

    public function __construct(RepositoryInterface $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * @param string|null $codeToLocale
     * @return LocaleInterface|null
     */
    public function transform($codeToLocale): ?LocaleInterface
    {
        if (null === $codeToLocale) {
            return null;
        }

        return $this->localeRepository->findOneBy(['code' => $codeToLocale]);
    }

    /**
     * @param LocaleInterface|null $localeToCode
     * @return string|null
     */
    public function reverseTransform($localeToCode): ?string
    {
        if (null === $localeToCode) {
            return null;
        }

        return $localeToCode->getCode();
    }
}
