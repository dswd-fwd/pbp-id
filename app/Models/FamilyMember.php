<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    public function currentSkill()
    {
        return $this->belongsTo(CodeSkill::class, 'code_skill_current_id');
    }
    
    public function acquireSkill()
    {
        return $this->belongsTo(CodeSkill::class, 'code_skill_acquire_id');
    }
}
