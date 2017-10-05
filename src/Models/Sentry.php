<?php

namespace EETechMedia\Sentry\Models;

use Illuminate\Database\Eloquent\Model;

class Sentry extends Model
{

    /**
     * Mass assignable fields
     *
     * @var array
     * @author goper
     */
    protected $fillable = [
        'base_id',
        'user_id',
        'url',
        'ip',
        'details',
    ];
}
