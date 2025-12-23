<?php

namespace App\Models;

use App\Imports\Excel\MerchantImport;
use App\Services\SlugService;
use App\Traits\HasLocalizedFields;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

/**
 * @property int $id
 * @property string $slug
 * @property string $name_ru
 * @property string $name_uz
 * @property string $logo
 * @property string $preview
 * @property array $images
 * @property string $address_ru
 * @property string $address_uz
 * @property string $latitude
 * @property string $longitude
 * @property string $label_ru
 * @property string $label_uz
 * @property string $excerpt_ru
 * @property string $excerpt_uz
 * @property string $description_ru
 * @property string $description_uz
 * @property string $contact_name_ru
 * @property string $contact_name_uz
 * @property string $contact_phone
 * @property string $additional_contact_phone
 * @property int $user_id
 * @property int $has_detail_page
 * @property int $sort Сортировка в общем каталоге
 * @property int $sort_category Сортировка в каталоге категории
 * @property int $clinic_id ID клиники в Med24. Нужно для передачи сертификатов
 * @property string $chat_id Chat ID группы с клиникой из Med24
 * @property string $phone Телефон заведения
 * @property string $additional_phone Дополнительный телефон заведения
 * @property string $type Тип заведения (Обычное или сеть)
 * @property string $work_time_ru
 * @property string $work_time_uz
 * @property int $location_id
 * @property string $about_ru
 * @property string $about_uz
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property User $user
 * @property Location $location
 * @property MerchantSocial $socials
 * @property Collection<int, MerchantBranch> $branches
 * @property Collection<int, MerchantOffer> $offers
 * @property Collection<int, MerchantInstagramPost> $posts
 * @property Collection<int, MerchantCategory> $categories
 * @property Collection<int, Tag> $tags
 */
class Merchant extends Model
{
    protected $fillable = [
        'slug',
        'name_ru',
        'name_uz',
        'logo',
        'preview',
        'images',
        'address_ru',
        'address_uz',
        'latitude',
        'longitude',
        'label_ru',
        'label_uz',
        'excerpt_ru',
        'excerpt_uz',
        'description_ru',
        'description_uz',
        'contact_name_ru',
        'contact_name_uz',
        'contact_phone',
        'additional_contact_phone',
        'user_id',
        'has_detail_page',
        'sort',
        'sort_category',
        'clinic_id',
        'chat_id',
        'phone',
        'additional_phone',
        'type',
        'work_time_ru',
        'work_time_uz',
        'location_id',
        'about_ru',
        'about_uz',
    ];

    protected $casts = [
        'images' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
