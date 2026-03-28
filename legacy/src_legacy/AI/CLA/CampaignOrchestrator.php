<?php

declare(strict_types=1);

namespace App\Component\Product\AI\CLA;

use App\Component\Product\AI\CLA\Channel\EmailChannel;
use App\Component\Product\AI\CLA\Channel\WebhookChannel;

final class CampaignOrchestrator
{
    public function __construct(
        private readonly EmailChannel $email,
        private readonly WebhookChannel $webhook,
    ) {
    }

    /**
     * @param array{stage:string,churn:float,locale?:string,email?:string,webhook?:string} $ctx
     *
     * @return array{campaign:string,channel:string,payload:array<string,mixed>}
     */
    public function choose(array $ctx): array
    {
        $stage = $ctx['stage'];
        $churn = $ctx['churn'];
        $locale = $ctx['locale'] ?? 'en';

        if ('new' === $stage) {
            $payload = ['template' => 'onboarding', 'locale' => $locale];

            return ['campaign' => 'onboarding', 'channel' => 'email', 'payload' => $payload];
        }
        if ('at_risk' === $stage || $churn >= 0.7) {
            $payload = ['template' => 'winback10', 'locale' => $locale, 'discount_pct' => 10];

            return ['campaign' => 'winback', 'channel' => 'email', 'payload' => $payload];
        }
        if ('lost' === $stage) {
            $payload = ['event' => 'reactivation', 'bonus_cents' => 1000];

            return ['campaign' => 'reactivation', 'channel' => 'webhook', 'payload' => $payload];
        }

        return ['campaign' => 'none', 'channel' => 'none', 'payload' => []];
    }

    /** Выполнить доставку по выбранному каналу. */
    public function deliver(string $channel, array $payload, array $ctx): array
    {
        return match ($channel) {
            'email' => $this->email->send($ctx['email'] ?? '', $payload['template'] ?? 'generic', $payload),
            'webhook' => $this->webhook->post($ctx['webhook'] ?? '', $payload),
            default => ['ok' => true, 'details' => 'no-op'],
        };
    }
}
