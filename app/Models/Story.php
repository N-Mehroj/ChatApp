<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

/**
 * @property int $id
 * @property string $name
 * @property array $files
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Collection<int, User> $users
 */
class Story extends Model
{
    protected $fillable = [
        'name',
        'files',
    ];

    protected $casts = [
        'files' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Story $story): void {
            foreach ($story->files as $file) {
                if (File::exists(public_path('uploads/' . $file))) {
                    File::delete(public_path('uploads/' . $file));
                }
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
