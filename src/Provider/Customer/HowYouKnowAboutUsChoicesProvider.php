<?php

declare(strict_types=1);

namespace App\Provider\Customer;

class HowYouKnowAboutUsChoicesProvider
{
    public const WE_CONTACT_YOU = 'we_contact_you';
    public const INSTAGRAM = 'instagram';
    public const PINTEREST = 'pinterest';
    public const YOUTUBE = 'youtube';
    public const FACEBOOK = 'facebook';
    public const BY_A_FRIEND = 'by_a_friend';
    public const BY_A_COLLEAGUE = 'by_a_colleague';
    public const BY_A_RELATIVE = 'by_a_relative';
    public const BY_MY_ARCHITECT = 'by_my_architect';
    public const BY_MY_CONTRACTOR = 'by_my_contractor';
    public const TV_REPLAY = 'tv_replay';
    public const METRO_BUS = 'metro_bus';
    public const BLOG_MAGAZINE = 'blog_magazine';
    public const PARTNERSHIP = 'partnership';
    public const INTERNET_MAGAZINE_MEDIA = 'internet_magazine_media';

    public const CHOICES_HOW_YOU_KNOW_US = [
        self::INSTAGRAM,
        self::PINTEREST,
        self::YOUTUBE,
        self::FACEBOOK,
        self::BY_A_RELATIVE,
        self::BY_MY_ARCHITECT,
        self::BY_MY_CONTRACTOR,
        self::TV_REPLAY,
        self::METRO_BUS,
        self::BLOG_MAGAZINE,
        self::PARTNERSHIP,
    ];

    public function getChoices(): array
    {
        return self::CHOICES_HOW_YOU_KNOW_US;
    }
}
