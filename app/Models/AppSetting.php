<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'app_name',
        'app_version',
        'foundation_name',
        'app_email',
        'app_logo_path',
        'app_description',
    ];
}
