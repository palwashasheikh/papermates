<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class paperMatesAcademiclevel extends Model
{
    use HasFactory;
    protected $table = "papermatesacademiclevel";
    protected $primaryKey = 'AcademicLevelId';
    protected $fillable = ['AcademicLevelName','price'];
    protected $hidden=['created_at','updated_at','is_Active'];

}
