<?php

declare(strict_types=1);

namespace App\Tests;

use Sylius\Component\User\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait SecurityAwareTrait
{
    protected function loginUser(KernelBrowser $client, object $user, string $firewallContext = 'shop'): self
    {
        if (!interface_exists(\Symfony\Component\Security\Core\User\UserInterface::class)) {
            throw new \LogicException(sprintf('"%s" requires symfony/security-core to be installed.', __METHOD__));
        }

        if (!$user instanceof UserInterface) {
            throw new \LogicException(sprintf('The first argument of "%s" must be instance of "%s", "%s" provided.', __METHOD__, UserInterface::class, \is_object($user) ? \get_class($user) : \gettype($user)));
        }

        $token = new UsernamePasswordToken($user, null, $firewallContext, $user->getRoles());
        $session = $client->getContainer()->get('session');
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        return $this;
    }
}
