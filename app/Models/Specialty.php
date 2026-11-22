<?php

namespace App\Models;

use App\Core\Model;

class Specialty extends Model
{
    protected string $table = 'specialties';

    public static function allActive(): array
    {
        return static::all('name ASC');
    }
}
