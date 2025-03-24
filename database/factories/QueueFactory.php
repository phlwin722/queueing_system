<?php

namespace Database\Factories;

use App\Models\Queue;
use App\Models\Teller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

use function PHPSTORM_META\map;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class QueueFactory  extends Factory
{
    /**
     * Define the model's default state.

     *
     * @return array<string, mixed>
     */

     protected $model = Queue::class;
    public function definition(): array
    {
        return [
            // Generate a random token, usually it might be a unique string
            'token' => fake()->uuid,
            // Generate a fake name
            'name'=> fake()->name,
            // Generate a fake email
            'email' => fake()->unique()->safeEmail,
            // Generate a random email status (e.g., 'verified' or 'unverified')
            'email_status' => fake()->randomElement(['Pending','Sending']),
            // Generate a random type_id (assuming it's an integer, can be any number
            'type_id' => fake()->numberBetween(1, 5), // Adjust range to match valid IDs
             // Generate a random teller_id (assuming it's an integer, can be any number)
            'teller_id' => Teller::inRandomOrder()->first()->id,  // Ensure a valid teller_id is used
            // Generate a random queue number (usually numeric)
            'queue_number' => fake()->numberBetween(1,100),
            // Generate a random status from 'finished' or 'cancel'
            'status' => fake()->randomElement(['finished', 'cancelled']),  // Changed 'cancel' to 'cancelled'
            // Generate random 'updated_at' from 2023 to present
            'updated_at' => fake()->dateTimeBetween('2023-01-01', Carbon::now())->format('Y-m-d H:i:s'),
        ];
    }
}
