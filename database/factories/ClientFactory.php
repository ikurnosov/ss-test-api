<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Client::class, function (Faker $faker) {
    return [
        'id' => $faker->randomNumber(3),
        'balance' => $faker->randomNumber(3)
    ];
});
