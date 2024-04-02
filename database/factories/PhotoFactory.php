<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Photo;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photo>
 */
class PhotoFactory extends Factory
{
    protected $model = Photo::class;
    public function definition(): array
    {
        $fileSize = $this->faker->numberBetween(10000, 2000000);

        return [
            'fileName' => $this->faker->word . '.jpg',
            'mimeType' => 'image/jpeg',
            'extension' => 'jpg',
            'fileSize' => $fileSize,
            'slug' => Str::random(10),
            'status' => $this->faker->randomElement(['active', 'deleted']),
            'user_id' => function () {
                $admin = \App\Models\User::where('role', 'admin')->inRandomOrder()->first();
                if (!$admin) {
                    $admin = \App\Models\User::factory()->create(['role' => 'admin']);
                }
                return $admin->id;
            },
            'name' => $this->faker->optional()->sentence,
            'description' => $this->faker->optional()->paragraph,
            'category' => $this->faker->optional()->word,
            'keyword' => $this->faker->optional()->words(3, true),
        ];
    }
}
