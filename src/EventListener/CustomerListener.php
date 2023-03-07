<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Context\SwitchableTranslation\SwitchableTranslationContextInterface;
use App\Entity\Customer\Customer;
use App\Exception\Translation\SwitchableTranslationsNotConfiguredException;
use App\Model\Translation\SwitchableTranslation;
use App\Provider\Translation\SwitchableTranslationProvider;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Context\LocaleNotFoundException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Webmozart\Assert\Assert;

class CustomerListener
{
    private ChannelContextInterface $channelContext;
    private LocaleContextInterface $localeContext;
    private LoggerInterface $logger;
    private SwitchableTranslationContextInterface $translationContext;
    private SwitchableTranslationProvider $translationProvider;
    private RouterInterface $router;
    private SessionInterface $session;

    public function __construct(
        ChannelContextInterface $channelContext,
        LocaleContextInterface $localeContext,
        LoggerInterface $logger,
        SwitchableTranslationContextInterface $translationContext,
        SwitchableTranslationProvider $translationProvider,
        RouterInterface $router,
        SessionInterface $session
    ) {
        $this->channelContext = $channelContext;
        $this->localeContext = $localeContext;
        $this->logger = $logger;
        $this->translationContext = $translationContext;
        $this->translationProvider = $translationProvider;
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * Store locale on customer registration.
     * We must listen to doctrine event "prePersist"
     * because newsletter subscription is not emitting any event
     * @param Customer $customer
     * @param LifecycleEventArgs $args
     */
    public function onPersistAssignLocaleAndChannel(Customer $customer, LifecycleEventArgs $args): void
    {
        Assert::isInstanceOf($customer, Customer::class);

        try {
            $customer->setChannelCode($this->channelContext->getChannel()->getCode());
        } catch (ChannelNotFoundException $exception) {
            $this->logger->error(sprintf('Unable to set channel (channel not found) for customer %s', $customer->getEmail()));
        }
        try {
            $customer->setLocaleCode($this->localeContext->getLocaleCode());
        } catch (LocaleNotFoundException $exception) {
            $this->logger->error(sprintf('Unable to set locale (locale not found) for customer %s', $customer->getEmail()));
        }
        $this->logger->debug(sprintf('Channel %s and locale %s assigned to customer %s', $customer->getChannelCode(), $customer->getLocaleCode(), $customer->getEmail()));
    }

    /**
     * @throws SwitchableTranslationsNotConfiguredException
     */
    public function onInteractiveLogin(InteractiveLoginEvent $interactiveLoginEvent): void
    {
        $user = $interactiveLoginEvent->getAuthenticationToken()->getUser();
        if (!$user instanceof ShopUserInterface) {
            return;
        }
        /** @var Customer $customer */
        $customer = $user->getCustomer();
        Assert::isInstanceOf($customer, Customer::class);

        if (null === $customer->getLocaleCode() || null === $customer->getChannelCode()) {
            return;
        }

        $slug = $this->translationProvider->findSlugFromChannelAndLocale($customer->getChannelCode(), $customer->getLocaleCode());
        $currentSlug = $this->translationContext->getSlug();
        if (isset($slug) && $slug !== $currentSlug) {
            $default = $this->router->generate('app_account_dashboard', [SwitchableTranslation::TRANSLATION_SLUG_PARAMETER => $slug]);
            $refererTargetPath = $this->session->get('_security.shop.target_path', $default);

            // Replace referer URL locale in session
            $this->session->set('_security.shop.target_path', str_replace(sprintf('/%s/', $currentSlug), sprintf('/%s/', $slug), $refererTargetPath));

            $this->logger->debug(sprintf('Switching customer (%s) from slug %s to slug %s', $customer->getEmail(), $currentSlug, $slug));
        }
    }
}
