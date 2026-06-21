<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'full_name', 'username', 'email', 'role', 'password'
    ];

    protected $hidden = [
        'password'
    ];

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'teacher_id');
    }
}