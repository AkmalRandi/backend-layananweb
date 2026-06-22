<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionImage extends Model
{
    protected $fillable = [
        'question_id', 'option_index', 'image_path'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}