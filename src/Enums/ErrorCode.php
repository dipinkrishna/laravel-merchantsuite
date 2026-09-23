<?php

namespace DK\MerchantSuite\Enums;

enum ErrorCode: string
{
    case Fatal = 'Fatal';
    case SystemDown = 'SystemDown';
    case InvalidFields = 'InvalidFields';
    case InvalidCredentials = 'InvalidCredentials';
    case NotAuthenticated = 'NotAuthenticated';
    case InvalidPermissions = 'InvalidPermissions';
    case InvalidPayload = 'InvalidPayload';
    case InvalidFlow = 'InvalidFlow';
    case NotFound = 'NotFound';
    case Unspecified = 'Unspecified';
}
