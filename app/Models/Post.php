<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'is_featured',
        'status',
        'published_at',
        'author_id',
        'seo_title',
        'seo_desc',
        'meta_keywords',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Categories::class, 'category_on_post', 'post_id', 'category_id');
    }
}
