<?php

declare(strict_types=1);

namespace App\Grid\Filter;

use App\Entity\Order\Order;
use App\Erp\Providers\NetSuiteOrderStatusProvider;
use App\Provider\Order\StatusProvider;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

/**
 * This grid filter filters custom order states defined in StatusProvider
 */
final class OrderStateFilter implements FilterInterface
{
    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        if (null === $data || "" === $data) { // Case 'all'
            return;
        }

        switch ($data) {
            case StatusProvider::STATE_PENDING:
                $dataSource->restrict($dataSource->getExpressionBuilder()->notEquals("paymentState", OrderPaymentStates::STATE_PAID));
                break;
            case StatusProvider::STATE_FULLY_PAID:
                $dataSource->restrict($dataSource->getExpressionBuilder()->andX(
                    $dataSource->getExpressionBuilder()->equals("paymentState", OrderPaymentStates::STATE_PAID),
                    $dataSource->getExpressionBuilder()->equals("erpStatus", Order::DEFAULT_ERP_STATUS)
                ));
                break;
            case StatusProvider::STATE_PARTIALLY_SHIPPED:
                $dataSource->restrict($dataSource->getExpressionBuilder()->equals("erpStatus", NetSuiteOrderStatusProvider::ORDER_ERP_STATUS_PRODUCTION_IN_PROGRESS));
                break;
            case StatusProvider::STATE_FULLY_SHIPPED:
                $dataSource->restrict($dataSource->getExpressionBuilder()->orX(
                    $dataSource->getExpressionBuilder()->equals("erpStatus", NetSuiteOrderStatusProvider::ORDER_ERP_STATUS_DELIVERY_IN_PROGRESS),
                    $dataSource->getExpressionBuilder()->equals("erpStatus", NetSuiteOrderStatusProvider::ORDER_ERP_STATUS_DELIVERED)
                ));
                break;
            case StatusProvider::STATE_FULFILLED:
                $dataSource->restrict($dataSource->getExpressionBuilder()->equals("erpStatus", NetSuiteOrderStatusProvider::ORDER_ERP_STATUS_BILLED));
                break;
            case StatusProvider::STATE_CANCELLED:
                $dataSource->restrict($dataSource->getExpressionBuilder()->equals("erpStatus", NetSuiteOrderStatusProvider::ORDER_ERP_STATUS_CANCELED));
                break;
            default:
                break;
        }
    }
}
