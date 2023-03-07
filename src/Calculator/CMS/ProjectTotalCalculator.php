<?php

declare(strict_types=1);

namespace App\Calculator\CMS;

class ProjectTotalCalculator
{
    public function calculateTotal(array $cardContent): ?float
    {
        $key = array_search('app.card_total', array_column($cardContent, 'code'));
        $cardData = $cardContent[$key]['data'];

        if (array_key_exists('cardTotalToDisplay', $cardData) && !in_array($cardData['cardTotalToDisplay'], [0, null], true)) {
            return $cardData['cardTotalToDisplay'];
        }

        return array_sum(array_column($cardData['cardElements'], 'price'));
    }
}
