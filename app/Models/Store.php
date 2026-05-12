<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'branch_code',
        'address',
        'timezone',
        'currency',
    ];
}
