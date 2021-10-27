<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class paperMatesOrder extends Model
{
    use HasFactory;
    protected $table = "papermatesorder";
    protected $primaryKey = 'orderId';
    protected $fillable = ['orderId','pages','userEmail','Papercategory','price','AcademicLevel','Deadline','paperTopic','paperDescription','userId','ReferenceNo','writerId'];
    protected $hidden=['created_at','updated_at','is_Active'];

}
