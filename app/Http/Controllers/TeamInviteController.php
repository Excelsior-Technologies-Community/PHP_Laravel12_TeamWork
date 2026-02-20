<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Mpociot\Teamwork\Facades\Teamwork;
use App\Notifications\TeamInviteNotification;

class TeamInviteController extends Controller
{
    public function invite(Request $request, $teamId)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $invite = Teamwork::inviteToTeam($request->email, $teamId);

        Notification::route('mail', $request->email)
            ->notify(new TeamInviteNotification($invite));

        return back()->with('success', 'Invitation Sent!');
    }

    public function accept($token)
    {
        $invite = \Mpociot\Teamwork\TeamInvite::where('accept_token', $token)->firstOrFail();

        Teamwork::acceptInvite($invite);

        return redirect('/dashboard')->with('success', 'You joined the team!');
    }

    public function deny($token)
    {
        $invite = \Mpociot\Teamwork\TeamInvite::where('deny_token', $token)->firstOrFail();

        $invite->delete(); // deny = delete

        return redirect('/')->with('success', 'Invitation denied.');
    }
}
