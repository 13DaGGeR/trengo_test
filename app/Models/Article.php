<?php

namespace App\Models;

use App\Models\Rating\Rating;
use App\Models\Search\ArticleIndex\Indexer;
use App\Models\Views\ArticleView;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'body', 'rating', 'rating_score'];

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

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function getTotalViews(): int
    {
        return (int)$this->views()->sum('count');
    }

    protected function finishSave(array $options)
    {
        parent::finishSave($options);
        (new Indexer())->indexArticle($this);
    }
}
