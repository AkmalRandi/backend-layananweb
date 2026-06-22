<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Question;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    public function store(Request $request)
    {
        // 1. Cek token
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'status'  => false,
                'message' => 'Token tidak ditemukan'
            ], 401);
        }

        try {
            $payload = json_decode(base64_decode($token), true);
            if (!$payload || !isset($payload['id']) || !isset($payload['role'])) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Token tidak valid: ' . $e->getMessage()
            ], 401);
        }

        if ($payload['role'] !== 'teacher') {
            return response()->json([
                'status'  => false,
                'message' => 'Hanya teacher yang bisa membuat quiz'
            ], 403);
        }

        $teacherId = $payload['id'];

        // 2. Log data (akan tersimpan di storage/logs/laravel.log)
        Log::info('📥 Data quiz dari frontend:', $request->all());
        Log::info('📥 Files yang diupload:', array_keys($request->files->all()));

        // 3. Validasi
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration'    => 'required|integer|min:1',
            'questions'   => 'required|array|min:1',
            'questions.*.question'       => 'required|string',
            'questions.*.image'          => 'nullable|file|image|max:2048',
            'questions.*.options'        => 'required|array|min:2',
            'questions.*.options.*.text' => 'required|string',
            'questions.*.options.*.image' => 'nullable|file|image|max:2048',
            'questions.*.correct_answer' => 'required|string'
        ]);

        if ($validator->fails()) {
            Log::error('❌ Validasi gagal:', $validator->errors()->toArray());
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 4. Simpan ke database
        DB::beginTransaction();
        try {
            // Buat quiz
            $quiz = Quiz::create([
                'title'       => $request->title,
                'description' => $request->description,
                'duration'    => (int) $request->duration,
                'teacher_id'  => $teacherId
            ]);

            Log::info('✅ Quiz berhasil dibuat, ID: ' . $quiz->id);

            // Proses setiap question
            foreach ($request->questions as $index => $q) {
                $questionImage = null;
                if (isset($q['image']) && $q['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $questionImage = FileHelper::upload($q['image'], 'questions');
                    Log::info("📸 Gambar soal {$index} diupload: " . $questionImage);
                }

                $options = [];
                foreach ($q['options'] as $optIndex => $opt) {
                    $optionData = ['text' => $opt['text']];
                    if (isset($opt['image']) && $opt['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $optionData['image'] = FileHelper::upload($opt['image'], 'options');
                        Log::info("📸 Gambar option {$index}-{$optIndex} diupload: " . $optionData['image']);
                    }
                    $options[] = $optionData;
                }

                Question::create([
                    'quiz_id'        => $quiz->id,
                    'question'       => $q['question'],
                    'image'          => $questionImage,
                    'options'        => json_encode($options),
                    'correct_answer' => $q['correct_answer']
                ]);
            }

            DB::commit();

            // Load ulang dengan questions
            $quiz->load('questions');
            $quiz->questions->each(function ($q) {
                $q->image_url = $q->image ? FileHelper::url($q->image) : null;
                $options = json_decode($q->options, true);
                foreach ($options as &$opt) {
                    if (isset($opt['image'])) {
                        $opt['image_url'] = FileHelper::url($opt['image']);
                    }
                }
                $q->options = $options;
            });

            Log::info('🎉 Quiz berhasil disimpan! ID: ' . $quiz->id);

            return response()->json([
                'status'  => true,
                'message' => 'Quiz berhasil dibuat',
                'data'    => $quiz
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERROR STORE QUIZ: ' . $e->getMessage());
            Log::error('❌ Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'status'  => false,
                'message' => 'Gagal menyimpan quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    // ... metode lainnya (index, show, update, destroy, start, submit, result) tetap sama
}