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

    // Relasi ke tabel option_images
    public function optionImages()
    {
        return $this->hasMany(OptionImage::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? FileHelper::url($this->image) : null;
    }

    // Menggabungkan options dengan gambar dari tabel terpisah
    public function getOptionsWithImagesAttribute()
    {
        $options = $this->options ?? [];
        $images = $this->optionImages->keyBy('option_index');

        foreach ($options as $index => &$opt) {
            if (isset($images[$index])) {
                $opt['image_url'] = FileHelper::url($images[$index]->image_path);
            }
        }

        return $options;
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}