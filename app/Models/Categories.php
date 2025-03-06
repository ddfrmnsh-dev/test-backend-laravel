<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description'
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'category_on_post', 'category_id', 'post_id');
    }
}
