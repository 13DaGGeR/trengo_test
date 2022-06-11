<?php

namespace App\Models;

use App\Models\Views\ArticleView;
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

    public function views()
    {
        return $this->hasMany(ArticleView::class);
    }

    public function getTotalViews(): int {
        return (int)$this->views()->sum('count');
    }
}
