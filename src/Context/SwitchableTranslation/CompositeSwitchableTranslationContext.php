<?php

declare(strict_types=1);

namespace App\Context\SwitchableTranslation;

use App\Exception\Translation\SwitchableTranslationNotFoundException;
use Sylius\Component\Core\Model\CustomerInterface;
use Zend\Stdlib\PriorityQueue;

class CompositeSwitchableTranslationContext implements SwitchableTranslationContextInterface
{
    /**
     * @var PriorityQueue|SwitchableTranslationContextInterface[]
     *
     * @psalm-var PriorityQueue<SwitchableTranslationContextInterface>
     */
    private $translationContexts;

    public function __construct()
    {
        $this->translationContexts = new PriorityQueue();
    }

    public function addContext(SwitchableTranslationContextInterface $translationContext, int $priority = 0): void
    {
        $this->translationContexts->insert($translationContext, $priority);
    }

    public function getSlug(): string
    {
        $lastException = null;

        foreach ($this->translationContexts as $translationContext) {
            try {
                return $translationContext->getSlug();
            } catch (SwitchableTranslationNotFoundException $exception) {
                $lastException = $exception;

                continue;
            }
        }

        throw new SwitchableTranslationNotFoundException(null, $lastException);
    }

    public function setCustomerContext(?CustomerInterface $customer): void
    {
        foreach ($this->translationContexts as $translationContext) {
            $translationContext->setCustomerContext($customer);
        }
    }
}
