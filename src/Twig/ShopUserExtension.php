<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Customer\Customer;
use App\Entity\User\ShopUser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Webmozart\Assert\Assert;

class ShopUserExtension extends AbstractExtension
{
    public function getFilters(): iterable
    {
        return [
            new TwigFilter('shop_user_export_typeform', [$this, 'exportShopUserForTypeform']),
        ];
    }

    public function exportShopUserForTypeform(ShopUser $user): array
    {
        Assert::isInstanceOf($user, ShopUser::class);
        if (null === $user->getCustomer()) {
            return [];
        }

        /** @var Customer $customer */
        $customer = $user->getCustomer();

        $object = [
            'email' => $user->getEmail(),
            'firstname' => $customer->getFirstName(),
            'lastname' => $customer->getLastName(),
            'is_b2b_program' => $customer->hasB2BProgram() ? 'true' : 'false',
            'customer_type' => $customer->getCustomerType()
        ];
        if ($customer->hasB2BProgram() && null !== $customer->getPersonalCoupon()) {
            $object['personal_coupon'] = $customer->getPersonalCoupon()->getCode();
        }

        return $object;
    }
}
