<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $hint
 * @property bool $collects_on_delivery
 * @property bool $is_active
 * @property int $sort
 */
#[Fillable([
    'code',
    'name',
    'hint',
    'collects_on_delivery',
    'is_active',
    'sort',
])]
class PaymentMethod extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name', 'hint'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'collects_on_delivery' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function displayName(): string
    {
        return (string) $this->name;
    }

    public function displayHint(): string
    {
        return (string) $this->hint;
    }
}
