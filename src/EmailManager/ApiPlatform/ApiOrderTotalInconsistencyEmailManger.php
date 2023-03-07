<?php

declare(strict_types=1);

namespace App\EmailManager\ApiPlatform;

use App\Email\Emails;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ApiOrderTotalInconsistencyEmailManger
{
    private string  $adminEmailAddress;

    private SenderInterface $sender;

    public function __construct(
        string $adminEmailAddress,
        SenderInterface $sender
    ) {
        $this->adminEmailAddress = $adminEmailAddress;
        $this->sender = $sender;
    }

    public function sendInconsistentOrderTotalEmail(OrderInterface $order, array $data = []): void
    {
        $this->sender->send(Emails::EMAIL_API_ORDER_TOTAL_INCONSISTENCY_CODE, [$this->adminEmailAddress], array_merge(['order' => $order], $data));
    }
}
