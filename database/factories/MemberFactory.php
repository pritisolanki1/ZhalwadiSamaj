<?php

namespace Database\Factories;

use App\Models\Kuldevi;
use App\Models\Member;
use App\Models\NativePlace;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'father_id'             => '',
            'mother_id'             => '',
            'relation_id'           => '',
            'head_of_the_family_id' => '',
            'name'                  => [
                'en' => $this->faker->name,
                'gu' => $this->faker->name,
            ],
            'name_en'               => $this->faker->name(),
            'password'              => Hash::make(1234),
            'is_private'            => '1',
            'gender'                => 'Male',
            'birth_date'            => $this->faker->date('Y-m-d', '1461067200'),
            'expire_date'           => '',
            'phone'                 => $this->faker->phoneNumber,
            'phone_verified_at'     => Carbon::now(),
            'email'                 => $this->faker->unique()->safeEmail(),
            'email_verified_at'     => Carbon::now(),
            'device_token'          => Str::random(10),
            'device_serial'         => $this->faker->word(),
            'remember_token'        => Str::random(10),
            'blood_group'           => 'A+',
            'address'               => [
                'en' => 'Udhna,Surat',
                'gu' => 'ઉધના, સુરત',
            ],
            'occupation'            => [
                'en' => 'Developer',
                'gu' => 'Developer',
            ],
            'qualification'         => [
                'en' => 'Bcom',
                'gu' => 'Bcom',
            ],
            'notification_status'   => '1',
            'relationShip_status'   => 'Married',
            'profession'            => [
                'en' => 'Udhna,Surat',
                'gu' => 'ઉધના, સુરત',
            ],
            'profession_type'       => [
                'en' => 'Udhna,Surat',
                'gu' => 'ઉધના, સુરત',
            ],
            'work_address'          => [
                'en' => 'Udhna,Surat',
                'gu' => 'ઉધના, સુરત',
            ],
            'mosal'                 => [
                'en' => 'Udhna,Surat',
                'gu' => 'ઉધના, સુરત',
            ],
            'education'             => 'primary',
            'mother_name'           => [
                'en' => 'Mom',
                'gu' => 'mom',
            ],
            'father_name'           => [
                'en' => 'dad',
                'gu' => 'dad',
            ],
            'unique_number'         => $this->faker->word(),
            'total_donation'        => $this->faker->randomFloat(),
            'created_at'            => Carbon::now(),
            'updated_at'            => Carbon::now(),

            'native_place_id' => NativePlace::inRandomOrder()->first('id') ?? NativePlace::factory(),
            'zone_id'         => Zone::inRandomOrder()->first('id') ?? Zone::factory(),
            'kuldevi_id'      => Kuldevi::inRandomOrder()->first('id') ?? Kuldevi::factory(),
        ];
    }
}
