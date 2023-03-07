<?php

declare(strict_types=1);

namespace App\Provider\Tunnel\Shopping;

use App\Entity\Taxonomy\Taxon;
use App\Entity\Tunnel\Shopping\Combination;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class CombinationProvider
{
    private RepositoryInterface $combinationRepository;

    public function __construct(RepositoryInterface $combinationRepository)
    {
        $this->combinationRepository = $combinationRepository;
    }

    /**
     * Get combination based on value of facade type, design, finish and color.
     */
    public function findCombination(
        ?Taxon $facadeType,
        ?ProductOptionValueInterface $design = null,
        ?ProductOptionValueInterface $finish = null,
        ?ProductOptionValueInterface $color = null
    ): ?Combination {
        // If no option is set, the combination will be null
        $combination = $this->combinationRepository->findOneBy([
            'facadeType' => $facadeType,
            'design' => $design,
            'finish' => $finish,
            'color' => $color,
        ]);

        if (null === $combination) {
            $optionsArguments = func_get_args();
            $combination = call_user_func_array([$this, 'findParentCombination'], $optionsArguments);
        }

        return $combination;
    }

    private function findParentCombination(
        ?Taxon $facadeType,
        ?ProductOptionValueInterface $design = null,
        ?ProductOptionValueInterface $finish = null,
        ?ProductOptionValueInterface $color = null
    ): ?Combination {
        $optionsArguments = func_get_args();
        //remove the last ProductOptionValue
        array_pop($optionsArguments);

        if (count($optionsArguments) === 0) {
            return null;
        }
        //find the combination without last ProductOptionValue (search for parent combination)
        return call_user_func_array([$this, 'findCombination'], $optionsArguments);
    }
}
