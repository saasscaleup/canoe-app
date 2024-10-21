<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FundManager extends Model
{
    use HasFactory;
    
    protected $fillable = ['name'];

    public function funds()
    {
        return $this->hasMany(Fund::class);
    }
}
