<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('clinics')->insert([
            [
                'name' => 'Clinic 1',
                'short_name' => 'CT',
                'contact_no' => '123-113-5555',
                'created_user' => '1',
            ],
            [
                'name' => 'Clinic 2',
                'short_name' => 'CT2',
                'contact_no' => '123-113-5555',
                'created_user' => '1',
            ]
        ]);

        DB::table('users')->insert(
            [
                [
                'first_name' => 'Zain',
                'mid_name' => 'ul',
                'last_name' => 'Hassan',
                'email' => 'zainarain.7666@gmail.com',
                'password' => Hash::make('123123'),
                'role' => 1,
                'clinic_id' => ''
                ],
                [
                'first_name' => 'Adnan',
                'mid_name' => '',
                'last_name' => 'Rasheed',
                'email' => 'adnanrasheed@gmail.com',
                'password' => Hash::make('123123'),
                'role' => 1,
                'clinic_id' => ''
                ],
                [
                'first_name' => 'Muhammad',
                'mid_name' => '',
                'last_name' => 'Ishaq',
                'email' => 'muhammad.ishaq@researchquran.org',
                'password' => Hash::make('123456'),
                'role' => 1,
                'clinic_id' => ''
                ],
                [
                'first_name' => 'Clinic',
                'mid_name' => '',
                'last_name' => 'Admin',
                'email' => 'clinicadmin@yopmail.com',
                'password' => Hash::make('123456'),
                'role' => 11,
                'clinic_id' => 1
                ],
                [
                'first_name' => 'Clinic 2',
                'mid_name' => '',
                'last_name' => 'Admin',
                'email' => 'clinicadmin2@yopmail.com',
                'password' => Hash::make('123456'),
                'role' => 11,
                'clinic_id' => 2
                ]
            ]
        );
    }
}
