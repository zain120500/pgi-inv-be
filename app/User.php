<?php
namespace App;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

//    protected $fillable = [
//        'name', 'email', 'password',
//    ];

    protected $guarded = [];

    protected $hidden = [
        'password', 'remember_token','created_at', 'updated_at','email_verified_at'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function admin()
    {
        return $this->hasOne('App\Model\Admin');
    }

    public function devisi()
    {
        return $this->hasOne('App\Model\devisi', 'DivisiID', 'devisi_id');
    }

    public function role()
    {
        return $this->belongsTo('App\Model\Role');
    }
}
