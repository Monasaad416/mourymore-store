<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email
 * @property string|null $name
 * @property string|null $locale
 */
#[Fillable(['email', 'name', 'locale'])]
class Subscription extends Model {}
