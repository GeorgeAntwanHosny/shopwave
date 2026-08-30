<?php

namespace App\Services;

use Stripe\StripeClient;

class StripeConnectService
{
    protected StripeClient $client;

    public function __construct(?StripeClient $client = null)
    {
        $this->client = $client ?? new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create a v2 Core Account configured as both a merchant (to accept card
     * payments) and a recipient (to receive Connect transfers) — the v2
     * equivalent of a v1 "Express" connected account.
     *
     * @see https://docs.stripe.com/api/v2/core/accounts/create
     */
    public function createConnectedAccount(string $email, string $shopName): \Stripe\V2\Core\Account
    {
        return $this->client->v2->core->accounts->create([
            'contact_email' => $email,
            'display_name' => $shopName,
            'dashboard' => 'express',
            'defaults' => [
                'responsibilities' => [
                    'fees_collector' => 'application',
                    'losses_collector' => 'application',
                ],
            ],
            'identity' => [
                'country' => 'us',
                'entity_type' => 'company',
                'business_details' => [
                    'registered_name' => $shopName,
                ],
            ],
            'configuration' => [
                'merchant' => [
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                    ],
                ],
                'recipient' => [
                    'capabilities' => [
                        'stripe_balance' => [
                            'stripe_transfers' => ['requested' => true],
                        ],
                    ],
                ],
            ],
            'include' => ['configuration.merchant', 'configuration.recipient', 'identity'],
        ]);
    }

    /**
     * Create a v2 Account Link that sends the connected account to
     * Stripe-hosted onboarding for both its merchant and recipient
     * configurations.
     *
     * @see https://docs.stripe.com/api/v2/core/account-links/create
     */
    public function createOnboardingLink(string $accountId): \Stripe\V2\Core\AccountLink
    {
        return $this->client->v2->core->accountLinks->create([
            'account' => $accountId,
            'use_case' => [
                'type' => 'account_onboarding',
                'account_onboarding' => [
                    'configurations' => ['merchant', 'recipient'],
                    'refresh_url' => config('app.frontend_url').'/vendor/onboarding/refresh',
                    'return_url' => config('app.frontend_url').'/vendor/onboarding/complete',
                ],
            ],
        ]);
    }

    /**
     * Fetch the current state of a v2 Account, including the capability
     * statuses needed to decide whether onboarding is complete.
     *
     * @see https://docs.stripe.com/api/v2/core/accounts/retrieve
     */
    public function fetchAccountStatus(string $accountId): \Stripe\V2\Core\Account
    {
        return $this->client->v2->core->accounts->retrieve($accountId, [
            'include' => ['configuration.merchant', 'configuration.recipient', 'requirements'],
        ]);
    }

    /**
     * Verify and parse an incoming v2 thin-event webhook notification. Thin
     * events carry only a reference to what changed (id, type,
     * related_object) — callers must re-fetch the object's current state
     * rather than trusting a payload snapshot.
     *
     * @see https://docs.stripe.com/webhooks#verify-official-libraries
     * @see https://docs.stripe.com/api/v2/core/events
     */
    public function parseWebhookEvent(string $payload, string $signature)
    {
        return $this->client->parseEventNotification(
            $payload,
            $signature,
            config('services.stripe.webhook_secret')
        );
    }
    /**
     * Determine whether both the merchant (card_payments) and recipient
     * (stripe_transfers) capabilities are active — extracted so both the
     * webhook handler and the on-demand status check use the same logic.
     */
    public function isOnboardingComplete(\Stripe\V2\Core\Account $account): bool
    {
        $merchantActive = $account->configuration?->merchant?->capabilities?->card_payments?->status === 'active';
        $recipientActive = $account->configuration?->recipient?->capabilities?->stripe_balance?->stripe_transfers?->status === 'active';

        return $merchantActive && $recipientActive;
    }
}
