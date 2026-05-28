<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $teams = auth()->user()->teams();

        if ($request->search) {
            $teams->where('name', 'like', '%' . $request->search . '%');
        }

        $teams = $teams->get();

        return view('teams.index', compact('teams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team = auth()->user()->createOwnedTeam([
            'name' => $request->name,
        ]);

        $team->users()->updateExistingPivot(auth()->id(), [
            'role' => 'owner'
        ]);

        auth()->user()->update(['current_team_id' => $team->id]);

        return back()->with('success', 'Team Created!');
    }

    public function removeMember($teamId, $userId)
    {
        $team = Team::findOrFail($teamId);

        if ($team->owner_id !== auth()->id()) {
            abort(403);
        }

        $team->users()->detach($userId);

        return back()->with('success', 'Member removed!');
    }

    public function switchTeam($teamId)
    {
        $team = auth()->user()->teams()->findOrFail($teamId);

        auth()->user()->update([
            'current_team_id' => $team->id
        ]);

        return back()->with('success', 'Team switched!');
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        $teamId = $request->get('team_id');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $team = Team::with('users')->find($teamId);

        $existingMemberIds = $team ? $team->users->pluck('id')->toArray() : [];

        $users = User::where('id', '!=', auth()->id())
            ->whereNotIn('id', $existingMemberIds)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('email', 'like', '%' . $query . '%');
            })
            ->select('id', 'name', 'email')
            ->limit(5)
            ->get();

        return response()->json($users);
    }
}