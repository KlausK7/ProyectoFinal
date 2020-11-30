<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';
            
    // Relacion muchos a uno
            
    public function user(){
        return $this->belongsTo('App\User','user_id');
    }
    
    public function category(){
        return $this->belongsTo('App\Category','category_id');
    }
     protected $fillable = [
        'title', 'content' , 'category_id', 'image'
    ];

}

