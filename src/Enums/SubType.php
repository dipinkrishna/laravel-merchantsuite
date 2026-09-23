<?php

namespace DK\MerchantSuite\Enums;

enum SubType: string
{
    case Single = 'Single';
    case Recurring = 'Recurring';
    case Unspecified = 'Unspecified';
}
