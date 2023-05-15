<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProfileImage>
 */
class UserProfileImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_profile_id' => UserProfile::factory(), // FK
            'attachment_id' => Attachment::factory(), // FK
        ];
    }
}
