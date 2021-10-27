<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class paperMatesUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = "papermatesuser";
    protected $primaryKey = 'UserId';


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['UserId','username','userEmail','UserPhone','userpassword','AcademicLevel','email_verified_at','role'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'userpassword',
        'verification_code',
        'updated_at',
        'created_at'
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    public function role()
    {
        return $this->belongsTo(paperMatesRole::class);
    }
}
