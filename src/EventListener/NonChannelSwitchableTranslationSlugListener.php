<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Channel\Channel;
use App\Exception\Context\IncorrectSwitchableTranslationException;
use App\Model\Translation\SwitchableTranslation;
use App\Provider\Translation\SwitchableTranslationProvider;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

/**
 * This listener redirects to homepage if the slug is incorrect for the current channel.
 * Only works for shop section.
 */
class NonChannelSwitchableTranslationSlugListener
{
    private SwitchableTranslationProvider $translationProvider;
    private ChannelContextInterface $channelContext;
    private FirewallMap $firewallMap;

    /** @var string[] */
    private array $firewallNames;

    /**
     * @param string[] $firewallNames
     */
    public function __construct(
        SwitchableTranslationProvider $translationProvider,
        ChannelContextInterface $channelContext,
        FirewallMap $firewallMap,
        array $firewallNames
    ) {
        Assert::notEmpty($firewallNames);
        Assert::allString($firewallNames);

        $this->translationProvider = $translationProvider;
        $this->channelContext = $channelContext;
        $this->firewallMap = $firewallMap;
        $this->firewallNames = $firewallNames;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function restrictRequestLocale(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        // Route has no translation slug parameter or is debug route.
        if (!$request->attributes->has(SwitchableTranslation::TRANSLATION_SLUG_PARAMETER)
            || in_array($request->attributes->get('_route'), ['_wdt', '_profiler', '_profiler_search', '_profiler_search_results'])) {
            return;
        }

        $currentFirewall = $this->firewallMap->getFirewallConfig($request);
        if (!$this->isFirewallSupported($currentFirewall)) { // Only for shop section
            return;
        }

        /** @var Channel $channel */
        $channel = $this->channelContext->getChannel();

        /**
         * We must use request attribute directly to check an invalid slug. Using SwitchableTranslationContext would return a default slug.
         */
        $currentSlug = $request->attributes->get(SwitchableTranslation::TRANSLATION_SLUG_PARAMETER);

        if (!array_key_exists($currentSlug, $this->translationProvider->getChannelTranslations($channel))) {
            $newSlug = $this->translationProvider->getChannelDefaultSlug($channel);
            throw new IncorrectSwitchableTranslationException($currentSlug, $newSlug ?? $this->translationProvider->getDefaultSlug());
        }
    }

    private function isFirewallSupported(?FirewallConfig $firewall = null): bool
    {
        return
            null !== $firewall &&
            in_array($firewall->getName(), $this->firewallNames, true)
            ;
    }
}
