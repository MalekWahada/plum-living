<?php

declare(strict_types=1);

namespace App\Validator\ChannelConstraint;

use Symfony\Component\Validator\Constraint;

class ChannelUniqueCountries extends Constraint
{
    public string $message = 'app.channel.unique_countries';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
