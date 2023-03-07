<?php

declare(strict_types=1);

namespace App\Faker\Provider\Customer;

use Faker\Provider\Base as BaseProvider;

final class RecommendationProvider extends BaseProvider
{
    private const RECOMMENDAITON_PROVIDER = [
        'by_a_friend',
        'instagram',
        'by_a_colleague',
        'internet_magazine_media',
        'we_contact_you',
        'pinterest',
        'facebook',
        null
    ];


    public function recommendation(): ?string
    {
        return self::randomElement(self::RECOMMENDAITON_PROVIDER);
    }
}
