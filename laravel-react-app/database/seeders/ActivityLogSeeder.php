<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    /**
     * Sample log entries. Skipped when logs already exist.
     */
    public function run(): void
    {
        if (ActivityLog::query()->exists()) {
            return;
        }

        $entries = [
            ['admin@example.com', 'Logged in', 13],
            ['admin@example.com', 'Added product: Wireless Mouse', 13],
            ['employee@example.com', 'Logged in', 10],
            ['employee@example.com', 'Updated stock: Ceramic Coffee Mug', 10],
            ['customer1@example.com', 'Registered an account', 12],
            ['customer1@example.com', 'Placed order #1', 12],
            ['customer2@example.com', 'Placed order #2', 9],
            ['admin@example.com', 'Marked order #1 as completed', 8],
            ['customer2@example.com', 'Cancelled order #4', 4],
            ['admin@example.com', 'Deactivated user: inactive@example.com', 2],
            ['admin@example.com', 'Logged in', 0],
        ];

        foreach ($entries as [$email, $action, $daysAgo]) {
            ActivityLog::create([
                'user_id' => User::where('email', $email)->firstOrFail()->user_id,
                'action' => $action,
                'timestamp' => now()->subDays($daysAgo),
            ]);
        }
    }
}
