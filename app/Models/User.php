<?php


namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mpociot\Teamwork\Traits\UserHasTeams;

class User extends Authenticatable
{
    use HasFactory, Notifiable, UserHasTeams;

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_team_id'
    ];
}