<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Fund extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'start_year', 'fund_manager_id'];

    public function fundManager()
    {
        return $this->belongsTo(FundManager::class);
    }

    public function fundAliases()
    {
        return $this->hasMany(FundAlias::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    ////////////////////////////////////////////////////
    ////////////////// SCOPES /////////////////////////
    //////////////////////////////////////////////////  

    /**
     * Scope a query to filter funds by name or aliases.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByName(Builder $query, $name)
    {
        if (!empty($name)) {
            $query->where(function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%')
                    ->orWhereHas('fundAliases', function ($q2) use ($name) {
                        $q2->where('name', 'like', '%' . $name . '%');
                    });
            });
        }

        return $query;
    }

    /**
     * Scope a query to filter funds by fund manager name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $fundManagerName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByFundManager(Builder $query, $fundManagerName)
    {
        if (!empty($fundManagerName)) {
            $query->where('fund_manager_id', $fundManagerName);
        }

        return $query;
    }

    /**
     * Scope a query to filter funds by start year.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $startYear
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByStartYear(Builder $query, $startYear)
    {
        if (!empty($startYear)) {
            $query->where('start_year', $startYear);
        }

        return $query;
    }
}
