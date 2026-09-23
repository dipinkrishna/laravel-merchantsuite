<?php

namespace DK\MerchantSuite\Exceptions;

class ConfigurationException extends MerchantSuiteException
{
    public static function missing(string $key): self
    {
        return new self("MerchantSuite is not configured: merchantsuite.{$key} is empty.");
    }
}
