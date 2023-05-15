<?php

namespace Database\Factories;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attachment>
 */
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->uuid(), // UUID
            'original_name' => $this->faker->word . '.jpg', // example.jpg
            'mime' => 'image/jpeg', // image/jpeg
            'extension' => 'jpg', // jpg
            'size' => $this->faker->numberBetween(1024, 4096), // 1024
            'sort' => 0, // 0
            'path' => 'path/to/', // path/to/
            'description' => $this->faker->sentence, // Lorem ipsum dolor sit amet consectetur adipisicing elit.
            'alt' => $this->faker->sentence, // Lorem ipsum dolor sit amet consectetur adipisicing elit.
            'hash' => Str::random(32), // 32 random characters
            'disk' => 'public', // public
            'group' => null, // null
        ];
    }
}
