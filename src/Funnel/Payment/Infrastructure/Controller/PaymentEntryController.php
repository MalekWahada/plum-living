<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Infrastructure\Controller;

use App\Datadog\Application\DatadogClient;
use App\Datadog\Application\DatadogClientInterface;
use App\Funnel\Payment\Application\PaymentEntryHandler;
use App\Funnel\Payment\Domain\Exception\ContextAwareException;
use App\Funnel\Payment\Domain\Exception\UnsupportedPaymentEntryActionException;
use App\Funnel\Payment\Domain\PaymentEntryAction;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PaymentEntryController extends AbstractController
{
    private LoggerInterface $logger;
    private PaymentEntryHandler $handler;
    private DatadogClientInterface $datadog;

    public function __construct(
        LoggerInterface $logger,
        PaymentEntryHandler $handler,
        DatadogClientInterface $datadog
    ) {
        $this->logger = $logger;
        $this->handler = $handler;
        $this->datadog = $datadog;
    }

    public function __invoke(Request $request): Response
    {
        $tags = [
            'error_type' => null,
            'status' => 'success',
        ];

        try {
            $action = ($this->handler)($request);
            return $this->apply($action);
        } catch (UnsupportedPaymentEntryActionException $e) {
            $tags['error_type'] = 'unsupported_payment_entry_action';
            $tags['status'] = 'fail';
            $this->logger->error(
                '[Stripe-Entry] an unsupported payment entry action was found',
                ['exception' => $e]
            );
        } catch (ContextAwareException $e) {
            $tags['error_type'] = $e->getErrorType();
            $tags['status'] = 'fail';
            $this->logger->error(
                '[Stripe-Entry] an error occurred',
                ['exception' => $e] + $e->getContext()
            );
        } catch (\Throwable $e) {
            $tags['error_type'] = ContextAwareException::UNKNOWN_EXCEPTION_TYPE;
            $tags['status'] = 'fail';
            $this->logger->error(
                '[Stripe-Entry] an unknown error occurred',
                ['exception' => $e]
            );
        } finally {
            $this->datadog->increment('payment.entry', $tags);
        }

        return $this->render('@Twig/Exception/error500.html.twig');
    }

    private function apply(PaymentEntryAction $action): Response
    {
        switch ($action->action) {
            case PaymentEntryAction::ACTION_REDIRECT:
                return $this->redirectToRoute($action->field, $action->options);
            case PaymentEntryAction::ACTION_RENDER:
                return $this->render($action->field, $action->options);
            default:
                throw new UnsupportedPaymentEntryActionException(sprintf('Action "%s" is not supported', $action->action));
        }
    }
}
