<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Nicolaslopezj\Searchable\SearchableTrait;


class User extends Authenticatable
{
    use Uuids;
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'name', 'email', 'password', 'verification_code',
    // ];
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    protected $table = 'users';

    use SearchableTrait;

    public function GroupUser()
    {
        return $this->hasMany('App\GroupUser', 'user_id', 'id');
    }
    
    public function Group()
    {
        return $this->belongsToMany('App\Group','groups_users','user_id','group_id');
    }

    public function Profile()
    {
        return $this->hasOne('App\Profile', 'user_id','id');
    }

    public function Role()
    { 
        return $this->belongsTo('App\Role','role_id','id');
    }

    public function Secret()
    { 
        return $this->hasMany('App\Secret','owner_id','id');
    }
    
    public function Share()
    { 
        return $this->hasMany('App\Share','user_id','id');
    }
    
    public function PGPkey()
    { 
        return $this->hasMany('App\PGPkey','owner_id','id');
    }
    
    public function Account()
    {
        return $this->belongsToMany('App\Account','secrets','owner_id','asset_id');
    }
    
    public function Note()
    {
        return $this->belongsToMany('App\Note','secrets','owner_id','asset_id');
    }
    
    public function Track()
    { 
        return $this->hasMany('App\AssetTracking','user_id','id');
    }

    public function isAdmin()
    {
        $role = auth()->user()->role_id;
        if( $role == "5bdf5220-d75c-11e8-843b-a7f6cbee423d" || $role == "5bed2760-d75c-11e8-8098-a930bf45516a") {
            return true;
        }
        return false;
    }
}
