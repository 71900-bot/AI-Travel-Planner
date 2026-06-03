<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use SoftDeletes;

    public function user()
    {
        return $this->belongsTo(User::class);
    }



    protected $fillable = [
        'user_id',
        'from_city',
        'to_city',
        'duration',
        'travelers',
        'budget',
        'itinerary_name',
        'itinerary',
    ];

    protected $casts = [
        'duration' => 'integer',
        'travelers' => 'integer',
        // NOTE: budget can contain non-numeric values in existing DB rows.
        // We parse it safely in an accessor to avoid MathException on the history page.
        // 'budget' => 'decimal:2',
        // Stored as a plain text response from OpenAI
        'itinerary' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getBudgetAttribute($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return number_format((float) $value, 2, '.', '');
        }

        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }

        // Remove common currency formatting (e.g. "$1,234.50", "1 234,50")
        $str = str_replace(['$', '€', '£', '¥'], '', $str);
        $str = str_replace([',', ' '], '', $str);

        // Keep digits, optional minus, and at most one dot.
        if (!preg_match('/^-?\d*(?:\.\d+)?$/', $str)) {
            return null;
        }

        $num = (float) $str;
        return number_format($num, 2, '.', '');
    }


    protected $hidden = [
        'deleted_at'
    ];

    public function scopeFromCity($query, $city)
    {
        return $query->where('from_city', 'like', "%{$city}%");
    }

    public function scopeToCity($query, $city)
    {
        return $query->where('to_city', 'like', "%{$city}%");
    }
}
