<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $table = 'categories';

    public function videos()
    {
        return $this->hasMany(Video::class, 'category_id', 'id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'category_id', 'id');
    }

}
