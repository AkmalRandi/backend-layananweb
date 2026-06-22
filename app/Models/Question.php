<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\FileHelper;

class Question extends Model
{
    protected $fillable = [
        'quiz_id', 'question', 'image', 'options', 'correct_answer'
    ];

    protected $casts = [
        'options' => 'array'
    ];

    public function getImageUrlAttribute()
    {
        return $this->image ? FileHelper::url($this->image) : null;
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}