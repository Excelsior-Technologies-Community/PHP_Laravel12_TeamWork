<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mpociot\Teamwork\TeamworkTeam;


class Team extends TeamworkTeam
{
    protected $fillable = [
        'name',
        'owner_id',
    ];

    use HasFactory;
}