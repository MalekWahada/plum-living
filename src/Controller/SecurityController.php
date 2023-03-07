<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Customer\Customer;
use RuntimeException;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository;
use Sylius\Bundle\UiBundle\Form\Type\SecurityLoginType;
use Sylius\Bundle\UserBundle\Mailer\Emails;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

final class SecurityController
{
    private AuthenticationUtils $authenticationUtils;

    private FormFactoryInterface $formFactory;

    private Environment $templatingEngine;

    private AuthorizationCheckerInterface $authorizationChecker;

    private RouterInterface $router;

    private CustomerRepository $customerRepository;

    private SenderInterface $emailSender;

    private FlashBagInterface $flashBag;

    private ChannelContextInterface $channelContext;

    public function __construct(
        AuthenticationUtils $authenticationUtils,
        FormFactoryInterface $formFactory,
        Environment $templatingEngine,
        AuthorizationCheckerInterface $authorizationChecker,
        RouterInterface $router,
        CustomerRepository $customerRepository,
        SenderInterface $emailSender,
        FlashBagInterface $flashBag,
        ChannelContextInterface $channelContext
    ) {
        $this->authenticationUtils = $authenticationUtils;
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->customerRepository = $customerRepository;
        $this->emailSender = $emailSender;
        $this->flashBag = $flashBag;
        $this->channelContext = $channelContext;
    }

    public function loginAction(Request $request): Response
    {
        $alreadyLoggedInRedirectRoute = $request->attributes->get('_sylius')['logged_in_route']['path'] ?? null;

        if ($alreadyLoggedInRedirectRoute && $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new RedirectResponse($this->router->generate(
                $alreadyLoggedInRedirectRoute,
                $this->getRedirectParameters($request)
            ));
        }

        $lastError = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        $options = $request->attributes->get('_sylius');

        $template = $options['template'] ?? '@SyliusUi/Security/login.html.twig';
        $formType = $options['form'] ?? SecurityLoginType::class;
        $form = $this->formFactory->createNamed('', $formType);

        $isVerified = true;
        if ($lastUsername !== '') {
            /** @var Customer|null $customer */
            $customer = $this->customerRepository->findOneBy(['email' => $lastUsername]);
            $shopUser = $customer !== null ? $customer->getUser() : null;
            $isVerified = $shopUser !== null ? $shopUser->isVerified() : true;
        }

        return new Response($this->templatingEngine->render($template, [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'last_error' => $lastError,
            'isVerified' => $isVerified,
        ]));
    }

    // used to verify email when verification is required with signing up (sign-up/email address verification is a B.O. config)
    public function verifyNonLoggedUser(Request $request): Response
    {
        $lastUsername = $this->authenticationUtils->getLastUsername();

        /** @var Customer|null $customer */
        $customer = $this->customerRepository->findOneBy(['email' => $lastUsername]);
        if (null !== $customer && !$customer->getUser()->isVerified()) {
            $templateParams = [
                'user' => $customer->getUser(),
                'localeCode' => $request->getLocale(),
                'channel' => $this->channelContext->getChannel(),
            ];
            $this->emailSender->send(Emails::EMAIL_VERIFICATION_TOKEN, [$customer->getEmail()], $templateParams);
            $this->flashBag->add('success', 'sylius.user.verify_email_request');
        } else {
            $this->flashBag->add('warning', 'sylius.user.verify_verified_email');
        }

        $redirectUrl = $request->headers->get('referer') ?? $this->router->generate('sylius_shop_homepage');

        return new RedirectResponse($redirectUrl);
    }

    public function checkAction(): void
    {
        throw new RuntimeException('You must configure the check path to be handled by the firewall.');
    }

    public function logoutAction(): void
    {
        throw new RuntimeException('You must configure the logout path to be handled by the firewall.');
    }

    private function getRedirectParameters(Request $request): array
    {
        return is_array($request->attributes->get('_sylius')['logged_in_route']['parameters']) ?
            array_intersect_key(
                $request->attributes->all(),
                array_flip($request->attributes->get('_sylius')['logged_in_route']['parameters'])
            ) :
            [];
    }
}
