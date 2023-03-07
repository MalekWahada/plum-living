<?php

declare(strict_types=1);

namespace App\Factory\Customer;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class CustomerFactory implements FactoryInterface
{
    private FactoryInterface $baseCustomerFactory;

    public function __construct(FactoryInterface $baseCustomerFactory)
    {
        $this->baseCustomerFactory = $baseCustomerFactory;
    }

    public function createNew(): CustomerInterface
    {
        /** @var CustomerInterface $customer */
        $customer = $this->baseCustomerFactory->createNew();
        $customer->setGender('');
        return $customer;
    }
}
