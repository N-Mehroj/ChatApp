<?php

namespace App\Console\Commands;

use App\Models\User;
use App\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create 
                            {--first_name= : User\'s first name}
                            {--last_name= : User\'s last name}
                            {--email= : User\'s email}
                            {--phone= : User\'s phone number}
                            {--role=user : User role (admin, user, operator, support, merchant)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user with random password';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Creating new user...');

        // Get user input
        $firstName = $this->option('first_name') ?: $this->ask('First name');
        $lastName = $this->option('last_name') ?: $this->ask('Last name');
        $email = $this->option('email') ?: $this->ask('Email');
        $phone = $this->option('phone') ?: $this->ask('Phone number');
        $roleInput = $this->option('role') ?: $this->choice(
            'Select role',
            UserRole::values(),
            'user'
        );

        // Validate input
        $validator = Validator::make([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'role' => $roleInput,
        ], [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'role' => 'required|in:'.implode(',', UserRole::values()),
        ]);

        if ($validator->fails()) {
            $this->error('❌ Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("  - {$error}");
            }

            return self::FAILURE;
        }

        // Generate random password
        $password = Str::random(12);
        $hashedPassword = Hash::make($password);

        // Convert role string to enum
        $role = UserRole::from($roleInput);

        try {
            // Create user
            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashedPassword,
                'role' => $role,
                'status' => 1, // Active
            ]);

            $this->newLine();
            $this->info('✅ User created successfully!');
            $this->newLine();

            // Display user info
            $this->table(['Field', 'Value'], [
                ['ID', $user->id],
                ['Name', $user->display_name],
                ['Email', $user->email],
                ['Phone', $user->phone],
                ['Role', $role->label()],
                ['Password', $password],
            ]);

            $this->newLine();
            $this->warn('⚠️  Please save this password information. It will not be shown again!');
            $this->newLine();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Failed to create user: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
