<?php

declare(strict_types=1);

namespace App\Faker\Provider\Address;

use Faker\Provider\Base as BaseProvider;

final class ProvinceProviderFr extends BaseProvider
{
    private const PROVINCE_PROVIDER = [
        'Autre',
        'Ain',
        'Aisne',
        'Allier',
        'Alpes-de-Haute-Provence',
        'Hautes-alpes',
        'Alpes-maritimes',
        'Ardèche',
        'Ardennes',
        'Ariège',
        'Aube',
        'Aveyron',
        'Bouches-du-Rhône',
        'Calvados',
        'Cantal',
        'Charente',
        'Charente-maritime',
        'Cher',
        'Corrèze',
        'Corse-du-sud',
        'Haute-Corse',
        "Côte-d'Or",
        "Côtes-d'Armor",
        'Creuse',
        'Dordogne',
        'Doubs',
        'Drôme',
        'Eure',
        'Eure-et-loir',
        'Finistère',
        'Gard',
        'Haute-garonne',
        'Gers',
        'Gironde',
        'Hérault',
        'Ille-et-vilaine',
        'Indre',
        'Indre-et-loire',
        'Isère',
        'Jura',
        'Landes',
        'Loir-et-cher',
        'Loire',
        'Haute-loire',
        'Loiret',
        'Lot',
        'Lot-et-garonne',
        'Lozère',
        'Maine-et-loire',
        'Manche',
        'Marne',
        'Haute-Marne',
        'Mayenne',
        'Meurthe-et-Moselle',
        'Meuse',
        'Morbihan',
        'Moselle',
        'Nièvre',
        'Nord',
        'Oise',
        'Orne',
        'Pas-de-Calais',
        'Puy-de-Dôme',
        'Pyrénées-Atlantiques',
        'Hautes-Pyrénées',
        'Pyrénées-Orientales',
        'Bas-Rhin',
        'Haut-Rhin',
        'Rhône',
        'Haute-Saône',
        'Saône-et-Loire',
        'Sarthe',
        'Savoie',
        'Haute-Savoie',
        'Paris',
        'Seine-Maritime',
        'Seine-et-Marne',
        'Yvelines',
        'Deux-Sèvres',
        'Somme',
        'Tarn',
        'Tarn-et-Garonne',
        'Var',
        'Vaucluse',
        'Vendée',
        'Vienne',
        'Haute-Vienne',
        'Vosges',
        'Yonne',
        'Territoire de Belfort',
        'Essonne',
        'Hauts-de-Seine',
        'Seine-St-Denis',
        'Val-de-Marne',
        "Val-D'Oise",
    ];

    public function provinceFr(int $current): string
    {
        return sprintf('FR-%02d', $current);
    }

    public function provinceNameFr(int $current): string
    {
        return self::PROVINCE_PROVIDER[$current];
    }
}
