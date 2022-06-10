<?php

namespace App\Models;

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
