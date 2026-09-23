<?php

namespace DK\MerchantSuite\Enums;

enum TokenisationMode: string
{
    /** Use the biller's configured behaviour. */
    case Default = 'Default';

    /** Never create a token. */
    case None = 'None';

    /** Create a token only if the cardholder opts in (storeCard). */
    case OptIn = 'OptIn';

    /** Always create a token. */
    case All = 'All';
}
