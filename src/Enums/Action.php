<?php

namespace DK\MerchantSuite\Enums;

enum Action: string
{
    case Payment = 'Payment';
    case Refund = 'Refund';
    case UnmatchedRefund = 'UnmatchedRefund';
    case PreAuth = 'PreAuth';
    case Capture = 'Capture';
    case Reversal = 'Reversal';
    case VerifyOnly = 'VerifyOnly';
    case Unspecified = 'Unspecified';

    /**
     * Actions that act on an earlier transaction and need its txnNumber.
     */
    public function needsOriginalTransaction(): bool
    {
        return in_array($this, [self::Refund, self::Capture, self::Reversal], true);
    }
}
