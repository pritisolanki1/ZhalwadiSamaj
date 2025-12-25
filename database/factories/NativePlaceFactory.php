<?php

namespace Database\Factories;

use App\Models\NativePlace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class NativePlaceFactory extends Factory
{
    protected $model = NativePlace::class;

    public function definition(): array
    {
        return [
            'native'    => [
                'en' => $this->faker->city,
                'gu' => $this->faker->city,
            ],
            'taluka'    => [
                'en' => $this->faker->city,
                'gu' => $this->faker->city,
            ],
            'district'  => [
                'en' => $this->faker->city,
                'gu' => $this->faker->city,
            ],
            'state'     => [
                'en' => $this->faker->country,
                'gu' => $this->faker->country,
            ],
            'latitude'  => $this->faker->latitude,
            'longitude' => $this->faker->longitude,

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
