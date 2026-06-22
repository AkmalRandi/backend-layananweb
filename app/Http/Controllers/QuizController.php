<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\OptionImage;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    public function index()
    {
        try {
            $quizzes = Quiz::withCount('questions')->get();
            return response()->json([
                'status'  => true,
                'message' => 'Data quizzes berhasil diambil',
                'data'    => $quizzes
            ]);
        } catch (\Exception $e) {
            Log::error('Index quiz error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil data quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $quiz = Quiz::with(['questions.optionImages'])->find($id);
            if (!$quiz) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Quiz tidak ditemukan'
                ], 404);
            }

            $quiz->questions->each(function ($q) {
                $q->image_url = $q->image ? FileHelper::url($q->image) : null;
                $options = json_decode($q->options, true);
                $images = $q->optionImages->keyBy('option_index');

                foreach ($options as $index => &$opt) {
                    if (isset($images[$index])) {
                        $opt['image_url'] = FileHelper::url($images[$index]->image_path);
                    }
                }
                $q->options = $options;
            });

            return response()->json([
                'status'  => true,
                'message' => 'Data quiz berhasil diambil',
                'data'    => $quiz
            ]);
        } catch (\Exception $e) {
            Log::error('Show quiz error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil detail quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // 1. Cek token
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'status'  => false,
                'message' => 'Token tidak ditemukan. Silakan login terlebih dahulu.'
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

        Log::info('📥 Data quiz masuk:', $request->all());
        Log::info('📥 Files:', array_keys($request->files->all()));

        // 2. Validasi
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

        DB::beginTransaction();
        try {
            $quiz = Quiz::create([
                'title'       => $request->title,
                'description' => $request->description,
                'duration'    => (int) $request->duration,
                'teacher_id'  => $teacherId
            ]);

            Log::info('✅ Quiz created, ID: ' . $quiz->id);

            foreach ($request->questions as $index => $q) {
                $questionImage = null;
                if (isset($q['image']) && $q['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $questionImage = FileHelper::upload($q['image'], 'questions');
                }

                // Simpan options (tanpa gambar, hanya teks)
                $options = [];
                foreach ($q['options'] as $opt) {
                    $options[] = ['text' => $opt['text']];
                }

                $question = Question::create([
                    'quiz_id'        => $quiz->id,
                    'question'       => $q['question'],
                    'image'          => $questionImage,
                    'options'        => json_encode($options),
                    'correct_answer' => $q['correct_answer']
                ]);

                // Simpan gambar option ke tabel option_images
                foreach ($q['options'] as $optIndex => $opt) {
                    if (isset($opt['image']) && $opt['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $imagePath = FileHelper::upload($opt['image'], 'options');
                        OptionImage::create([
                            'question_id'  => $question->id,
                            'option_index' => $optIndex,
                            'image_path'   => $imagePath
                        ]);
                    }
                }

                Log::info("✅ Question {$index} saved");
            }

            DB::commit();

            $quiz->load('questions.optionImages');
            $quiz->questions->each(function ($q) {
                $q->image_url = $q->image ? FileHelper::url($q->image) : null;
                $options = json_decode($q->options, true);
                $images = $q->optionImages->keyBy('option_index');

                foreach ($options as $idx => &$opt) {
                    if (isset($images[$idx])) {
                        $opt['image_url'] = FileHelper::url($images[$idx]->image_path);
                    }
                }
                $q->options = $options;
            });

            Log::info('🎉 Quiz saved successfully! ID: ' . $quiz->id);

            return response()->json([
                'status'  => true,
                'message' => 'Quiz berhasil dibuat',
                'data'    => $quiz
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ STORE QUIZ ERROR: ' . $e->getMessage());
            Log::error('❌ Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menyimpan quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Update quiz error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal update quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Delete quiz error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal hapus quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function start($id)
    {
        try {
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
                    'duration'        => $quiz->duration,
                    'total_questions' => $quiz->questions()->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Start quiz error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memulai quiz: ' . $e->getMessage()
            ], 500);
        }
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

        try {
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
                    'is_correct'     => $isCorrect,
                    'correct_answer' => $question->correct_answer
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Submit answer error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal submit jawaban: ' . $e->getMessage()
            ], 500);
        }
    }

    public function result($id)
    {
        try {
            $quiz = Quiz::find($id);
            if (!$quiz) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Quiz tidak ditemukan'
                ], 404);
            }

            $totalQuestions = $quiz->questions()->count();
            $correctAnswers = rand(0, $totalQuestions);

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
        } catch (\Exception $e) {
            Log::error('Result quiz error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal ambil hasil: ' . $e->getMessage()
            ], 500);
        }
    }
}