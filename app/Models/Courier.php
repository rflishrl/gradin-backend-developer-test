<?php

namespace App\Models;

use App\Enums\VehicleType;
use Database\Factories\CourierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    /** @use HasFactory<CourierFactory> */
    use HasFactory;

    public const MIN_LEVEL = 1;

    public const MAX_LEVEL = 5;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'vehicle_type',
        'license_plate',
        'level',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Match every word of the search text against the name, in any order.
     * "budi agung" matches "Budiono Hadi Agung".
     *
     * @param  Builder<Courier>  $query
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $terms = preg_split('/\s+/', trim((string) $search), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($terms as $term) {
            $escaped = addcslashes($term, '\\%_');
            $query->where('name', 'like', "%{$escaped}%");
        }
    }

    /**
     * @param  Builder<Courier>  $query
     * @param  array<int, int>  $levels
     */
    public function scopeWithLevels(Builder $query, array $levels): void
    {
        if ($levels !== []) {
            $query->whereIn('level', $levels);
        }
    }
}
