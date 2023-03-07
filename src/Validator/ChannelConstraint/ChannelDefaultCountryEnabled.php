<?php

declare(strict_types=1);

namespace App\Validator\ChannelConstraint;

use Symfony\Component\Validator\Constraint;

final class ChannelDefaultCountryEnabled extends Constraint
{
    public string $message = 'app.channel.default_country.enabled';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
