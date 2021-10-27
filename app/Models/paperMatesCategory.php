<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class paperMatesCategory extends Model
{
    use HasFactory;
    protected $table = "papermatescategories";
    protected $primaryKey = 'orderId';
    protected $fillable = ['papercategoriesName','AcademicLevel'];
    protected $hidden=['created_at','updated_at','is_Active'];

}
