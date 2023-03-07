<?php

declare(strict_types=1);

namespace App\EmailManager\OrderProcessing;

use App\Email\Emails;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\Order\Model\OrderInterface;

class OrderAdjustmentsTotalInconsistencyEmailManager
{
    private SenderInterface $sender;
    private string $adminEmailAddress;

    public function __construct(string $adminEmailAddress, SenderInterface $sender)
    {
        $this->adminEmailAddress = $adminEmailAddress;
        $this->sender = $sender;
    }

    public function sendOrderAdjustmentsTotalInconsistencyEmail(
        OrderInterface $order,
        int $previousTotal,
        int $newTotal
    ): void {
        $this->sender->send(Emails::EMAIL_ORDER_ADJUSTMENTS_INCONSISTENCY_CODE, [
            $this->adminEmailAddress,
        ], [
            'order' => $order,
            'newTotal' => $newTotal,
            'previousTotal' => $previousTotal,
        ]);
    }
}
