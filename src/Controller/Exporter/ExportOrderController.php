<?php

declare(strict_types=1);

namespace App\Controller\Exporter;

use App\Repository\Order\CustomExportOrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

final class ExportOrderController extends AbstractController
{
    private CustomExportOrderRepository $customExportOrderRepository;

    public function __construct(
        CustomExportOrderRepository $customExportOrderRepository
    ) {
        $this->customExportOrderRepository = $customExportOrderRepository;
    }

    public function __invoke(Request $request): Response
    {
        $response = new Response($this->write());

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'Orders.csv'
        );

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function write(): string
    {
        $orders = $this->customExportOrderRepository->findOrdersByCustomRequest()->getArrayResult();

        $headers = [
            'id', 'number', 'checkout_completed_at', 'total', 'checkout_state', 'payment_state', 'original_order_number',
            'targeted_room', 'min_date_delivery', 'max_date_delivery', 'mailing_workflow_type', 'erp_registered', 'erp_status',
            'code', 'email', 'customer_firstname', 'customer_lastname', 'birthday', 'gender', 'created_at', 'customer_phone_number',
            'subscribed_to_newsletter', 'how_you_know_about_us', 'customer_type', 'how_you_know_about_us_details', 'shipping_firstname',
            'shipping_lastname', 'shipping_company', 'shipping_street', 'shipping_post_code', 'shipping_city', 'shipping_province_code',
            'shipping_country_code', 'shipping_phone_number', 'billing_firstname', 'billing_lastname', 'billing_company',
            'billing_street', 'billing_post_code', 'billing_city', 'billing_province_code', 'billing_country_code', 'billing_phone_number'
        ];

        $rows = [];
        $rows[] = implode(',', $headers);
        foreach ($orders as $order) {
            $data = [
                $order['id'],
                $order['number'],
                $order['checkoutCompletedAt']->format('Y-m-d H:i:s'),
                $order['total'],
                $order['checkoutState'],
                $order['paymentState'],
                $order['originalOrderNumber'],
                $order['targetedRoom'],
                $order['minDateDelivery']->format('Y-m-d'),
                $order['maxDateDelivery']->format('Y-m-d'),
                $order['mailingWorkflowType'],
                $order['erpRegistered'],
                $order['erpStatus'],
                $order['code'],
                $order['email'],
                $order['customer_firstname'],
                $order['customer_lastname'],
                $order['birthday'] ? $order['birthday']->format('Y-m-d') : 'NC',
                $order['gender'],
                $order['createdAt']->format('Y-m-d H:i:s'),
                $order['customer_phonenumber'],
                $order['subscribedToNewsletter'],
                $order['howYouKnowAboutUs'],
                $order['customerType'],
                $order['howYouKnowAboutUsDetails'],
                $order['shipping_firstname'],
                $order['shipping_lastname'],
                $order['shipping_company'],
                $order['shipping_street'],
                $order['shipping_postcode'],
                $order['shipping_city'],
                $order['shipping_provincecode'],
                $order['shipping_countrycode'],
                $order['shipping_phonenumber'],
                $order['billing_firstname'],
                $order['billing_lastname'],
                $order['billing_company'],
                $order['billing_street'],
                $order['billing_postcode'],
                $order['billing_city'],
                $order['billing_provincecode'],
                $order['billing_countrycode'],
                $order['billing_phonenumber'],
            ];

            $rows[] = sprintf('"%s"', implode('", "', $data));
        }

        return implode("\n", $rows);
    }
}
