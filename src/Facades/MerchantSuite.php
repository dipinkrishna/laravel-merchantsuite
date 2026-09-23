<?php

namespace DK\MerchantSuite\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \DK\MerchantSuite\Resources\Transactions transactions()
 * @method static \DK\MerchantSuite\Resources\Tokens tokens()
 * @method static \DK\MerchantSuite\Resources\Checkout checkout()
 * @method static \DK\MerchantSuite\Webhooks\Webhooks webhooks()
 * @method static \DK\MerchantSuite\Data\Transaction chargeToken(string $token, \DK\MerchantSuite\Data\TransactionDetails $details, ?\DK\MerchantSuite\Data\Expiry $expiry = null, ?string $webhookUrl = null)
 *
 * @see \DK\MerchantSuite\MerchantSuite
 */
class MerchantSuite extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \DK\MerchantSuite\MerchantSuite::class;
    }
}
