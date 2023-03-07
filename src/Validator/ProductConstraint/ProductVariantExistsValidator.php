<?php

declare(strict_types=1);

namespace App\Validator\ProductConstraint;

use App\Entity\CustomerProject\ProjectItem;
use Psr\Log\LoggerInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\Channel;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ProductVariantExistsValidator extends ConstraintValidator
{
    private ChannelContextInterface $channelContext;
    private LoggerInterface $logger;

    public function __construct(
        ChannelContextInterface $channelContext,
        LoggerInterface $logger
    ) {
        $this->channelContext = $channelContext;
        $this->logger = $logger;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProductVariantExists) {
            throw new UnexpectedTypeException($constraint, ProductVariantExists::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        /** @var ProjectItem $projectItem */
        $projectItem = $value;

        $chosenVariant = $projectItem->getChosenVariant();

        if (null === $chosenVariant) {
            $this->context->buildViolation('app.customer_project.chosen_variant.not_set')
                ->atPath('validationError')
                ->addViolation();
            return;
        }

        /** @var Channel $channel */
        $channel = $this->channelContext->getChannel();

        // if the product doesn't have a price for given channel -> error goes to color option
        if (null === $chosenVariant->getProductVariant() || !$chosenVariant->getProductVariant()->hasChannelPricingForChannel($channel)) {
            $this->logger->error(sprintf('ProductVariantExistsValidator: ProductVariant does not exist or has not price for channel %s and projectItemVariant #%s', $channel->getCode(), $chosenVariant->getId()));
            $this->context->buildViolation('app.customer_project.chosen_variant.error_occurred')
                ->atPath('validationError')
                ->addViolation();
        }
    }
}
