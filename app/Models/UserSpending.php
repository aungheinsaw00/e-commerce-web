<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSpending extends Model
{
    public $timestamps = false;

    const TIER_BRONZE = 'bronze';
    const TIER_SILVER = 'silver';
    const TIER_GOLD   = 'gold';

    // Bronze: 0–1000, Silver: 1000–5000, Gold: 5000+
    const SILVER_THRESHOLD = 1000.00;
    const GOLD_THRESHOLD   = 5000.00;

    protected $table = 'user_spending';

    protected $fillable = ['user_id', 'total_spent', 'tier'];

    protected function casts(): array
    {
        return [
            'total_spent' => 'decimal:2',
            'updated_at'  => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Compute the member tier based on total spending.
     */
    public static function computeTier(float $totalSpent): string
    {
        if ($totalSpent >= self::GOLD_THRESHOLD) {
            return self::TIER_GOLD;
        }

        if ($totalSpent >= self::SILVER_THRESHOLD) {
            return self::TIER_SILVER;
        }

        return self::TIER_BRONZE;
    }
}
