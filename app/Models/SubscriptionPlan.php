<?php

namespace App\Models;

use Database\Factories\SubscriptionPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    /** @use HasFactory<SubscriptionPlanFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'price_paise', 'billing_interval', 'features', 'support_months', 'ai_level', 'is_active'];

    protected function casts(): array
    {
        return ['features' => 'array', 'is_active' => 'boolean', 'price_paise' => 'integer'];
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }
}
