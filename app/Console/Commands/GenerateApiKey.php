<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'widget:api-key {user?} {--demo} {--regenerate}';

    /**
     * The console command description.
     */
    protected $description = 'Generate API key for chat widget';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userIdentifier = $this->argument('user');
        $regenerate = $this->option('regenerate');
        $demo = $this->option('demo');

        // Handle demo mode
        if ($demo || ! $userIdentifier || filter_var($userIdentifier, FILTER_VALIDATE_URL)) {
            return $this->createDemoUser();
        }

        // Find user by email or ID
        $user = User::where('email', $userIdentifier)
            ->orWhere('id', $userIdentifier)
            ->first();

        if (! $user) {
            $this->error("User not found: {$userIdentifier}");
            $this->line('');
            $this->info('💡 Available options:');
            $this->line("  • Use an existing user's email: php artisan widget:api-key user@example.com");
            $this->line('  • Create a demo user: php artisan widget:api-key --demo');
            $this->line("  • List users: php artisan tinker -> User::all(['id', 'name', 'email'])");

            return 1;
        }

        return $this->generateApiKeyForUser($user, $regenerate);
    }

    /**
     * Create a demo user with API key
     */
    private function createDemoUser(): int
    {
        $this->info('🚀 Creating demo user for widget testing...');

        // Check if demo user already exists
        $demoUser = User::where('email', 'demo@widget.local')->first();

        if ($demoUser && $demoUser->api_key && ! $this->option('regenerate')) {
            $this->info('Demo user already exists!');
            $this->displayUserInfo($demoUser);

            return 0;
        }

        // Create or update demo user
        if (! $demoUser) {
            $demoUser = User::create([
                'first_name' => 'Demo',
                'last_name' => 'Support Agent',
                'email' => 'demo@widget.local',
                'password' => bcrypt('password123'),
                'role' => 'merchant',
                'email_verified_at' => now(),
                'phone' => '',
                'telegram_token' => Str::random(40),
            ]);
            $this->info('✅ Demo user created!');
        }

        return $this->generateApiKeyForUser($demoUser, true);
    }

    /**
     * Generate API key for user
     */
    private function generateApiKeyForUser(User $user, bool $regenerate = false): int
    {
        // Check if user already has API key
        if ($user->api_key && ! $regenerate) {
            $this->info('User already has API key:');
            $this->displayUserInfo($user);
            $this->info('Use --regenerate flag to generate a new one');

            return 0;
        }

        // Generate new API key
        $apiKey = 'widget_' . Str::random(32);

        // Update user
        $user->update([
            'api_key' => $apiKey,
            'role' => 'merchant', // Set role to merchant for widget access
        ]);

        $this->info('🔑 API Key generated successfully!');
        $this->displayUserInfo($user);

        return 0;
    }

    /**
     * Display user information
     */
    private function displayUserInfo(User $user): void
    {
        $this->line('');
        $this->line("👤 User: {$user->first_name} {$user->last_name} ({$user->email})");
        $this->line("🔑 API Key: {$user->api_key}");
        $this->line("🔧 Role: {$user->role}");
        $this->line('');

        // Show usage example
        $this->info('📋 Usage Example:');
        $this->line('<script>');
        $this->line('  window.chatWidgetConfig = {');
        $this->line("    apiKey: '{$user->api_key}',");
        $this->line("    primaryColor: '#3B82F6'");
        $this->line('  };');
        $this->line('  // ... rest of widget code');
        $this->line('</script>');
        $this->line('');
        $this->warn("⚠️  Keep this API key secure and don't share it publicly!");
        $this->info('🌐 Visit /widget/demo to test the widget');
    }
}
