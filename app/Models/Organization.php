<?php

namespace App\Models;

use Amedia\Platform\Packages\Catalogue\Models\Organization as BaseOrganization;
use App\Services\SlugService;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string $logo
 * @property string $description
 * @property int $status
 * @property string $address
 * @property int $number_employees
 * @property string $phone
 * @property string $tariff
 * @property string $email_domain
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Organization extends BaseOrganization
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'logo',
        'description',
        'status',
        'address',
        'number_employees',
        'phone',
        'tariff',
        'email_domain',
    ];

    protected static function booted(): void
    {
        static::creating(function (Organization $organization): void {
            $organization->slug = (new SlugService())->generateSlug($organization->name, Organization::class);
        });
    }
}
