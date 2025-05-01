<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GenerateAdminToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:token {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a token for an admin user for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        if (!$email) {
            // Get the first admin user
            $admin = User::where('is_admin', true)->first();
            
            if (!$admin) {
                $this->error('No admin users found in the database');
                return 1;
            }
        } else {
            // Find the admin by email
            $admin = User::where('email', $email)
                ->where('is_admin', true)
                ->first();
                
            if (!$admin) {
                $this->error('No admin user found with the provided email');
                return 1;
            }
        }
        
        // Revoke existing tokens
        $admin->tokens()->delete();
        
        // Generate new token with admin ability
        $token = $admin->createToken('admin-cli-token', ['admin'])->plainTextToken;
        
        // Output the token
        $this->line($token);
        
        return 0;
    }
} 