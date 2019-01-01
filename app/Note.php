<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Note extends Model
{
    use Uuids;
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $table = 'notes';

    use SearchableTrait;

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'title' => 10,
        ],
    ];

    public function Secret()
    {
        return $this->hasOne('App\Secret', 'asset_id','id');
    }
    
    public function Share()
    {
        return $this->hasOne('App\Share', 'asset_id','id');
    }
    
    public function User()
    {
        return $this->belongsToMany('App\User','secrets','asset_id','owner_id');
    }

    public function Group()
    {
        return $this->belongsToMany('App\Group','secrets','asset_id','owner_id');
    }
}

