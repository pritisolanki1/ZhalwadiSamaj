<?php

namespace Database\Factories;

use App\Models\Kuldevi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class KuldeviFactory extends Factory
{
    protected $model = Kuldevi::class;

    public function definition(): array
    {
        return [
            'name'       => [
                'en' => $this->faker->name,
                'gu' => $this->faker->name,
            ],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
