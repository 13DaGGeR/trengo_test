<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Views\IpView>
 */
class IpViewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $now = time();
        $yearAgo = $now - 60 * 60 * 24 * 365;
        return [
            'ip_address' => $this->faker->ipv6(),
            'created_at' => $this->faker->numberBetween($yearAgo, time()),
        ];
    }
}
