<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $teamId = Auth::user()->current_team_id;

        if (!$teamId) {
            return redirect()->route('teams.index')->with('error', 'Please select an active team first.');
        }

        $team = Team::with('users')->findOrFail($teamId);

        $allMembers = $team->users;
        $members = $allMembers->where('id', '!=', Auth::id())->values();

        $receiverId = $request->query('receiver_id');

        if ($receiverId && !$allMembers->contains('id', $receiverId)) {
            $receiverId = null;
        }

        $query = Message::with('user')->where('team_id', $team->id);

        if ($receiverId) {
            $query->where(function ($q) use ($receiverId) {
                $q->where(function ($sub) use ($receiverId) {
                    $sub->where('user_id', Auth::id())
                        ->where('receiver_id', $receiverId);
                })->orWhere(function ($sub) use ($receiverId) {
                    $sub->where('user_id', $receiverId)
                        ->where('receiver_id', Auth::id());
                });
            });
        } else {
            $query->whereNull('receiver_id');
        }

        $messages = $query->oldest()->get();

        return view('chat.index', compact('messages', 'members', 'allMembers', 'team', 'receiverId'));
    }

    public function store(Request $request)
    {
        $request->validate(['content' => 'required|string|max:1000']);

        $teamId = Auth::user()->current_team_id;

        if (!$teamId) {
            return redirect()->route('teams.index')->with('error', 'Please select an active team first.');
        }

        $receiverId = $request->receiver_id ?: null;

        if ($receiverId) {
            $team = Team::with('users')->findOrFail($teamId);
            if (!$team->users->contains('id', $receiverId)) {
                $receiverId = null;
            }
        }

        Message::create([
            'team_id'     => $teamId,
            'user_id'     => Auth::id(),
            'receiver_id' => $receiverId,
            'content'     => $request->content,
        ]);

        return back();
    }
}