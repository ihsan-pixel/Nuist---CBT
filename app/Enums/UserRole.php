<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Panitia = 'panitia';
    case Peserta = 'peserta';
}
