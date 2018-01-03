<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    $this->call(bundeslaenderSeeder::class);
	    $this->call(schulkeywordsSeeder::class);
	    $this->call(fragenSeeder::class);
	    $this->call(schulformenSeeder::class);
        $this->call(schulenseeder::class);
    }
}
