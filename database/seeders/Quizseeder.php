<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Question;

class QuizSeeder extends Seeder
{
    public function run()
    {
        $quiz1 = Quiz::create([
            'title' => 'Quiz Matematika Dasar',
            'description' => 'Uji kemampuan matematika dasar Anda',
            'duration' => 15,
            'teacher_id' => 1,
        ]);

        Question::create([
            'quiz_id' => $quiz1->id,
            'question' => 'Berapakah hasil dari 2 + 2?',
            'options' => json_encode(['2', '3', '4', '5']),
            'correct_answer' => '4'
        ]);

        Question::create([
            'quiz_id' => $quiz1->id,
            'question' => 'Berapakah hasil dari 5 x 5?',
            'options' => json_encode(['20', '25', '30', '35']),
            'correct_answer' => '25'
        ]);

        Question::create([
            'quiz_id' => $quiz1->id,
            'question' => 'Berapakah hasil dari 10 / 2?',
            'options' => json_encode(['2', '3', '5', '7']),
            'correct_answer' => '5'
        ]);

        $quiz2 = Quiz::create([
            'title' => 'Quiz Sejarah Indonesia',
            'description' => 'Pelajari sejarah bangsa Indonesia',
            'duration' => 12,
            'teacher_id' => 1,
        ]);

        Question::create([
            'quiz_id' => $quiz2->id,
            'question' => 'Siapa presiden pertama Indonesia?',
            'options' => json_encode(['Soekarno', 'Soeharto', 'Habibie', 'Gus Dur']),
            'correct_answer' => 'Soekarno'
        ]);

        Question::create([
            'quiz_id' => $quiz2->id,
            'question' => 'Kapan Indonesia merdeka?',
            'options' => json_encode(['1945', '1946', '1947', '1948']),
            'correct_answer' => '1945'
        ]);

        $this->command->info('Quiz Seeder berhasil!');
    }
}