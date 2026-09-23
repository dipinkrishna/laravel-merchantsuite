<?php

namespace DK\MerchantSuite\Enums;

enum CardScheme: string
{
    case Visa = 'Visa';
    case Mastercard = 'Mastercard';
    case Amex = 'Amex';
    case Diners = 'Diners';
    case Jcb = 'Jcb';
    case ChinaUnionPay = 'ChinaUnionPay';
    case DirectEntry = 'DirectEntry';
    case Unspecified = 'Unspecified';
}
