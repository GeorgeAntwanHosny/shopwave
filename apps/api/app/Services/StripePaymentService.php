<?php

namespace App\Services;

use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\Transfer;
use Stripe\Webhook;

class StripePaymentService
{
    protected StripeClient $client;

    public function __construct(?StripeClient $client = null)
    {
        $this->client = $client ?? new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Charges the customer via a normal (non-destination) PaymentIntent that
     * settles into the platform's own Stripe balance — half of Stripe's
     * "Separate Charges and Transfers" Connect pattern. The other half
     * (per-vendor Transfers) happens in createTransfer() below, once
     * payment_intent.succeeded confirms the charge went through.
     */
    public function createPaymentIntent(int $amountCents, string $currency, array $metadata = []): PaymentIntent
    {
        return $this->client->paymentIntents->create([
            'amount' => $amountCents,
            'currency' => $currency,
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => $metadata,
        ]);
    }

    /**
     * Moves a vendor's payout share from the platform's balance to their
     * connected account. Transfers remain a stable v1 API and work fine
     * against v2-created Accounts, as long as the account has the recipient
     * configuration's stripe_transfers capability requested — which
     * StripeConnectService::createConnectedAccount() already does for every
     * vendor at onboarding (Phase 2), so no changes were needed there.
     */
    public function createTransfer(int $amountCents, string $currency, string $destinationAccountId, string $transferGroup, array $metadata = []): Transfer
    {
        return $this->client->transfers->create([
            'amount' => $amountCents,
            'currency' => $currency,
            'destination' => $destinationAccountId,
            'transfer_group' => $transferGroup,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Classic v1 "snapshot" webhook verification — deliberately NOT the v2
     * thin-event path used by StripeConnectService::parseWebhookEvent().
     * Thin events for v1 resources like PaymentIntent are still in private
     * preview; payment_intent.* events are delivered as full-payload classic
     * webhooks, verified the traditional way.
     *
     * No return type is declared here — same reasoning as
     * StripeConnectService::parseWebhookEvent(): tests mock this method to
     * return a lightweight stdClass with `type`/`data->object` rather than
     * a real Stripe\Event, and PHP enforces a declared return type even
     * through a Mockery-generated mock. A strict `: Event` here caused
     * exactly that TypeError in the webhook tests.
     */
    public function constructWebhookEvent(string $payload, string $signature)
    {
        return Webhook::constructEvent(
            $payload,
            $signature,
            config('services.stripe.payment_webhook_secret')
        );
    }

    /**
     * Refunds the customer for their share of a multi-vendor charge —
     * amountCents can be less than the full PaymentIntent, since one
     * checkout's PaymentIntent may cover several vendor-orders.
     */
    public function createRefund(string $paymentIntentId, int $amountCents, ?string $reason = null): \Stripe\Refund
    {
        return $this->client->refunds->create([
            'payment_intent' => $paymentIntentId,
            'amount' => $amountCents,
            'metadata' => $reason ? ['reason' => $reason] : [],
        ]);
    }

    /**
     * Claws back a vendor's payout — required before/alongside refunding
     * the customer if the Transfer already went out, since Stripe's
     * "Separate Charges and Transfers" pattern does NOT automatically
     * reverse a completed Transfer just because the original charge was
     * refunded. Based on current Stripe API docs — not executed against a
     * live account in this environment, so confirm against a real test-mode
     * dispute before relying on it in anger.
     */
    public function reverseTransfer(string $transferId, int $amountCents): \Stripe\TransferReversal
    {
        return $this->client->transfers->createReversal($transferId, [
            'amount' => $amountCents,
        ]);
    }
}
