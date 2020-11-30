<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123456'),
            'rol' => '1',
            'tipo_precio'=>'1'
        ]);
        // DB::table('users')->insert([
        //     'name' => 'antonio',
        //     'email' => 'antonio@neofox.com',
        //     'password' => bcrypt('123456'),
        // ]);
    }
}
