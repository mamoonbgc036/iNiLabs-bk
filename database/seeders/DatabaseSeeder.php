<?php
namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a demo user
        $user = User::create([
            'name'              => 'Demo User',
            'email'             => 'demo@example.com',
            'password'          => Hash::make('Password@123'),
            'email_verified_at' => now(),
        ]);

        // Create sample tasks for the demo user
        $tasks = [
            [
                'title'       => 'Complete Laravel API Development',
                'description' => 'Build a complete REST API with authentication, validation, and proper error handling.',
                'status'      => 'in_progress',
                'priority'    => 'high',
                'due_date'    => now()->addDays(3),
            ],
            [
                'title'       => 'Setup Vue 3 Frontend',
                'description' => 'Create a Vue 3 SPA with Pinia state management and Vue Router.',
                'status'      => 'pending',
                'priority'    => 'high',
                'due_date'    => now()->addDays(5),
            ],
            [
                'title'       => 'Write Unit Tests',
                'description' => 'Write comprehensive unit tests for the API endpoints.',
                'status'      => 'pending',
                'priority'    => 'medium',
                'due_date'    => now()->addWeek(),
            ],
            [
                'title'       => 'Code Review',
                'description' => 'Review code and ensure best practices are followed.',
                'status'      => 'pending',
                'priority'    => 'medium',
                'due_date'    => now()->addDays(4),
            ],
            [
                'title'       => 'Documentation',
                'description' => 'Write API documentation and setup guide.',
                'status'      => 'completed',
                'priority'    => 'low',
                'due_date'    => now()->subDay(),
            ],
            [
                'title'       => 'Deploy to Production',
                'description' => 'Deploy the application to a production server.',
                'status'      => 'pending',
                'priority'    => 'urgent',
                'due_date'    => now()->addDays(7),
            ],
            [
                'title'       => 'Bug Fixes',
                'description' => 'Fix any bugs found during testing.',
                'status'      => 'pending',
                'priority'    => 'high',
                'due_date'    => now()->addDays(2),
            ],
            [
                'title'       => 'Performance Optimization',
                'description' => 'Optimize database queries and API response times.',
                'status'      => 'cancelled',
                'priority'    => 'low',
                'due_date'    => null,
            ],
        ];

        foreach ($tasks as $taskData) {
            Task::create(array_merge($taskData, ['user_id' => $user->id]));
        }

        $this->command->info('Demo user created:');
        $this->command->info('Email: demo@example.com');
        $this->command->info('Password: Password@123');
    }
}
