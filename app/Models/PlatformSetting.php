<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = ['platform_name', 'logo_path', 'timezone', 'settings'];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }
}
