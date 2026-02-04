<?php

namespace App\Http\Controllers;

use App\Models\CalendarNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarNoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['year' => 'required|integer|min:2000|max:2100', 'month' => 'required|integer|min:1|max:12']);

        $start = sprintf('%04d-%02d-01', $request->year, $request->month);
        $end = date('Y-m-t', strtotime($start));

        $notes = CalendarNote::where('user_id', $request->user()->id)
            ->whereBetween('note_date', [$start, $end])
            ->get()
            ->mapWithKeys(fn (CalendarNote $n) => [$n->note_date->format('Y-m-d') => $n->body]);

        return response()->json($notes);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'note_date' => 'required|date',
            'body' => 'nullable|string|max:5000',
        ]);

        $date = $data['note_date'];
        $body = isset($data['body']) ? (string) $data['body'] : '';
        $user = $request->user();

        $note = CalendarNote::updateOrCreate(
            ['user_id' => $user->id, 'note_date' => $date],
            ['body' => $body]
        );

        return response()->json(['date' => $note->note_date->format('Y-m-d'), 'body' => $note->body]);
    }

    public function destroy(Request $request, string $date): JsonResponse
    {
        $request->validate(['date' => 'sometimes']); // route provides $date

        $deleted = CalendarNote::where('user_id', $request->user()->id)
            ->where('note_date', $date)
            ->delete();

        return response()->json(['deleted' => $deleted > 0]);
    }
}
