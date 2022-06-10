<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'body', 'rating_score'];

    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'articles_to_categories'
        );
    }
}