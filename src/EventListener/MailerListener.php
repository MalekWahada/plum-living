<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Context\SwitchableTranslation\SwitchableTranslationContextInterface;
use App\Email\Emails;
use App\Entity\Customer\Customer;
use Sylius\Bundle\CoreBundle\EventListener\MailerListener as BaseMailerListener;
use Sylius\Bundle\UserBundle\Mailer\Emails as UserBundleEmails;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\User\Model\UserInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

class MailerListener
{
    private BaseMailerListener $decorated;
    private SenderInterface $emailSender;
    private ChannelContextInterface $channelContext;
    private LocaleContextInterface $localeContext;
    private SwitchableTranslationContextInterface $translationContext;

    public function __construct(
        BaseMailerListener $decorated,
        SenderInterface $emailSender,
        ChannelContextInterface $channelContext,
        LocaleContextInterface $localeContext,
        SwitchableTranslationContextInterface $translationContext
    ) {
        $this->decorated = $decorated;
        $this->emailSender = $emailSender;
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
        $this->translationContext = $translationContext;
    }

    public function sendResetPasswordTokenEmail(GenericEvent $event): void
    {
        $this->sendEmail($event->getSubject(), UserBundleEmails::RESET_PASSWORD_TOKEN); // Use local method to add custom email data
    }

    public function sendResetPasswordPinEmail(GenericEvent $event): void
    {
        $this->sendEmail($event->getSubject(), UserBundleEmails::RESET_PASSWORD_PIN);
    }

    public function sendVerificationTokenEmail(GenericEvent $event): void
    {
        $this->sendEmail($event->getSubject(), UserBundleEmails::EMAIL_VERIFICATION_TOKEN);
    }

    public function sendUserRegistrationEmail(GenericEvent $event): void
    {
        /** @var Customer $customer */
        $customer = $event->getSubject();

        Assert::isInstanceOf($customer, Customer::class);

        if ($customer->hasB2BProgram()) { // B2B users are managed with bellow event
            return;
        }

        $this->decorated->sendUserRegistrationEmail($event);
    }

    /**
     * Event triggered on registration and customer edit (switching from personal to B2B account)
     * @param GenericEvent $event
     */
    public function sendUserRegistrationB2bProgram(GenericEvent $event): void
    {
        /** @var Customer $customer */
        $customer = $event->getSubject();

        Assert::isInstanceOf($customer, Customer::class);

        if (!$customer->hasB2BProgram() || null !== $customer->getPersonalCoupon()) { // Send email if new to B2B program
            return;
        }

        $user = $customer->getUser();
        if (null === $user) {
            return;
        }

        $email = $customer->getEmail();
        if (empty($email)) {
            return;
        }
        $this->sendEmail($user, Emails::EMAIL_USER_REGISTRATION_B2B_PROGRAM);
    }

    private function sendEmail(UserInterface $user, string $emailCode): void
    {
        /** @var Customer $customer */
        $customer = $user->getCustomer(); /** @phpstan-ignore-line  */
        $localeCode = (null !== $customer && null !== $customer->getLocaleCode()) ? $customer->getLocaleCode() : $this->localeContext->getLocaleCode();
        $this->translationContext->setCustomerContext($customer);
        $this->emailSender->send(
            $emailCode,
            [$user->getEmail()],
            [
                'user' => $user,
                'channel' => $this->channelContext->getChannel(),
                'localeCode' => $localeCode,
                'transSlug' => $this->translationContext->getSlug()
            ]
        );
    }
}
