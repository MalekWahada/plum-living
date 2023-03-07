<?php

declare(strict_types=1);

namespace App\Provider\CMS\Chip;

use App\Entity\Locale\Locale;

class ChipProvider
{
    public const CHIP_TYPE_INSPIRATION = 'inspiration';
    public const CHIP_TYPE_HOME_TOUR = 'home_tour';
    public const CHIP_TYPE_HOME_PROJECT = 'home_project';
    public const CHIP_TYPE_TUTORIAL = 'tutorial';
    public const CHIP_TYPE_PRACTICE = 'practice';
    public const CHIP_TYPE_ADDRESS = 'address';
    public const CHIP_TYPE_TOOLBOX = 'toolbox';
    public const CHIP_TYPE_TUTO_IKEA = 'tuto_ikea';

    public const CHIP_ROOM_KITCHEN = 'kitchen';
    public const CHIP_ROOM_BEDROOM_DRESSING = 'bedroom_dressing';
    public const CHIP_ROOM_BATHROOM = 'bathroom';
    public const CHIP_ROOM_OTHER = 'other_rooms';
    public const CHIP_ROOM_ALL = 'all_rooms';

    public const CHIPS_THEME_KITCHEN_COLOR = 1;
    public const CHIPS_THEME_KITCHEN_STYLE = 2;
    public const CHIPS_THEME_KITCHEN_PRACTICAL = 3;
    public const CHIPS_THEME_BEDROOM_AND_DRESSING = 4;
    public const CHIPS_THEME_BATHROOM_OR_NOTHING = 5;
    public const CHIPS_THEME_ENTRANCE_FURNITURE = 6;
    public const CHIPS_THEME_DETAILS_CHANGE_ALL = 7;
    public const CHIPS_THEME_LOW_BUDGET_MAX_EFFECT = 8;
    public const CHIPS_THEME_ARCHITECT_SECRETS = 9;
    public const CHIPS_THEME_BEDROOM_AND_DRESSING_COLOR = 10;
    public const CHIPS_THEME_KIDS_KINGDOM = 11;
    public const CHIPS_THEME_PAINT_GAME = 12;
    public const CHIPS_THEME_PLAN_WITH_IKP = 13;

    public const CHIPS_THEMES = [
        Locale::DEFAULT_LOCALE_CODE => [
            'la-cuisine-en-couleurs' => self::CHIPS_THEME_KITCHEN_COLOR,
            'cuisine-toutes-nos-inspirations' => self::CHIPS_THEME_KITCHEN_STYLE,
            'cuisine-conseils-et-bonnes-adresses' => self::CHIPS_THEME_KITCHEN_PRACTICAL,
            'tout-sur-la-chambre-et-le-dressing' => self::CHIPS_THEME_BEDROOM_AND_DRESSING,
            'une-salle-de-bain-sinon-rien' => self::CHIPS_THEME_BATHROOM_OR_NOTHING,
            'je-veux-un-meuble-dentree-ou-de-salon' => self::CHIPS_THEME_ENTRANCE_FURNITURE,
            'les-details-qui-changent-tout' => self::CHIPS_THEME_DETAILS_CHANGE_ALL,
            'petit-budget-maxi-effet' => self::CHIPS_THEME_LOW_BUDGET_MAX_EFFECT,
            'secrets-d_archi' => self::CHIPS_THEME_ARCHITECT_SECRETS,
            'chambres-dressing-en-couleurs' => self::CHIPS_THEME_BEDROOM_AND_DRESSING_COLOR,
            'au-royaume-des-kids' => self::CHIPS_THEME_KIDS_KINGDOM,
            'jeux-de-peinture' => self::CHIPS_THEME_PAINT_GAME,
            'faire-mon-plan-avec-le-ikea-kitchen-planner' => self::CHIPS_THEME_PLAN_WITH_IKP,
        ],
        'nl' => [
            'de-keuken-in-kleur' => self::CHIPS_THEME_KITCHEN_COLOR,
            'al-onze-keukeninspiraties' => self::CHIPS_THEME_KITCHEN_STYLE,
            'keuken-advies-en-shopping-guide' => self::CHIPS_THEME_KITCHEN_PRACTICAL,
            'alles-over-de-slaapkamer-en-de-dressing' => self::CHIPS_THEME_BEDROOM_AND_DRESSING,
            'een-plum-badkamer-of-niets' => self::CHIPS_THEME_BATHROOM_OR_NOTHING,
            'ik-wil-een-meubel-voor-de-entree-of-woonkamer' => self::CHIPS_THEME_ENTRANCE_FURNITURE,
            'de-details-maken-het-verschil' => self::CHIPS_THEME_DETAILS_CHANGE_ALL,
            'minimaal-budget-maximaal-effect' => self::CHIPS_THEME_LOW_BUDGET_MAX_EFFECT,
            'architectonische-geheimen' => self::CHIPS_THEME_ARCHITECT_SECRETS,
            'slaapkamers-and-kleedkamers-in-kleur' => self::CHIPS_THEME_BEDROOM_AND_DRESSING_COLOR,
            'in-het-rijk-van-kinderen' => self::CHIPS_THEME_KIDS_KINGDOM,
            'speels-schilderen' => self::CHIPS_THEME_PAINT_GAME,
            'mijn-plan-maken-met-de-ikea-keukenplan-instructievideo-s' => self::CHIPS_THEME_PLAN_WITH_IKP,
        ],
        'en' => [
            'the-kitchen-in-colours' => self::CHIPS_THEME_KITCHEN_COLOR,
            'all-your-kitchen-inspiration' => self::CHIPS_THEME_KITCHEN_STYLE,
            'kitchen-advice-and-shopping-guide' => self::CHIPS_THEME_KITCHEN_PRACTICAL,
            'all-things-bedroom-and-wardrobe' => self::CHIPS_THEME_BEDROOM_AND_DRESSING,
            'a-plum-bathroom-or-nothing' => self::CHIPS_THEME_BATHROOM_OR_NOTHING,
            'i-want-a-piece-of-furniture-for-my-entrance-or-living-room' => self::CHIPS_THEME_ENTRANCE_FURNITURE,
            'the-details-that-change-everything' => self::CHIPS_THEME_DETAILS_CHANGE_ALL,
            'minimum-budget-maximum-effect' => self::CHIPS_THEME_LOW_BUDGET_MAX_EFFECT,
            'architectural-secrets' => self::CHIPS_THEME_ARCHITECT_SECRETS,
            'bedrooms-and-wardrobes-in-colour' => self::CHIPS_THEME_BEDROOM_AND_DRESSING_COLOR,
            'kids-kingdom' => self::CHIPS_THEME_KIDS_KINGDOM,
            'playful-painting' => self::CHIPS_THEME_PAINT_GAME,
            'create-your-plan-with-the-ikea-kitchen-planner-tuto-videos' => self::CHIPS_THEME_PLAN_WITH_IKP,
        ],
        'de' => [
            'farbenfrohe-kuche' => self::CHIPS_THEME_KITCHEN_COLOR,
            'alle-unsere-kücheninspirationen' => self::CHIPS_THEME_KITCHEN_STYLE,
            'kuche-tipps-und-shopping-guide' => self::CHIPS_THEME_KITCHEN_PRACTICAL,
            'alles-uuber-das-zimmer-und-den-kleiderschrank' => self::CHIPS_THEME_BEDROOM_AND_DRESSING,
            'ein-badezimmer-oder-gar-nichts' => self::CHIPS_THEME_BATHROOM_OR_NOTHING,
            'ich-mochte-ein-mobel-fur-den-eingangsbereich-oder-das-wohnzimmer' => self::CHIPS_THEME_ENTRANCE_FURNITURE,
            'die-details-die-alles-verandern' => self::CHIPS_THEME_DETAILS_CHANGE_ALL,
            'mini-budget-maxi-effekt' => self::CHIPS_THEME_LOW_BUDGET_MAX_EFFECT,
            'innenarchitekten-geheimnisse' => self::CHIPS_THEME_ARCHITECT_SECRETS,
            'farbwelt-fur-schlafzimmer-and-kleiderschrank' => self::CHIPS_THEME_BEDROOM_AND_DRESSING_COLOR,
            'kinder-paradies' => self::CHIPS_THEME_KIDS_KINGDOM,
            'spielerische-farbe' => self::CHIPS_THEME_PAINT_GAME,
            'machen-sie-meinen-plan-mit-dem-ikea-kuchenplan-tutorial-videos' => self::CHIPS_THEME_PLAN_WITH_IKP,
        ],
    ];

