# PHP_Laravel12_TeamWork

## Project Introduction

PHP_Laravel12_TeamWork is a Laravel 12 application that demonstrates how to build a Team Management System using the mpociot/teamwork package.

It allows users to create teams, invite members via email, manage team membership, and protect routes using team-based authorization.


## Project Overview

This project includes:

- User Authentication (Laravel Breeze)

- Team Creation and Ownership

- Email-based Team Invitations (Accept/Deny system)

- Team Member Management

- Middleware-based Team Owner Protection

- Gmail SMTP Integration for sending invitations

---

## Step 1 — Create Laravel 12 Project

```bash
composer create-project laravel/Laravel PHP_Laravel12_TeamWork "12.*"
cd PHP_Laravel12_TeamWork
```

---

## Step 2 — Install Authentication (Laravel Breeze)

```bash
composer require laravel/breeze --dev
php artisan breeze:install
npm install
npm run dev
php artisan migrate
```

Now authentication (login/register) is ready.

---

## Step 3 — Install Teamwork Package

```bash
composer require mpociot/teamwork
```

Publish config and migrations:

```bash
php artisan vendor:publish --provider="Mpociot\Teamwork\TeamworkServiceProvider"
```
Run migrations:

```bash
php artisan migrate
```
---

## Step 4 — Configure Environment File (.env)

```.env
APP_NAME=PHP_Laravel12_TeamWork
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=teamwork_db
DB_USERNAME=root
DB_PASSWORD=
```

---

## Step 5 — Configure Teamwork

Open:

```
config/teamwork.php
```

Update:

```php
'team_model' => App\Models\Team::class,
'user_model' => App\Models\User::class,
```
---

## Step 6 — Create Team Model

```bash
php artisan make:model Team
```

app/Models/Team.php

```php
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
```

---


## Step 7 — Update User Model

Open:

app/Models/User.php

Add trait:

```php
use Mpociot\Teamwork\Traits\UserHasTeams;

class User extends Authenticatable
{
    use HasFactory, Notifiable, UserHasTeams;
}
```

---

## Step 8 — Create Controllers

### Create TeamController

```bash
php artisan make:controller TeamController
```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;

class TeamController extends Controller
{
    public function index()
    {
        $teams = auth()->user()->teams;
        return view('teams.index', compact('teams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        auth()->user()->createOwnedTeam([
            'name' => $request->name,
        ]);

        return redirect()->back();
    }
}
```

### Create TeamInviteController

```bash
php artisan make:controller TeamInviteController
```
app/Http/Controllers/TeamInviteController.php

```php
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
```

---

## Step 9 — Laravel Gmail SMTP Setup Guide (Step-by-Step)

### 1 — Enable 2-Step Verification (Required)

Go to:

```
https://myaccount.google.com/security
```

Enable 2-Step Verification on your Google account.

This is mandatory before generating an App Password.


### 2 — Generate Gmail App Password

Go to:

```
 https://myaccount.google.com/apppasswords
```

Select:

- App: Mail

- Device: Other

- Name it: Laravel

Click Generate

Google will give you something like:

```
abcd efgh ijkl mnop
```

Remove spaces and use it like:

```
abcdefghijklmnop
```

This is your SMTP password.

### 3 — Update Your .env File

Open your Laravel .env file and update the mail section:

```.env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=yourgmail@gmail.com
MAIL_PASSWORD=your_16_character_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=yourgmail@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```
Replace:

```
yourgmail@gmail.com

your_16_character_app_password
```

---

## Step 10 — Create Notification

```bash
php artisan make:notification TeamInviteNotification
```

app/Notifications/TeamInviteNotification.php

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInviteNotification extends Notification
{
    use Queueable;

    public $invite;

    public function __construct($invite)
    {
        $this->invite = $invite;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $acceptUrl = url('/invite/accept/' . $this->invite->accept_token);
        $denyUrl   = url('/invite/deny/' . $this->invite->deny_token);

        return (new MailMessage)
            ->subject('You are invited to join a Team')
            ->greeting('Hello!')
            ->line('You have been invited to join the team: ' . $this->invite->team->name)
            ->action('Accept Invitation', $acceptUrl) // Only ONE action button
            ->line('If you do not want to join, click here to deny:')
            ->line($denyUrl) // Show deny as plain link
            ->line('Thank you!');
    }
}
```

