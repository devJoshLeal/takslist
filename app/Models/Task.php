<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table ="tasks";

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'attach_pdf',
        'attach_image'
    ];

     // Definición de la relación con el modelo User
     public function user()
     {
         return $this->belongsTo(User::class);
     }
}