    public const CHIPS_TYPES = [
        self::CHIP_TYPE_INSPIRATION,
        self::CHIP_TYPE_HOME_TOUR,
        self::CHIP_TYPE_HOME_PROJECT,
        self::CHIP_TYPE_TUTORIAL,
        self::CHIP_TYPE_PRACTICE,
        self::CHIP_TYPE_ADDRESS,
        self::CHIP_TYPE_TOOLBOX,
        self::CHIP_TYPE_TUTO_IKEA,
    ];

    public const CHIPS_ROOMS = [
        self::CHIP_ROOM_KITCHEN,
        self::CHIP_ROOM_BEDROOM_DRESSING,
        self::CHIP_ROOM_BATHROOM,
        self::CHIP_ROOM_OTHER,
        self::CHIP_ROOM_ALL,
    ];

    public function getChips(): array
    {
        return self::CHIPS_TYPES;
    }

    public function getThemes(string $locale = Locale::DEFAULT_LOCALE_CODE): array
    {
        return self::CHIPS_THEMES[$locale] ?? self::CHIPS_THEMES[Locale::DEFAULT_LOCALE_CODE];
    }

    /**
     * Return the translation code key (use french code) for a given translated theme key
     * @param string $theme
     * @param string $locale
     * @return string
     */
    public function getThemeTranslationKey(string $theme, string $locale = Locale::DEFAULT_LOCALE_CODE): string
    {
        if (!isset(self::CHIPS_THEMES[$locale][$theme])) {
            return $theme;
        }

        return array_search(self::CHIPS_THEMES[$locale][$theme], self::CHIPS_THEMES[Locale::DEFAULT_LOCALE_CODE]) ?: $theme; // Search by theme id in FR locale
    }


    public function getRooms(): array
    {
        return self::CHIPS_ROOMS;
    }

    public function getMenuShips(): array
    {
        return [
            self::CHIP_TYPE_INSPIRATION => 'creative_ideas',
            self::CHIP_TYPE_HOME_TOUR => 'home_tours_interviews',
            self::CHIP_TYPE_TUTORIAL => 'shopping_lists_by_step',
            self::CHIP_TYPE_PRACTICE => 'practical_guides',
            self::CHIP_TYPE_ADDRESS => 'address_book',
            self::CHIP_TYPE_TOOLBOX => 'toolbox',
        ];
    }
}
