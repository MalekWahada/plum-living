<?php

declare(strict_types=1);

namespace App\Funnel\Payment\Infrastructure\Controller;

use App\Datadog\Application\DatadogClientInterface;
use App\Funnel\Payment\Application\StripeWebhookHandler;
use App\Funnel\Payment\Domain\Exception\ContextAwareException;
use App\Funnel\Payment\Domain\Exception\DatabaseException;
use App\Funnel\Payment\Domain\Exception\StripeException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class StripeWebhookController extends AbstractController
{
    private StripeWebhookHandler $stripeWebhookHandler;
    private LoggerInterface $logger;
    private DatadogClientInterface $datadog;

    public function __construct(
        StripeWebhookHandler $stripeWebhookHandler,
        LoggerInterface $logger,
        DatadogClientInterface $datadog
    ) {
        $this->stripeWebhookHandler = $stripeWebhookHandler;
        $this->logger = $logger;
        $this->datadog = $datadog;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $tags = [
            'error_type' => null,
            'status' => 'success',
        ];

        try {
            $tags += ($this->stripeWebhookHandler)($request);
        } catch (StripeException|DatabaseException $exception) {
            $tags = [
                'error_type' => $exception->getErrorType(),
                'status' => 'fail',
            ];
            $this->logger->error(
                sprintf('[Stripe-Webhook][Process-Stopped] an error occurred: %s', $exception->getMessage()),
                ['exception' => $exception] + $exception->getContext()
            );
            throw $exception;
        } catch (ContextAwareException $exception) {
            $tags = [
                'error_type' => $exception->getErrorType(),
                'status' => 'fail',
            ];
            $this->logger->error(
                sprintf('[Stripe-Webhook][Process-Continued] an error occurred: %s', $exception->getMessage()),
                ['exception' => $exception] + $exception->getContext()
            );
        } catch (\Throwable $exception) {
            $tags = [
                'error_type' => ContextAwareException::UNKNOWN_EXCEPTION_TYPE,
                'status' => 'fail',
            ];
            $this->logger->error(
                sprintf('[Stripe-Webhook][Process-Continued] an unknown error occurred: %s', $exception->getMessage()),
                ['exception' => $exception]
            );
        } finally {
            $this->datadog->increment('payment.webhook', $tags);
            return new JsonResponse();
        }
    }
}
