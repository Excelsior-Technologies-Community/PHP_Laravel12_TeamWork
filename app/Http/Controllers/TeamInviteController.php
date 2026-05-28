<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Mpociot\Teamwork\Facades\Teamwork;
use Mpociot\Teamwork\TeamInvite;
use App\Notifications\TeamInviteNotification;
use App\Models\Team;

class TeamInviteController extends Controller
{
    public function invite(Request $request, $teamId)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $team = Team::findOrFail($teamId);

        if ($team->owner_id !== auth()->id()) {
            abort(403, 'Only team owner can invite members.');
        }

        $alreadyMember = $team->users()->where('email', $request->email)->exists();
        if ($alreadyMember) {
            return back()->with('error', 'This user is already a member of the team.');
        }

        $invite = Teamwork::inviteToTeam($request->email, $teamId);

        Notification::route('mail', $request->email)
            ->notify(new TeamInviteNotification($invite));

        return back()->with('success', 'Invitation sent to ' . $request->email);
    }

    public function accept($token)
    {
        $invite = TeamInvite::where('accept_token', $token)->firstOrFail();

        if (!auth()->check()) {
            return redirect()->route('login')->with('info', 'Please login to accept the invitation.');
        }

        Teamwork::acceptInvite($invite);

        $user = auth()->user();

        if (!$user->current_team_id) {
            $user->update(['current_team_id' => $invite->team_id]);
        }

        return redirect()->route('teams.index')->with('success', 'You have joined the team successfully.');
    }

    public function deny($token)
    {
        $invite = TeamInvite::where('deny_token', $token)->firstOrFail();

        $invite->delete();

        return redirect('/')->with('success', 'Invitation declined.');
    }
}