<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case BANCALE = 'bancale';
    case USER = 'user';
}
