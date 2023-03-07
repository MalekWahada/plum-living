<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Context\SwitchableTranslation\CompositeSwitchableTranslationContext;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Compiler\PrioritizedCompositeServicePass;
use App\Context\SwitchableTranslation\SwitchableTranslationContextInterface;

class CompositeSwitchableTranslationContextPass extends PrioritizedCompositeServicePass
{
    public function __construct()
    {
        parent::__construct(
            SwitchableTranslationContextInterface::class,
            CompositeSwitchableTranslationContext::class,
            'app.context.switchable_translation',
            'addContext'
        );
    }
}
