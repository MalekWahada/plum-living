<?php

declare(strict_types=1);

namespace App\EmailManager\CMS;

use App\Email\Emails;
use Exception;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProjectPlanEmailManager
{
    private SenderInterface $sender;

    public function __construct(
        SenderInterface $sender
    ) {
        $this->sender = $sender;
    }

    public function sendProjectPlan(string $email, string $pdf): void
    {
        try {
            $this->sender->send(Emails::EMAIL_SHARE_PLAN_CODE, [$email], [], [$pdf]);
        } catch (Exception $e) {
            throw new BadRequestHttpException(sprintf('Invalid mail address received. Got: "%s"', $email));
        }
    }
}
