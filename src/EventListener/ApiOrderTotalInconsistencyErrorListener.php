<?php

declare(strict_types=1);

namespace App\EventListener;

use App\EmailManager\ApiPlatform\ApiOrderTotalInconsistencyEmailManger;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

class ApiOrderTotalInconsistencyErrorListener
{
    private ApiOrderTotalInconsistencyEmailManger $emailManager;

    public function __construct(ApiOrderTotalInconsistencyEmailManger $emailManger)
    {
        $this->emailManager = $emailManger;
    }

    public function onError(GenericEvent $event): void
    {
        /** @var OrderInterface $order */
        $order = $event->getSubject();

        Assert::notNull($order);
        $this->emailManager->sendInconsistentOrderTotalEmail($order, $event->getArguments());
    }
}
