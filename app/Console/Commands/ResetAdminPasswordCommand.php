<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAdminPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password
                            {email? : Admin email (optional; will prompt or use first admin)}
                            {--password= : New password (optional; default: password)}
                            {--create : Create a new admin user if none exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset an admin user password so you can log in again.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->option('password') ?? 'password';

        if (! $email) {
            $admin = User::where('role', 'admin')->first();
            if (! $admin) {
                if ($this->option('create')) {
                    $email = $this->ask('Email for the new admin user', 'admin@example.com');
                    $password = $this->option('password') ?? 'password';
                    if (User::where('email', $email)->exists()) {
                        $this->error("A user with email {$email} already exists. Run: php artisan admin:reset-password {$email}");
                        return self::FAILURE;
                    }
                    User::create([
                        'name' => 'Admin',
                        'email' => $email,
                        'password' => Hash::make($password),
                        'role' => 'admin',
                    ]);
                    $this->info("Admin user created.");
                    $this->line("Log in with email: <comment>{$email}</comment> and password: <comment>{$password}</comment>");
                    return self::SUCCESS;
                }
                $this->error('No admin user found in the database.');
                $this->line('Create one with: php artisan admin:reset-password --create');
                return self::FAILURE;
            }
            $email = $admin->email;
        } else {
            $admin = User::where('email', $email)->first();
            if (! $admin) {
                $this->error("No user found with email: {$email}");
                return self::FAILURE;
            }
        }

        if ($admin->role !== 'admin') {
            $admin->role = 'admin';
            $this->warn("User role was '{$admin->role}'; set to 'admin' so you can access admin routes.");
        }

        $admin->password = Hash::make($password);
        $admin->save();

        $this->info("Password reset for: {$admin->email}");
        $this->line("You can now log in with email: <comment>{$admin->email}</comment> and password: <comment>{$password}</comment>");
        $this->newLine();
        $this->line('If you still cannot log in:');
        $this->line('  1. Clear rate limit (too many failed attempts): php artisan cache:clear');
        $this->line('  2. Ensure the user has role=admin: check in database or run this command with that email.');

        return self::SUCCESS;
    }
}