---

### Step 11 — Create Blade View

Create folder:

resources/views/teams

Create file:

resources/views/teams/index.blade.php

```html
<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Team Management
        </h2>
    </x-slot>

    <br>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Create Team -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                    Create New Team
                </h3>

                <form action="{{ route('teams.store') }}" method="POST" class="flex gap-3">
                    @csrf
                    <input type="text"
                        name="name"
                        placeholder="Team Name"
                        class="border rounded px-3 py-2 w-full dark:bg-gray-700 dark:text-white"
                        required>

                    <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                        Create
                    </button>
                </form>
            </div>

            <!-- Team List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                    Your Teams
                </h3>

                @forelse($teams as $team)
                <div class="border-b py-4 dark:border-gray-700">

                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-bold text-lg text-gray-900 dark:text-gray-100">
                                {{ $team->name }}
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Members: {{ $team->users->count() }}
                            </p>
                        </div>
                    </div>

                    <!-- Invite Form -->
                    <form action="{{ route('teams.invite', $team->id) }}"
                        method="POST"
                        class="mt-3 flex gap-3">
                        @csrf
                        <input type="email"
                            name="email"
                            placeholder="Invite Email"
                            class="border rounded px-3 py-2 w-full dark:bg-gray-700 dark:text-white"
                            required>

                        <button type="submit"
                            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                            Invite
                        </button>
                    </form>

                </div>
                @empty
                <p class="text-gray-500 dark:text-gray-400">
                    You don’t have any teams yet.
                </p>
                @endforelse

            </div>

        </div>
    </div>

</x-app-layout>
```

---

## Step 12 — Register Middleware 

Open:

bootstrap/app.php

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Mpociot\Teamwork\Middleware\TeamOwner;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'teamowner' => TeamOwner::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

---

## Step 13 — Define Routes

Open:

routes/web.php

Add:

```php
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInviteController;


/*
|--------------------------------------------------------------------------
| Invite Routes (Public)
|--------------------------------------------------------------------------
*/

Route::get('/invite/accept/{token}', [TeamInviteController::class, 'accept']);
Route::get('/invite/deny/{token}', [TeamInviteController::class, 'deny']);



Route::middleware(['auth'])->group(function () {

    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');

    Route::post('/teams/{team}/invite', [TeamInviteController::class, 'invite'])
        ->name('teams.invite');

    // Team Owner Protected Route
    Route::get('/team/manage', function () {
        return "Only Team Owner Can Access";
    })->middleware('teamowner');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
```

---

## Step 14 — Testing

```
Register User A

Create Team

Register User B

Invite User B via email

Accept invite

Check team members
```

## Output

<img width="1523" height="787" alt="Screenshot 2026-02-20 144535" src="https://github.com/user-attachments/assets/25654191-350e-4299-ba80-8a96dd5ec386" />

<img width="1509" height="788" alt="image" src="https://github.com/user-attachments/assets/0c67b9fa-598a-4575-959a-f2cd2a972242" />


<img width="1918" height="1024" alt="Screenshot 2026-02-20 144458" src="https://github.com/user-attachments/assets/ddc82e76-c457-415e-856b-4de426ccb1f4" />

---

## Project Structure

```
PHP_Laravel12_TeamWork/
│
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   └── Team.php
│   │
│   ├── Http/
│   │   └── Controllers/
│   │       ├── TeamController.php
│   │       └── TeamInviteController.php
│   │
│   └── Notifications/
│       └── TeamInviteNotification.php
│
├── bootstrap/
│   └── app.php   ← middleware registered here
│
├── config/
│   └── teamwork.php
│
├── database/migrations
│
├── resources/
│   └── views/
│       └── teams/
│           └── index.blade.php
│
├── routes/
│   └── web.php
│
├── .env
│
└── README.md
```

---

Your PHP_Laravel12_TeamWork Project is now ready!
