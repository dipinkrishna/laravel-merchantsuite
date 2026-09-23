<?php

namespace DK\MerchantSuite\Enums;

enum TransactionType: string
{
    case Internet = 'Internet';
    case ECommerce = 'ECommerce';
    case CallCentre = 'CallCentre';
    case CardPresent = 'CardPresent';
    case Ivr = 'Ivr';
    case MailOrder = 'MailOrder';
    case TelephoneOrder = 'TelephoneOrder';
    case Unspecified = 'Unspecified';
}
