<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class paperMatesReview extends Model
{
    use HasFactory;
    protected $table = "papermatesreview";
    protected $primaryKey = 'reviewId ';
    protected $fillable = ['reviewId','reviews','userId','reply'];

}
