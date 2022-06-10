<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpView extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['ip_address', 'article_id', 'created_at'];
}
