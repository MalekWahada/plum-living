<?php

declare(strict_types=1);

namespace App\EmailManager\Cart;

use App\Email\Emails;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShareCartEmailManager
{
    private SenderInterface $sender;
    private RouterInterface $router;
    private TranslatorInterface $translator;
    private CustomerContextInterface $customerContext;

    public function __construct(
        SenderInterface $sender,
        RouterInterface $router,
        TranslatorInterface $translator,
        CustomerContextInterface $customerContext
    ) {
        $this->sender = $sender;
        $this->router = $router;
        $this->translator = $translator;
        $this->customerContext = $customerContext;
    }

    public function shareCart(string $email, string $orderCacheKey): void
    {
        $this->sender->send(Emails::EMAIL_SHARE_CART_CODE, [$email], [
            'customerName' => $this->getCustomerName(),
            'cartUrl' => $this->getCartUrl($orderCacheKey),
        ]);
    }

    private function getCustomerName(): string
    {
        $noName = $this->translator->trans('app.ui.cart.share_cart.no_customer_name');
        $customer = $this->customerContext->getCustomer();
        if ($customer !== null) {
            return $customer->getFirstName() ??
                $customer->getLastName() ??
                explode('@', $customer->getEmail())[0] ??
                $noName;
        }
        return $noName;
    }

    private function getCartUrl(string $cacheToken): string
    {
        return $this->router->generate(
            'app_duplicate_cart',
            [
                'orderCacheToken' => $cacheToken,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
