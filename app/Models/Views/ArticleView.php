<?php

namespace App\Models\Views;

use App\Models\Article;
use Illuminate\Database\Eloquent\Model;

class ArticleView extends Model
{
    public $timestamps = false;

    protected $fillable = ['date', 'count'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
