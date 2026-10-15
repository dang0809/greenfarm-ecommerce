<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case FARM_STAFF = 'farm_staff';
    case SALES = 'sales';
    case CUSTOMER = 'customer';
}