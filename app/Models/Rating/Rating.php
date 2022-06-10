<?php

namespace App\Models\Rating;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['ip_address', 'value'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
