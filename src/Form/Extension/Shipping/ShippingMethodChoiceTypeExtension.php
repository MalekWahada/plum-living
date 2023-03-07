<?php

declare(strict_types=1);

namespace App\Form\Extension\Shipping;

use App\Checkout\Shipping\ShippingPriceCalculator;
use App\Entity\Order\Order;
use App\Entity\Shipping\Shipment;
use App\Entity\Shipping\ShippingMethod;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ShippingMethodChoiceTypeExtension extends AbstractTypeExtension
{
    private ShippingPriceCalculator $shippingPriceCalculator;

    public function __construct(ShippingPriceCalculator $shippingPriceCalculator)
    {
        $this->shippingPriceCalculator = $shippingPriceCalculator;
    }

    // used in checkout shipping step to calculate taxed shipping cost
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (!isset($options['subject'])) {
            return;
        }
        /** @var Shipment $subject */
        $subject = $options['subject'];
        /** @var Order $order */
        $order = $subject->getOrder();

        $shippingCosts = [];
        $viewChoices = $view->vars['choices'];

        foreach ($viewChoices as $choiceView) {
            $method = $choiceView->data;
            if (!$method instanceof ShippingMethod) {
                throw new UnexpectedTypeException($method, ShippingMethod::class);
            }
            $shippingCosts[$choiceView->value] = $this->shippingPriceCalculator->calculatePrice($method, $order);
        }

        $view->vars['shipping_costs'] = $shippingCosts;
    }

    public static function getExtendedTypes(): iterable
    {
        return [
            ShippingMethodChoiceType::class,
        ];
    }
}
