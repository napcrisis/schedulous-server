<?php

class UserTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker\Factory::create();
        for ($i = 0; $i < 10; $i++) {
            User::create(array(
                'international_number' => $faker->numerify('+6590######'),
                'country' => $faker->country,
                'registered' => 'yes',
                'registered_on' => $faker->dateTimeThisMonth()
            ));
        }
    }
}