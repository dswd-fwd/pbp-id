<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyProfile extends Model
{
    protected $guarded = [];

    protected $table = 'family_profiles';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
