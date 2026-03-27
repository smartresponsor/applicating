<?php

declare(strict_types=1);

namespace App\Component\Product\Billing\Controller;

use App\Component\Product\Billing\InvoiceGenerator;
use App\Component\Product\Billing\LimitEnforcer;
use App\Component\Product\Billing\Payment\CryptoWallet;
use App\Component\Product\Billing\Payment\PayPalClient;
use App\Component\Product\Billing\Payment\StripeClient;
use App\Component\Product\Billing\PricingRules;
use App\Component\Product\Billing\UsageTracker;
use App\Component\Product\Billing\WalletService;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class BillingAPI
{
    public function __construct(
        private readonly Connection $db,
        private readonly UsageTracker $usage,
        private readonly PricingRules $pricing,
        private readonly WalletService $wallet,
        private readonly InvoiceGenerator $invoice,
        private readonly LimitEnforcer $limits,
        private readonly StripeClient $stripe,
        private readonly PayPalClient $paypal,
        private readonly CryptoWallet $crypto,
    ) {
    }

    #[Route('/api/billing/track', methods: ['POST'])]
    public function track(Request $req): JsonResponse
    {
        $t = (string) ($req->get('tenant') ?? 'tenantA');
        $f = (string) ($req->get('feature') ?? 'llm.call');
        $q = (int) ($req->get('quantity') ?? 1);
        $price = $this->pricing->unitPrice($f);
        $this->usage->track($t, $f, $q, $price, gmdate('c'));

        return new JsonResponse(['ok' => true, 'charged' => $price * $q]);
    }

    #[Route('/api/billing/balance', methods: ['GET'])]
    public function balance(Request $req): JsonResponse
    {
        $t = (string) ($req->get('tenant') ?? 'tenantA');
        $bal = $this->wallet->getBalance($t);

        return new JsonResponse(['tenant' => $t, 'balance_usd' => $bal]);
    }

    #[Route('/api/billing/topup/stripe', methods: ['POST'])]
    public function topupStripe(Request $req): JsonResponse
    {
        $t = (string) ($req->get('tenant') ?? 'tenantA');
        $amt = (float) ($req->get('amount') ?? 20.0);
        $checkout = $this->stripe->createCheckout($t, $amt);
        // имитируем успешную оплату
        $this->wallet->topup($t, $amt, 'stripe');

        return new JsonResponse(['ok' => true, 'checkout' => $checkout]);
    }

    #[Route('/api/billing/topup/paypal', methods: ['POST'])]
    public function topupPaypal(Request $req): JsonResponse
    {
        $t = (string) ($req->get('tenant') ?? 'tenantA');
        $amt = (float) ($req->get('amount') ?? 20.0);
        $checkout = $this->paypal->createPayment($t, $amt);
        $this->wallet->topup($t, $amt, 'paypal');

        return new JsonResponse(['ok' => true, 'approval' => $checkout]);
    }

    #[Route('/api/billing/topup/crypto', methods: ['POST'])]
    public function topupCrypto(Request $req): JsonResponse
    {
        $t = (string) ($req->get('tenant') ?? 'tenantA');
        $asset = (string) ($req->get('asset') ?? 'USDT');
        $addr = $this->crypto->depositAddress($t, $asset);
        // в реальности — ожидание поступления, здесь имитируем моментальный зачет $25
        $this->wallet->topup($t, 25.0, 'crypto:'.$asset);

        return new JsonResponse(['ok' => true, 'deposit' => $addr, 'credited_usd' => 25.0]);
    }

    #[Route('/api/billing/invoice', methods: ['POST'])]
    public function createInvoice(Request $req): JsonResponse
    {
        $t = (string) ($req->get('tenant') ?? 'tenantA');
        $period = (string) ($req->get('period') ?? gmdate('Y-m'));
        $id = $this->invoice->generate($t, $period);

        return new JsonResponse(['ok' => true, 'invoice_id' => $id]);
    }
}
