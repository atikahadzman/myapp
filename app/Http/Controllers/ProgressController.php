<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\ReadingProgress;
use App\Models\Book;

class ProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = request()->get('page', 1);
        $perPage = 10;

        $cacheKey = "progress_page_{$page}";

        $progress = cache()->remember($cacheKey, 60, function () use ($perPage, $page) {
            return ReadingProgress::orderBy('created_at', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->toArray();
        });

        return response()->json([
            'status' => 'success',
            'data' => $progress
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|integer',
            'bookmark' => 'required|integer',
            'highlights' => 'sometimes|array'
        ]);

        $progress = ReadingProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'book_id' => $validated['book_id'],
                'bookmark' => $validated['bookmark'],
                'last_read_at' => now(),
            ],
            [
                'highlights' => $validated['highlights'] ?? [],
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Bookmark created successfully'
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $progress = ReadingProgress::find($id);

        if (!$progress) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bookmark not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $progress
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $progress = ReadingProgress::find($id);

        if (!$progress) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bookmark not found'
            ], 404);
        }

        $data = $request->validate([
            'bookmark' => 'required|integer',
            'highlights' => 'sometimes|array'
        ]);
        $data['last_read_at'] = now();

        $progress->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Bookmark/Highlight updated successfully',
            'data' => $progress
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $progress = ReadingProgress::find($id);

        if (!$progress) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bookmark not found'
            ], 404);
        }

        $progress->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'This progress has been deleted.'
        ], 200);
    }

    public function getByUserId(Request $request)
    {
        $progress = ReadingProgress::with('book.media')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $progress
        ], 200);
    }

    /**
     * get reading streak and set some message using persuasive technology principles
     */
    public function getReadingStreak(Request $request)
    {
        $user_id = auth()->id();
        
        $readDates = ReadingProgress::where('user_id', $user_id)
            ->selectRaw('DATE(updated_at) as read_date')
            ->groupBy('read_date')
            ->orderBy('read_date', 'desc')
            ->pluck('read_date')
            ->map(fn($date) => Carbon::parse($date));

        // case 1: Brand new user with zero history
        if ($readDates->isEmpty()) {
            return response()->json([
                'streak' => 0,
                'status' => 'cold',
                'message' => "Every great journey begins with a single page. Ready to start yours?"
            ]);
        }

        $latestReadDate = $readDates->first();
        $hasReadToday = $latestReadDate->isToday();
        
        // calculate consecutive days starting from the latest reading date
        $streak = 0;
        $currentCheckDate = $hasReadToday ? Carbon::today() : Carbon::yesterday();

        foreach ($readDates as $date) {
            if ($date->equalTo($currentCheckDate)) {
                $streak++;
                $currentCheckDate->subDay();
            } else {
                break;
            }
        }

        // determine the persuasive message based on user behavior state
        if ($hasReadToday) {
            // STATE A: Active & Safe (Positive Reinforcement)
            $status = 'safe';
            $message = $streak >= 5 
                ? "Unstoppable! That's a solid $streak-day habit you're building." 
                : "Awesome job! You've secured your streak for today.";
        } elseif ($latestReadDate->isYesterday()) {
            // STATE B: At Risk (Loss Aversion)
            $status = 'at_risk';
            $message = "Don't let your $streak-day streak slip away! Just read 2 pages to keep it alive.";
        } else {
            // streak is broken (latest reading date is older than yesterday)
            $wasLongStreak = $streak >= 3;
            $streak = 0; // reset counter for display
            
            if ($latestReadDate->diffInDays(Carbon::today()) <= 2) {
                // STATE C: Just Broken (Fresh Start Effect)
                $status = 'broken';
                $message = "Don't sweat the break. Today is a perfect day for a clean slate!";
            } else {
                // STATE D: Cold (Tunneling / Tiny Habits)
                $status = 'cold';
                $message = "Tiny steps count. Open your book for just 60 seconds today.";
            }
        }

        return response()->json([
            'streak' => $streak,
            'status' => $status,
            'message' => $message
        ]);
    }
}
