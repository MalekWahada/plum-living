<?php

declare(strict_types=1);

namespace App\Repository\Order;

use App\Entity\Order\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class CustomExportOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Order::class);
    }
    
    public function findOrdersByCustomRequest(): Query
    {
        return $this
            ->createQueryBuilder('ord')
            ->select('ord.id , ord.number, ord.checkoutCompletedAt, ord.total, ord.checkoutState, ord.paymentState, ord.originalOrderNumber, ord.targetedRoom, ord.minDateDelivery, ord.maxDateDelivery, ord.mailingWorkflowType, ord.erpRegistered, ord.erpStatus, promo.code,
                cus.email, cus.firstName AS customer_firstname, cus.lastName AS customer_lastname , cus.birthday, cus.gender, cus.createdAt, cus.phoneNumber AS customer_phonenumber, cus.subscribedToNewsletter, cus.howYouKnowAboutUs, cus.customerType, cus.howYouKnowAboutUsDetails,
                ad1.firstName AS shipping_firstname, ad1.lastName AS shipping_lastname, ad1.company AS shipping_company, ad1.street AS shipping_street, ad1.postcode AS shipping_postcode, ad1.city AS shipping_city, ad1.provinceCode AS shipping_provincecode, ad1.countryCode AS shipping_countrycode, ad1.phoneNumber AS shipping_phonenumber,
                ad2.firstName AS billing_firstname, ad2.lastName AS billing_lastname, ad2.company AS billing_company, ad2.street AS billing_street, ad2.postcode AS billing_postcode, ad2.city AS billing_city, ad2.provinceCode AS billing_provincecode, ad2.countryCode AS billing_countrycode, ad2.phoneNumber AS billing_phonenumber')
            ->Join('ord.customer', 'cus')
            ->Join('ord.shippingAddress', 'ad1')
            ->Join('ord.billingAddress', 'ad2')
            ->LeftJoin('ord.promotionCoupon', 'promo')
            ->Where("ord.paymentState = 'paid'")
            ->OrderBy('ord.id', 'DESC')
            ->getQuery()
        ;
    }
}
