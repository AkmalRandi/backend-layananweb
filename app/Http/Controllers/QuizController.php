<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    // 🔥 Ambil semua quiz (untuk siswa & teacher)
    public function index()
    {
        $quizzes = Quiz::withCount('questions')->get();
        return response()->json([
            'status'  => true,
            'message' => 'Data quizzes berhasil diambil',
            'data'    => $quizzes
        ]);
    }

    public function show($id)
    {
        $quiz = Quiz::with('questions')->find($id);
        if (!$quiz) {
            return response()->json([
                'status'  => false,
                'message' => 'Quiz tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'status'  => true,
            'message' => 'Data quiz berhasil diambil',
            'data'    => $quiz
        ]);
    }

    // 🔥 TEACHER CREATE QUIZ (dengan otorisasi)
    public function store(Request $request)
    {
        // 1. Validasi token
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'status'  => false,
                'message' => 'Token tidak ditemukan'
            ], 401);
        }

        // 2. Decode token
        $payload = json_decode(base64_decode($token), true);
        if (!$payload || !isset($payload['id']) || !isset($payload['role'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Token tidak valid'
            ], 401);
        }

        // 3. Cek role teacher
        if ($payload['role'] !== 'teacher') {
            return response()->json([
                'status'  => false,
                'message' => 'Hanya teacher yang bisa membuat quiz'
            ], 403);
        }

        $teacherId = $payload['id'];

        // 4. Validasi input
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration'    => 'required|integer|min:1',
            'questions'   => 'required|array|min:1',
            'questions.*.question'       => 'required|string',
            'questions.*.options'        => 'required|array|min:2',
            'questions.*.correct_answer' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 5. Simpan quiz
        DB::beginTransaction();
        try {
            $quiz = Quiz::create([
                'title'       => $request->title,
                'description' => $request->description,
                'duration'    => $request->duration,
                'teacher_id'  => $teacherId
            ]);

            foreach ($request->questions as $q) {
                Question::create([
                    'quiz_id'        => $quiz->id,
                    'question'       => $q['question'],
                    'options'        => json_encode($q['options']),
                    'correct_answer' => $q['correct_answer']
                ]);
            }

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Quiz berhasil dibuat',
                'data'    => $quiz->load('questions')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal membuat quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) {
            return response()->json([
                'status'  => false,
                'message' => 'Quiz tidak ditemukan'
            ], 404);
        }
        $quiz->update($request->only(['title', 'description', 'duration']));
        return response()->json([
            'status'  => true,
            'message' => 'Quiz berhasil diupdate',
            'data'    => $quiz
        ]);
    }

    public function destroy($id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) {
            return response()->json([
                'status'  => false,
                'message' => 'Quiz tidak ditemukan'
            ], 404);
        }
        $quiz->questions()->delete();
        $quiz->delete();
        return response()->json([
            'status'  => true,
            'message' => 'Quiz berhasil dihapus'
        ]);
    }

    // ============ PLAY QUIZ ============

    public function start($id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) {
            return response()->json([
                'status'  => false,
                'message' => 'Quiz tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'status'  => true,
            'message' => 'Quiz dimulai',
            'data'    => [
                'duration'         => $quiz->duration,
                'total_questions'  => $quiz->questions()->count()
            ]
        ]);
    }

    public function submit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|integer',
            'answer'      => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $question = Question::where('quiz_id', $id)
                            ->where('id', $request->question_id)
                            ->first();

        if (!$question) {
            return response()->json([
                'status'  => false,
                'message' => 'Pertanyaan tidak ditemukan'
            ], 404);
        }

        $isCorrect = $request->answer === $question->correct_answer;

        return response()->json([
            'status'  => true,
            'message' => 'Jawaban berhasil dikirim',
            'data'    => [
                'is_correct'      => $isCorrect,
                'correct_answer'  => $question->correct_answer
            ]
        ]);
    }

    public function result($id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) {
            return response()->json([
                'status'  => false,
                'message' => 'Quiz tidak ditemukan'
            ], 404);
        }

        $totalQuestions = $quiz->questions()->count();
        $correctAnswers = rand(0, $totalQuestions); // Demo

        return response()->json([
            'status'  => true,
            'message' => 'Hasil quiz',
            'data'    => [
                'score'   => $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0,
                'correct' => $correctAnswers,
                'total'   => $totalQuestions,
                'answers' => []
            ]
        ]);
    }
}