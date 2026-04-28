<?php

// app/Http/Controllers/TeamController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;

class TeamController extends Controller
{
    // Show Teams + Search
    public function index(Request $request)
    {
        $teams = auth()->user()->teams();

        if ($request->search) {
            $teams->where('name', 'like', '%' . $request->search . '%');
        }

        $teams = $teams->get();

        return view('teams.index', compact('teams'));
    }

    // Create Team
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team = auth()->user()->createOwnedTeam([
            'name' => $request->name,
        ]);

        // Set owner role
        $team->users()->updateExistingPivot(auth()->id(), [
            'role' => 'owner'
        ]);

        return back()->with('success', 'Team Created!');
    }

    // Remove Member
    public function removeMember($teamId, $userId)
    {
        $team = Team::findOrFail($teamId);

        if ($team->owner_id !== auth()->id()) {
            abort(403);
        }

        $team->users()->detach($userId);

        return back()->with('success', 'Member removed!');
    }

    // Switch Active Team
    public function switchTeam($teamId)
    {
        $team = auth()->user()->teams()->findOrFail($teamId);

        auth()->user()->update([
            'current_team_id' => $team->id
        ]);

        return back()->with('success', 'Team switched!');
    }
}