<?php

declare(strict_types=1);

namespace App\Validator\ChannelConstraint;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ChannelUniqueCountriesValidator extends ConstraintValidator
{
    private RepositoryInterface $channelRepository;

    public function __construct(RepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ChannelUniqueCountries) {
            throw new UnexpectedTypeException($constraint, ChannelUniqueCountries::class);
        }

        if (!$value instanceof ChannelInterface) {
            return;
        }

        $otherChannels = array_filter($this->channelRepository->findAll(), static function ($channel) use ($value) {
            return $channel->getCode() !== $value->getCode();
        });

        $duplicateCountries = [];
        foreach ($value->getCountries() as $country) {
            /** @var ChannelInterface $otherChannel */
            foreach ($otherChannels as $otherChannel) {
                if (in_array($country, $otherChannel->getCountries()->toArray(), true)) {
                    $duplicateCountries[] = $country;
                }
            }
        }

        if (count($duplicateCountries) > 0) {
            $this->context->buildViolation($constraint->message)
                ->atPath('countries')
                ->setParameter('%countries%', implode(', ', $duplicateCountries))
                ->addViolation();
        }
    }
}
