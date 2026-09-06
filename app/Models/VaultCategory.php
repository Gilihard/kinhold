<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaultCategory extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'family_id',
        'name',
        'slug',
        'icon',
        'description',
        'sort_order',
    ];

    /**
     * VaultCategory belongs to a Family.
     */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * VaultCategory has many VaultEntries.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(VaultEntry::class);
    }

    /**
     * Default vault categories.
     *
     * @return array<string, array<string, string>>
     */
    public static function defaultCategories(): array
    {
        return [
            'medical' => [
                'name' => 'Медицина',
                'slug' => 'medical',
                'icon' => 'heart',
                'description' => 'Медицинские записи и информация о здоровье',
            ],
            'financial' => [
                'name' => 'Финансы',
                'slug' => 'financial',
                'icon' => 'dollar-sign',
                'description' => 'Финансовые счета и информация',
            ],
            'insurance' => [
                'name' => 'Страхование',
                'slug' => 'insurance',
                'icon' => 'shield',
                'description' => 'Страховые полисы и документы',
            ],
            'legal' => [
                'name' => 'Юридическое',
                'slug' => 'legal',
                'icon' => 'briefcase',
                'description' => 'Юридические документы и соглашения',
            ],
            'education' => [
                'name' => 'Образование',
                'slug' => 'education',
                'icon' => 'book',
                'description' => 'Образовательные документы и справки',
            ],
            'personal' => [
                'name' => 'Личное',
                'slug' => 'personal',
                'icon' => 'lock',
                'description' => 'Личные документы и информация',
            ],
        ];
    }
}
