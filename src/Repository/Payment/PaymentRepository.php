<?php

declare(strict_types=1);

namespace App\Repository\Payment;

use App\Entity\Payment\PaymentMethod;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentRepository as BaseRepository;
use Sylius\Component\Payment\Model\PaymentInterface;

class PaymentRepository extends BaseRepository
{
    public function getWiretransferToRemind(\DateTimeInterface $limitDate): array
    {
        $qb = $this->createQueryBuilder('payment');
        $qb
            ->join('payment.method', 'method')
            ->where('payment.remindedAt IS NULL AND payment.createdAt < :limitDate AND payment.state = :paymentState AND method.code = :paymentCode')
            ->setParameter(':limitDate', $limitDate)
            ->setParameter(':paymentState', PaymentInterface::STATE_NEW)
            ->setParameter(':paymentCode', PaymentMethod::STRIPE_PAYMENT_METHOD_WIRE_CODE);
        return $qb->getQuery()->getResult();
    }
}
