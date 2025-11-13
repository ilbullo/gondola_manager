<?php

namespace App\Enums;

enum LicenseType: string
{
    case OWNER = 'titolare';
    case SUBSTITUTe = 'sostituto';
}
