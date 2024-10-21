<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FundAlias extends Model
{
    use HasFactory;
    
    protected $fillable = ['fund_id', 'name'];

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }
}
