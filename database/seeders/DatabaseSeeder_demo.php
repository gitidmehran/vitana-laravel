<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
      $currentDate = Carbon::now();
      $currentYear = $currentDate->year;
      
      DB::table('clinics')->insert(
        [
          [
            'name' => 'Northern Arizona Medical Group',
            'short_name' => 'NAMG',
            'contact_no' => '123-113-5555',
            'created_user' => '1',
          ],
          [
            'name' => 'Test Clinic',
            'short_name' => 'TC',
            'contact_no' => '123-113-5555',
            'created_user' => '1',
          ]
        ]
      );
        // 1 => "Super Admin",
        // 11 => "Admin",
        // 13 => "Owner",

        // 21 => "Doctor",
        // 22 => "Pharmacist",
        // 23 => "CCM Coordinator",
        // 24 => "Team Lead"
      DB::table('users')->insert(
        [
          [
          'first_name' => 'Super',
          'mid_name' => '',
          'last_name' => 'Admin',
          'email' => 'admin@gmail.com',
          'password' => Hash::make('123456'),
          'role' => 1,
          'clinic_id' => ''
          ],
          [
            'first_name' => 'Clinic',
            'mid_name' => '',
            'last_name' => 'Admin',
            'email' => 'clinicadmin@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 11,
            'clinic_id' => 1
          ],
          [
            'first_name' => 'Clinic 2',
            'mid_name' => '',
            'last_name' => 'Admin',
            'email' => 'clinicadmin2@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 11,
            'clinic_id' => 2
          ],
          [
            'first_name' => 'System',
            'mid_name' => '',
            'last_name' => 'Owner',
            'email' => 'owner@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 13,
            'clinic_id' => [1, 2]
          ],
          [
            'first_name' => 'Test',
            'mid_name' => '',
            'last_name' => 'Doctor',
            'email' => 'doctor@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 21,
            'clinic_id' => 1
          ],
          [
            'first_name' => 'Test',
            'mid_name' => '',
            'last_name' => 'Pharmacist',
            'email' => 'pharmacist@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 22,
            'clinic_id' => 1
          ],
          [
            'first_name' => 'CCM',
            'mid_name' => '',
            'last_name' => 'Coordinator',
            'email' => 'ccm@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 23,
            'clinic_id' => 1
          ]

        ]
      );
        
      DB::table('insurances')->insert(
        [
          [
            'clinic_id' => 1,
            'name' => "Humana",
            'provider' =>"hum-001",
            'short_name' =>"HUMA",
            'type_id' =>"1",
            'created_user' => '1',
          ],
          [
            'clinic_id' => 1,
            'name' => "Healthchoice Pathways",
            'provider' =>"hcpw-001",
            'short_name' =>"HCPW",
            'type_id' =>"1",
            'created_user' => '1',
          ],
          [
            'clinic_id' => 1,
            'name' => "Medicare Arizona",
            'provider' =>"med-arz-001",
            'short_name' =>"MCAZ",
            'type_id' =>"1",
            'created_user' => '1',
          ],
          [
            'clinic_id' => 1,
            'name' => "UHC Medicare",
            'provider' =>"uhc-001",
            'short_name' =>"UHCM",
            'type_id' =>"1",
            'created_user' => '1',
          ]

        ]
      );

      DB::table('programs')->insert(
        [
          [
            'clinic_id'     =>  1,
            'name'          =>  "Annual Wellness Visit",
            'short_name'    =>  "AWV",
            'slug'          => 'annual-wellness-visit',
            'created_user'  => '1',
          ],
          [
            'clinic_id'     => 1,
            'name'          =>  "Chronic Care Management",
            'short_name'    =>  "CCM",
            'slug'          => 'chronic-care-management',
            'created_user'  => '1',
          ]

        ]
      );

      DB::table('patients')->insert(
        [
          [
            'first_name' => 'test',
            'last_name' => 'patient1',
            'email' => 'patient1@gmail.com',
            'contact_no' => '214-359-6997',
            'dob' => '1944-04-20',
            'age' => 80,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '3525 Ersel Street',
            'insurance_id' => 1,
            'city' => 'Plano',
            'state' => 'TX',
            'zipCode' => '75074',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000000',
            'member_id' => 'TI0042872',
            'unique_id' => 'patient1test04201944',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          [
            'first_name' => 'test',
            'last_name' => 'patient2',
            'email' => 'patient2@gmail.com',
            'contact_no' => '907-957-5659',
            'dob' => '1950-02-18',
            'age' => 74,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '4345 Jerry Toth Drive',
            'insurance_id' => 1,
            'city' => 'Juneau',
            'state' => 'AK',
            'zipCode' => '99801',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000001',
            'member_id' => 'TI0042862',
            'unique_id' => 'patient2test02181950',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          [
            'first_name' => 'test',
            'last_name' => 'patient3',
            'email' => 'patient3@gmail.com',
            'contact_no' => '334-415-8549',
            'dob' => '1960-01-05',
            'age' => 64,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '3033 Willow Greene Drive',
            'insurance_id' => 1,
            'city' => 'Tallassee',
            'state' => 'AL',
            'zipCode' => '36078',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000003',
            'member_id' => 'TI0044872',
            'unique_id' => 'patient3test01051960',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 2,
          ],
          [
            'first_name' => 'test',
            'last_name' => 'patient4',
            'email' => 'patient4@gmail.com',
            'contact_no' => '714-716-5551',
            'dob' => '1964-01-05',
            'age' => 60,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '1683 Alpaca Way',
            'insurance_id' => 1,
            'city' => 'El Monte',
            'state' => 'CA',
            'zipCode' => '91731',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000004',
            'member_id' => 'TI0742872',
            'unique_id' => 'patient4test01051960',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 2,
          ],
          [
            'first_name' => 'test',
            'last_name' => 'patient5',
            'email' => 'patient5@gmail.com',
            'contact_no' => '860-452-7418',
            'dob' => '1965-10-08',
            'age' => 55,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '77 Lochmere Lane',
            'insurance_id' => 2,
            'city' => 'Hartford',
            'state' => 'CT',
            'zipCode' => '06103',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000005',
            'member_id' => 'TI0042222',
            'unique_id' => 'patient5test10081965',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 2,
          ],
          //55555555//////////////////////////////////////////////

          [
            'first_name' => 'test',
            'last_name' => 'patient6',
            'email' => 'patien6@gmail.com',
            'contact_no' => '323-394-3973',
            'dob' => '1942-04-20',
            'age' => 82,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '2570 New York Avenue',
            'insurance_id' => 2,
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zipCode' => '90017',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000006',
            'member_id' => 'TI0042333',
            'unique_id' => 'patient1test04201942',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 3,
          ],
          [
            'first_name' => 'test',
            'last_name' => 'patient7',
            'email' => 'patient7@gmail.com',
            'contact_no' => '502-303-9808',
            'dob' => '1952-02-18',
            'age' => 72,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '3796 Gregory Lane',
            'insurance_id' => 2,
            'city' => 'Louisville',
            'state' => 'KY',
            'zipCode' => '40202',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000007',
            'member_id' => 'TI0042882',
            'unique_id' => 'patient2test02181952',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 3,
          ],
          [
            'first_name' => 'test',
            'last_name' => 'patient8',
            'email' => 'patient8@gmail.com',
            'contact_no' => '847-684-1174',
            'dob' => '1959-01-05',
            'age' => 65,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '1743 Vine Street',
            'insurance_id' => 3,
            'city' => 'Chicago',
            'state' => 'IL',
            'zipCode' => '60605',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000008',
            'member_id' => 'TI0049872',
            'unique_id' => 'patient8test01051959',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          [
            'first_name' => 'test',
            'last_name' => 'patient9',
            'email' => 'patient9@gmail.com',
            'contact_no' => '858-617-4017',
            'dob' => '1963-01-05',
            'age' => 61,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '2850 Pike Street',
            'insurance_id' => 3,
            'city' => 'San Diego',
            'state' => 'CA',
            'zipCode' => '92121',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000009',
            'member_id' => 'TI0042865',
            'unique_id' => 'patient9test01051961',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          [
            'first_name' => 'test',
            'last_name' => 'patient10',
            'email' => 'patient10@gmail.com', 
            'contact_no' => '860-452-7418',
            'dob' => '1980-10-08',
            'age' => 44,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '77 Lochmere Lane',
            'insurance_id' => 3,
            'city' => 'Hartford',
            'state' => 'CT',
            'zipCode' => '06103',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000010',
            'member_id' => 'TI0042896',
            'unique_id' => 'patient10test10081980',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          //10 10 10 10//////////////////////////////////////////////

          [
            'first_name' => 'ROBERT ',
            'last_name' => 'do',
            'email' => 'do1@gmail.com',
            'contact_no' => '508-531-7493',
            'dob' => '1984-04-20',
            'age' => 40,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '4333 Kovar Road',
            'insurance_id' => 4,
            'city' => 'Bridgewater',
            'state' => 'MA',
            'zipCode' => '02324',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000011',
            'member_id' => 'TI0042832',
            'unique_id' => 'doROBERT1test04201984',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          [
            'first_name' => 'OSSEGE',
            'last_name' => 'do',
            'email' => 'OSSEGE@gmail.com',
            'contact_no' => '508-531-7493',
            'dob' => '1962-02-18',
            'age' => 82,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '966 Murphy Court',
            'insurance_id' => 4,
            'city' => 'Golden Valley',
            'state' => 'MN',
            'zipCode' => '55427',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000012',
            'member_id' => 'TI0042111',
            'unique_id' => 'doOSSEGE02181962',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          [
            'first_name' => 'JR',
            'last_name' => 'do',
            'email' => 'JR@gmail.com',
            'contact_no' => '918-233-7679',
            'dob' => '1949-01-05',
            'age' => 75,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '2588 Henry Ford Avenue',
            'insurance_id' => 4,
            'city' => 'Chetopa',
            'state' => 'OK',
            'zipCode' => '67336',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000013',
            'member_id' => 'TI0048976',
            'unique_id' => 'doJR01051949',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          [
            'first_name' => 'MARY',
            'last_name' => 'do',
            'email' => 'MARY@gmail.com',
            'contact_no' => '804-556-0454',
            'dob' => '1953-01-05',
            'age' => 71,
            'doctor_id' => 2,
            'gender' => 'female',
            'address' => '683 Biddie Lane',
            'insurance_id' => 4,
            'city' => 'Goochland',
            'state' => 'VA',
            'zipCode' => '23063',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000014',
            'member_id' => 'TI0049875',
            'unique_id' => 'doMARY01051953',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          [
            'first_name' => 'ANN',
            'last_name' => 'do',
            'email' => 'ANN@gmail.com', 
            'contact_no' => '315-662-0169',
            'dob' => '1984-10-08',
            'age' => 40,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '2040 Confederate Drive',
            'insurance_id' => 4,
            'city' => 'New Woodstock',
            'state' => 'NY',
            'zipCode' => '42547',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000015',
            'member_id' => 'TI00498721',
            'unique_id' => 'doANN0081984',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          //15 15 15 15//////////////////////////////////////////////

          [
            'first_name' => 'king',
            'last_name' => 'do',
            'email' => 'king@gmail.com',
            'contact_no' => '508-531-7493',
            'dob' => '1924-04-20',
            'age' => 100,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '542 West Virginia Avenue',
            'insurance_id' => 4,
            'city' => 'New York',
            'state' => 'NY',
            'zipCode' => '12207',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000016',
            'member_id' => 'TI0042245',
            'unique_id' => 'doking04201924',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          [
            'first_name' => 'JON',
            'last_name' => 'do',
            'email' => 'JON@gmail.com',
            'contact_no' => '210-690-0463',
            'dob' => '1934-02-18',
            'age' => 90,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '1160 Fidler Drive',
            'insurance_id' => 4,
            'city' => 'San Antonio',
            'state' => 'TX',
            'zipCode' => '78240',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000017',
            'member_id' => 'TI0042657',
            'unique_id' => 'doJON02181934',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          [
            'first_name' => 'SCHOOLCRAFT',
            'last_name' => 'do',
            'email' => 'SCHOOLCRAFT@gmail.com',
            'contact_no' => '317-710-1777',
            'dob' => '1944-01-05',
            'age' => 80,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '3098 Stewart Street',
            'insurance_id' => 4,
            'city' => 'Indianapolis',
            'state' => 'IN',
            'zipCode' => '46204',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000018',
            'member_id' => 'TI0042142',
            'unique_id' => 'doSCHOOLCRAFT01051944',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          [
            'first_name' => 'LAVERNE',
            'last_name' => 'do',
            'email' => 'LAVERNE@gmail.com',
            'contact_no' => '323-545-2122',
            'dob' => '1954-01-05',
            'age' => 70,
            'doctor_id' => 2,
            'gender' => 'female',
            'address' => '1830 Evergreen Lane',
            'insurance_id' => 4,
            'city' => 'Rialto',
            'state' => 'CA',
            'zipCode' => '92376',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000019',
            'member_id' => 'TI0042555',
            'unique_id' => 'doLAVERNE01051954',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ],
          [
            'first_name' => 'PEDERSON',
            'last_name' => 'do',
            'email' => 'PEDERSON@gmail.com', 
            'contact_no' => '410-203-8257',
            'dob' => '1964-10-08',
            'age' => 60,
            'doctor_id' => 2,
            'gender' => 'male',
            'address' => '1019 Hamilton Drive',
            'insurance_id' => 4,
            'city' => 'Ellicott City',
            'state' => 'MD',
            'zipCode' => '21042',
            'clinic_id' => 1,
            'patient_consent' => 0,
            'family_history' => json_encode([]),
            'created_user' => 1,
            'identity' => '00000020',
            'member_id' => 'TI0044215',
            'unique_id' => 'doPEDERSON0081964',
            'patient_year'=> $currentYear,
            'status' => '1',
            'group' => 1,
          ]

        ]
      );


      $data1 = 
        [
            "fall_screening" => [
                "blackingout_from_bed" => "Yes",
                "fall_in_one_year" => "No",
                "number_of_falls" => null,
                "injury" => null,
                "physical_therapy" => null,
                "unsteady_todo_things" => "Yes",
                "assistance_device" => "Walker",
                "completed" => "1"
            ],
            "medicareOptions" => "welcomeMedicare",
            "depression_phq9" => [
                "referred_to_mh_professional" => "Yes",
                "enroll_in_bhi" => "Yes",
                "feltdown_depressed_hopeless" => 3,
                "little_interest_pleasure" => 3,
                "feeling_bad_failure" => 3,
                "trouble_concentrating" => 3,
                "slow_fidgety" => 3,
                "suicidal_thoughts" => 3,
                "trouble_sleep" => 2,
                "tired_little_energy" => 2,
                "poor_over_appetite" => 2,
                "problem_difficulty" => "Extremely difficult",
                "comments" => "testing purpose",
                "completed" => "1"
            ],
            "general_health" => [
                "health_level" => "Good",
                "mouth_and_teeth" => "Excellent",
                "feeling_caused_distress" => "Yes",
                "completed" => "1"
            ],
            "social_emotional_support" => [
                "get_social_emotional_support" => "Sometimes"
            ],
            "high_stress" => [
                "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
                "pain_felt" => "None"
            ],
            "cognitive_assessment" => [
                "year_recalled" => "correct",
                "month_recalled" => "incorrect",
                "hour_recalled" => "correct",
                "reverse_month" => "Correct",
                "reverse_count" => "1 error",
                "address_recalled" => "1 error",
                "completed" => "1"
            ],
            "physical_activities" => [
                "does_not_apply" => null,
                "days_of_exercise" => "7",
                "mins_of_exercise" => "7",
                "exercise_intensity" => "moderate",
                "completed" => "1"
            ],
            "alcohol_use" => [
                "days_of_alcoholuse" => "7",
                "drinks_per_day" => "7",
                "drinks_per_occasion" => "7",
                "drink_drive_yes" => "Yes",
                "completed" => "1"
            ],
            "tobacco_use" => [
                "average_packs_per_year" => 49,
                "perform_ldct" => null,
                "smoked_in_thirty_days" => "Yes",
                "smoked_in_fifteen_years" => "Yes",
                "average_smoking_years" => "7",
                "average_packs_per_day" => "7",
                "quit_tobacco" => "Yes",
                "smokeless_product_use" => "Yes",
                "completed" => "1"
            ],
            "nutrition" => [
                "fruits_vegs" => "7",
                "whole_grain_food" => "7",
                "high_fat_food" => "7",
                "sugar_beverages" => "7",
                "completed" => "1"
            ],
            "seatbelt_use" => [
                "wear_seat_belt" => "Yes",
                "completed" => "1"
            ],
            "immunization" => [
                "flu_vaccine_recieved" => "Yes",
                "flu_vaccine_recieved_on" => "02\/2024",
                "flu_vaccine_recieved_at" => "ryk",
                "flu_vaccine_refused" => null,
                "flu_vaccine_script_given" => null,
                "comments" => "testing comment",
                "pneumococcal_vaccine_recieved" => "No",
                "pneumococcal_prevnar_recieved_on" => null,
                "pneumococcal_prevnar_recieved_at" => null,
                "pneumococcal_ppsv23_recieved_on" => null,
                "pneumococcal_ppsv23_recieved_at" => null,
                "pneumococcal_vaccine_refused" => "No",
                "pneumococcal_vaccine_script_given" => "Yes",
                "completed" => "1"
            ],
            "screening" => [
                "colonoscopy_done" => "Yes",
                "colonoscopy_refused" => null,
                "refused_colonoscopy" => null,
                "refused_fit_test" => null,
                "refused_cologuard" => null,
                "colonoscopy_script" => null,
                "script_given_for" => null,
                "colon_test_type" => "FIT Test",
                "colonoscopy_done_on" => "01\/2025",
                "colonoscopy_done_at" => null,
                "next_colonoscopy" => "01\/2026",
                "colonoscopy_report_reviewed" => null,
                "comments" => "screening testing comment",
                "completed" => "1",
                "mammogram_done" => "Yes",
                "mammogram_refused" => null,
                "mammogram_script" => null,
                "mammogram_done_on" => "05\/2024",
                "next_mommogram" => "08\/2026",
                "mommogram_report_reviewed" => null
            ],
            "diabetes" => [
                "ckd_stage_4" => null,
                "diabetec_patient" => "Yes",
                "fbs_in_year" => null,
                "fbs_value" => null,
                "fbs_date" => null,
                "completed" => "1",
                "hba1c_value" => "5",
                "hba1c_date" => "05\/2024",
                "diabetec_eye_exam" => "Yes",
                "diabetec_eye_exam_report" => "report_requested",
                "eye_exam_doctor" => null,
                "eye_exam_facility" => null,
                "eye_exam_date" => null,
                "eye_exam_report_reviewed" => null,
                "ratinavue_ordered" => null,
                "urine_microalbumin" => null,
                "urine_microalbumin_date" => null,
                "urine_microalbumin_report" => null,
                "urine_microalbumin_ordered" => null,
                "urine_microalbumin_inhibitor" => null,
                "diabetec_ratinopathy" => null
            ],
            "misc" => [
                "completed" => "1",
                "Suppliers" => [
                    
                ],
                "Pharmacy" => [
                    
                ],
            ],
            "bp_assessment" => [
                "bp_value" => "120\/90",
                "bp_date" => "02\/26\/2024",
                "completed" => "1"
            ],
            "physical_exam" => [
                "completed" => "1"
            ]
    
        ];

      $data2 = 
        [
            "fall_screening" => [
              "blackingout_from_bed" => "Yes",
              "fall_in_one_year" => "No",
              "number_of_falls" => null,
              "injury" => null,
              "physical_therapy" => null,
              "unsteady_todo_things" => "Yes",
              "assistance_device" => "Walker",
              "completed" => "1"
            ],
            "medicareOptions" => "welcomeMedicare",
            "depression_phq9" => [
              "referred_to_mh_professional" => "Yes",
              "enroll_in_bhi" => "Yes",
              "feltdown_depressed_hopeless" => 3,
              "little_interest_pleasure" => 3,
              "feeling_bad_failure" => 3,
              "trouble_concentrating" => 3,
              "slow_fidgety" => 3,
              "suicidal_thoughts" => 3,
              "trouble_sleep" => 2,
              "tired_little_energy" => 2,
              "poor_over_appetite" => 2,
              "problem_difficulty" => "Extremely difficult",
              "comments" => "testing purpose",
              "completed" => "1"
            ],
            "general_health" => [
              "health_level" => "Good",
              "mouth_and_teeth" => "Excellent",
              "feeling_caused_distress" => "Yes",
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Sometimes"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "None"
            ],
            "cognitive_assessment" => [
              "year_recalled" => "correct",
              "month_recalled" => "incorrect",
              "hour_recalled" => "correct",
              "reverse_month" => "Correct",
              "reverse_count" => "1 error",
              "address_recalled" => "1 error",
              "completed" => "1"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "7",
              "mins_of_exercise" => "7",
              "exercise_intensity" => "moderate",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "7",
              "drinks_per_day" => "7",
              "drinks_per_occasion" => "7",
              "drink_drive_yes" => "Yes",
              "completed" => "1"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => 49,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "Yes",
              "smoked_in_fifteen_years" => "Yes",
              "average_smoking_years" => "7",
              "average_packs_per_day" => "7",
              "quit_tobacco" => "Yes",
              "smokeless_product_use" => "Yes",
              "completed" => "1"
            ],
            "nutrition" => [
              "fruits_vegs" => "7",
              "whole_grain_food" => "7",
              "high_fat_food" => "7",
              "sugar_beverages" => "7",
              "completed" => "1"
            ],
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "Yes",
              "flu_vaccine_recieved_on" => "02\/2024",
              "flu_vaccine_recieved_at" => "ryk",
              "flu_vaccine_refused" => null,
              "flu_vaccine_script_given" => null,
              "comments" => "testing comment",
              "pneumococcal_vaccine_recieved" => "No",
              "pneumococcal_prevnar_recieved_on" => null,
              "pneumococcal_prevnar_recieved_at" => null,
              "pneumococcal_ppsv23_recieved_on" => null,
              "pneumococcal_ppsv23_recieved_at" => null,
              "pneumococcal_vaccine_refused" => "No",
              "pneumococcal_vaccine_script_given" => "Yes",
              "completed" => "1"
            ],
            "screening" => [
              "colonoscopy_done" => "Yes",
              "colonoscopy_refused" => null,
              "refused_colonoscopy" => null,
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "colonoscopy_script" => null,
              "script_given_for" => null,
              "colon_test_type" => "FIT Test",
              "colonoscopy_done_on" => "01\/2025",
              "colonoscopy_done_at" => null,
              "next_colonoscopy" => "01\/2026",
              "colonoscopy_report_reviewed" => null,
              "comments" => "screening testing comment",
              "completed" => "1",
              "mammogram_done" => "Yes",
              "mammogram_refused" => null,
              "mammogram_script" => null,
              "mammogram_done_on" => "05\/2024",
              "next_mommogram" => "08\/2026",
              "mommogram_report_reviewed" => null
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "Yes",
              "fbs_in_year" => null,
              "fbs_value" => null,
              "fbs_date" => null,
              "completed" => "1",
              "hba1c_value" => "5",
              "hba1c_date" => "05\/2024",
              "diabetec_eye_exam" => "Yes",
              "diabetec_eye_exam_report" => "report_requested",
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "ratinavue_ordered" => null,
              "urine_microalbumin" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "diabetec_ratinopathy" => null
            ],
            "misc" => [
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ],
              "height" => "5,6"
            ],
            "bp_assessment" => [
              "bp_value" => "120\/89",
              "bp_date" => "02\/26\/2024",
              "completed" => "1"
            ],
            "physical_exam" => [
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "4",
              "ldl_date" => "01\/2024",
              "patient_has_ascvd" => "Yes",
              "ldlvalue_190ormore" => null,
              "pure_hypercholesterolemia" => null,
              "active_diabetes" => null,
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "statin_prescribed" => "Yes",
              "statintype_dosage" => "Atorvastatin10 to 20 mg",
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "21.5",
              "completed" => "1"
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "Yes",
              "completed" => "1",
              "provider" => [
                
              ]
            ]
        ];

      $data3 = 
        [
            "fall_screening" => [
                "blackingout_from_bed" => "Yes",
                "fall_in_one_year" => "No",
                "number_of_falls" => null,
                "injury" => null,
                "physical_therapy" => null,
                "unsteady_todo_things" => "Yes",
                "assistance_device" => "Walker",
                "completed" => "1"
            ],
            "medicareOptions" => "welcomeMedicare",
            "depression_phq9" => [
                "referred_to_mh_professional" => "Yes",
                "enroll_in_bhi" => "Yes",
                "feltdown_depressed_hopeless" => 2,
                "little_interest_pleasure" => 2,
                "feeling_bad_failure" => 2,
                "trouble_concentrating" => 2,
                "slow_fidgety" => 2,
                "suicidal_thoughts" => 2,
                "trouble_sleep" => 2,
                "tired_little_energy" => 2,
                "poor_over_appetite" => 2,
                "problem_difficulty" => "Extremely difficult",
                "comments" => "testing purpose",
                "completed" => "1"
            ],
            "general_health" => [
                "health_level" => "Good",
                "mouth_and_teeth" => "Excellent",
                "feeling_caused_distress" => "Yes",
                "completed" => "1"
            ],
            "social_emotional_support" => [
                "get_social_emotional_support" => "Sometimes"
            ],
            "high_stress" => [
                "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
                "pain_felt" => "None"
            ],
            "cognitive_assessment" => [
                "year_recalled" => "correct",
                "month_recalled" => "incorrect",
                "hour_recalled" => "correct",
                "reverse_month" => "Correct",
                "reverse_count" => "1 error",
                "address_recalled" => "1 error",
                "completed" => "1"
            ],
            "physical_activities" => [
                "does_not_apply" => null,
                "days_of_exercise" => "6",
                "mins_of_exercise" => "6",
                "exercise_intensity" => "moderate",
                "completed" => "1"
            ],
            "alcohol_use" => [
                "days_of_alcoholuse" => "6",
                "drinks_per_day" => "6",
                "drinks_per_occasion" => "6",
                "drink_drive_yes" => "Yes",
                "completed" => "1"
            ],
            "tobacco_use" => [
                "average_packs_per_year" => 49,
                "perform_ldct" => null,
                "smoked_in_thirty_days" => "Yes",
                "smoked_in_fifteen_years" => "Yes",
                "average_smoking_years" => "6",
                "average_packs_per_day" => "6",
                "quit_tobacco" => "Yes",
                "smokeless_product_use" => "Yes",
                "completed" => "1"
            ],
            "nutrition" => [
                "fruits_vegs" => "6",
                "whole_grain_food" => "6",
                "high_fat_food" => "6",
                "sugar_beverages" => "6",
                "completed" => "1"
            ],
            "seatbelt_use" => [
                "wear_seat_belt" => "Yes",
                "completed" => "1"
            ],
            "immunization" => [
                "flu_vaccine_recieved" => "Yes",
                "flu_vaccine_recieved_on" => "02\/2024",
                "flu_vaccine_recieved_at" => "ryk",
                "flu_vaccine_refused" => null,
                "flu_vaccine_script_given" => null,
                "comments" => "testing comment",
                "pneumococcal_vaccine_recieved" => "No",
                "pneumococcal_prevnar_recieved_on" => null,
                "pneumococcal_prevnar_recieved_at" => null,
                "pneumococcal_ppsv23_recieved_on" => null,
                "pneumococcal_ppsv23_recieved_at" => null,
                "pneumococcal_vaccine_refused" => "No",
                "pneumococcal_vaccine_script_given" => "Yes",
                "completed" => "1"
            ],
            "screening" => [
                "colonoscopy_done" => "Yes",
                "colonoscopy_refused" => null,
                "refused_colonoscopy" => null,
                "refused_fit_test" => null,
                "refused_cologuard" => null,
                "colonoscopy_script" => null,
                "script_given_for" => null,
                "colon_test_type" => "FIT Test",
                "colonoscopy_done_on" => "01\/2025",
                "colonoscopy_done_at" => null,
                "next_colonoscopy" => "01\/2026",
                "colonoscopy_report_reviewed" => null,
                "comments" => "screening testing comment",
                "completed" => "1",
                "mammogram_done" => "Yes",
                "mammogram_refused" => null,
                "mammogram_script" => null,
                "mammogram_done_on" => "05\/2024",
                "next_mommogram" => "08\/2026",
                "mommogram_report_reviewed" => null
            ],
            "diabetes" => [
                "ckd_stage_4" => null,
                "diabetec_patient" => "Yes",
                "fbs_in_year" => null,
                "fbs_value" => null,
                "fbs_date" => null,
                "completed" => "1",
                "hba1c_value" => "5",
                "hba1c_date" => "05\/2024",
                "diabetec_eye_exam" => "Yes",
                "diabetec_eye_exam_report" => "report_requested",
                "eye_exam_doctor" => null,
                "eye_exam_facility" => null,
                "eye_exam_date" => null,
                "eye_exam_report_reviewed" => null,
                "ratinavue_ordered" => null,
                "urine_microalbumin" => null,
                "urine_microalbumin_date" => null,
                "urine_microalbumin_report" => null,
                "urine_microalbumin_ordered" => null,
                "urine_microalbumin_inhibitor" => null,
                "diabetec_ratinopathy" => null
            ],
            "misc" => [
                "completed" => "1",
                "Suppliers" => [
                    
                ],
                "Pharmacy" => [
                    
                ],
            ],
            "bp_assessment" => [
                "bp_value" => "130\/90",
                "bp_date" => "02\/28\/2024",
                "completed" => "1"
            ],
            "physical_exam" => [
                "completed" => "1"
            ]
    
        ];

      $data4 = 
        [
            "fall_screening" => [
              "fall_in_one_year" => "No",
              "number_of_falls" => null,
              "injury" => null,
              "physical_therapy" => null,
              "blackingout_from_bed" => "Yes",
              "assistance_device" => "Walker",
              "unsteady_todo_things" => "No",
              "completed" => "1"
            ],
            "medicareOptions" => "welcomeMedicare",
            "depression_phq9" => [
              "referred_to_mh_professional" => "No",
              "enroll_in_bhi" => "No",
              "feltdown_depressed_hopeless" => 1,
              "feeling_bad_failure" => 3,
              "trouble_concentrating" => 3,
              "little_interest_pleasure" => 0,
              "trouble_sleep" => 2,
              "slow_fidgety" => 0,
              "tired_little_energy" => 1,
              "poor_over_appetite" => 1,
              "suicidal_thoughts" => 0,
              "problem_difficulty" => "Somewhat difficult",
              "completed" => "1"
            ],
            "general_health" => [
              "health_level" => "Good",
              "mouth_and_teeth" => "Fair",
              "feeling_caused_distress" => "Yes",
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Usually"
            ],
            "high_stress" => [
              "stress_problem" => "Often"
            ],
            "pain" => [
              "pain_felt" => "Some"
            ],
            "cognitive_assessment" => [
              "year_recalled" => "incorrect",
              "month_recalled" => "correct",
              "hour_recalled" => "incorrect",
              "reverse_count" => "correct",
              "reverse_month" => "1 error",
              "address_recalled" => "1 error",
              "completed" => "1"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => null,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "completed" => "1"
            ],
            "nutrition" => [
              "completed" => "1"
            ],
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "No",
              "flu_vaccine_recieved_on" => null,
              "flu_vaccine_recieved_at" => null,
              "flu_vaccine_refused" => "Yes",
              "flu_vaccine_script_given" => null,
              "pneumococcal_vaccine_recieved" => "No",
              "pneumococcal_prevnar_recieved_on" => null,
              "pneumococcal_prevnar_recieved_at" => null,
              "pneumococcal_ppsv23_recieved_on" => null,
              "pneumococcal_ppsv23_recieved_at" => null,
              "pneumococcal_vaccine_refused" => "Yes",
              "pneumococcal_vaccine_script_given" => null,
              "completed" => "1"
            ],
            "screening" => [
              "colonoscopy_done" => "No",
              "colon_test_type" => null,
              "colonoscopy_done_on" => null,
              "colonoscopy_done_at" => null,
              "next_colonoscopy" => null,
              "colonoscopy_report_reviewed" => null,
              "colonoscopy_refused" => "Yes",
              "refused_colonoscopy" => "1",
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "colonoscopy_script" => null,
              "script_given_for" => null,
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "No",
              "hba1c_value" => null,
              "hba1c_date" => null,
              "diabetec_eye_exam" => null,
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "ratinavue_ordered" => null,
              "urine_microalbumin" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "fbs_in_year" => "No",
              "fbs_value" => null,
              "fbs_date" => null,
              "diabetec_ratinopathy" => null,
              "urine_microalbumin_value" => null,
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "No",
              "ldl_value" => null,
              "ldl_date" => null,
              "patient_has_ascvd" => "Yes",
              "ldlvalue_190ormore" => null,
              "pure_hypercholesterolemia" => null,
              "active_diabetes" => null,
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "statin_prescribed" => "Yes",
              "statintype_dosage" => "Atorvastatin10 to 20 mg",
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_value" => "130\/90",
              "bp_date" => "03\/06\/2024",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "completed" => "1"
            ],
            "physical_exam" => [
              "completed" => "1"
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "No",
              "full_name" => null,
              "speciality" => null,
              "completed" => "1",
              "provider" => [
                
              ]
            ],
            "misc" => [
              "height" => "6,6",
              "patient_on_asprin" => "No",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ]
            ]
        ];

      $data5 = 
        [
            "fall_screening" => [
              "fall_in_one_year" => "Yes",
              "number_of_falls" => "One",
              "injury" => "Yes",
              "physical_therapy" => "Referred",
              "unsteady_todo_things" => "Yes",
              "blackingout_from_bed" => "Yes",
              "completed" => "1"
            ],
            "medicareOptions" => "welcomeMedicare",
            "depression_phq9" => [
              "referred_to_mh_professional" => "Yes",
              "enroll_in_bhi" => "Yes",
              "feltdown_depressed_hopeless" => 3,
              "feeling_bad_failure" => 3,
              "trouble_concentrating" => 3,
              "little_interest_pleasure" => 3,
              "trouble_sleep" => 3,
              "slow_fidgety" => 3,
              "tired_little_energy" => 3,
              "suicidal_thoughts" => 3,
              "poor_over_appetite" => 3,
              "problem_difficulty" => "Extremely difficult",
              "completed" => "1"
            ],
            "general_health" => [
              "health_level" => "Excellent",
              "mouth_and_teeth" => "Excellent",
              "feeling_caused_distress" => "Yes",
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Always"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "None"
            ],
            "cognitive_assessment" => [
              "year_recalled" => "correct",
              "month_recalled" => "correct",
              "hour_recalled" => "correct",
              "reverse_count" => "correct",
              "reverse_month" => "Correct",
              "address_recalled" => "correct",
              "completed" => "1"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "7",
              "mins_of_exercise" => "2",
              "exercise_intensity" => "light",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "6",
              "drinks_per_day" => "2",
              "drinks_per_occasion" => "2",
              "drink_drive_yes" => "Yes",
              "completed" => "1"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => null,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "Yes",
              "smoked_in_fifteen_years" => "Yes",
              "smokeless_product_use" => "Yes",
              "completed" => "1"
            ],
            "nutrition" => [
              "fruits_vegs" => "1",
              "whole_grain_food" => "0",
              "high_fat_food" => "1",
              "sugar_beverages" => "1",
              "completed" => "1"
            ],
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "Yes",
              "flu_vaccine_recieved_on" => "01\/2024",
              "flu_vaccine_recieved_at" => "home",
              "pneumococcal_vaccine_recieved" => "Yes",
              "pneumococcal_prevnar_recieved_on" => "01\/2024",
              "pneumococcal_prevnar_recieved_at" => "home",
              "pneumococcal_ppsv23_recieved_on" => "01\/2024",
              "pneumococcal_ppsv23_recieved_at" => "home",
              "pneumococcal_vaccine_refused" => null,
              "pneumococcal_vaccine_script_given" => null,
              "flu_vaccine_refused" => null,
              "flu_vaccine_script_given" => null,
              "completed" => "1"
            ],
            "screening" => [
              "colonoscopy_done" => "Yes",
              "colonoscopy_refused" => null,
              "refused_colonoscopy" => null,
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "colonoscopy_script" => null,
              "script_given_for" => null,
              "colon_test_type" => "Colonoscopy",
              "colonoscopy_done_on" => "01\/2024",
              "colonoscopy_done_at" => "home",
              "next_colonoscopy" => "01\/2034",
              "colonoscopy_report_reviewed" => null,
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "Yes",
              "fbs_in_year" => null,
              "fbs_value" => null,
              "fbs_date" => null,
              "hba1c_value" => "7",
              "hba1c_date" => "01\/2024",
              "diabetec_eye_exam" => "Yes",
              "diabetec_eye_exam_report" => "report_requested",
              "ratinavue_ordered" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "diabetec_ratinopathy" => null,
              "urine_microalbumin" => "Yes",
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "urine_microalbumin_value" => "5",
              "urine_microalbumin_date" => "01\/2024",
              "urine_microalbumin_report" => "Positive",
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "8",
              "ldl_date" => "01\/2024",
              "patient_has_ascvd" => "Yes",
              "ldlvalue_190ormore" => null,
              "pure_hypercholesterolemia" => null,
              "active_diabetes" => null,
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "statin_prescribed" => "Yes",
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "statintype_dosage" => "Lovastatin40 mg",
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_value" => "100\/60",
              "bp_date" => "03\/07\/2024",
              "completed" => "1"
            ],
            "physical_exam" => [
              "completed" => "1"
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "Yes",
              "completed" => "1",
              "provider" => [
                
              ]
            ],
            "misc" => [
              "height" => "6,0",
              "weight" => "0",
              "patient_on_asprin" => "Yes",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ]
            ]
        ];

      $data6 = 
        [
            "fall_screening" => [
              "completed" => "1",
              "fall_in_one_year" => "No",
              "number_of_falls" => null,
              "injury" => null,
              "physical_therapy" => null,
              "blackingout_from_bed" => "No",
              "unsteady_todo_things" => "No",
              "assistance_device" => "None"
            ],
            "screening" => [
              "mammogram_done" => "Yes",
              "mammogram_refused" => null,
              "mammogram_script" => null,
              "mammogram_done_on" => "10\/2022",
              "next_mommogram" => "01\/2025",
              "mammogram_done_at" => "KRMC",
              "colonoscopy_done" => "Yes",
              "colon_test_type" => "Colonoscopy",
              "colonoscopy_done_on" => "01\/2014",
              "colonoscopy_done_at" => "due in 2024 last one was 2014 done by Dr Jacobson in Medford OR, report not available FIT kit given on 1\/17\/23",
              "next_colonoscopy" => "01\/2024",
              "colonoscopy_report_reviewed" => null,
              "colonoscopy_refused" => null,
              "colonoscopy_script" => null,
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "No",
              "hba1c_value" => "5.7",
              "hba1c_date" => "08\/2022",
              "diabetec_eye_exam" => null,
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "ratinavue_ordered" => null,
              "urine_microalbumin" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "fbs_in_year" => "Yes",
              "fbs_value" => "115",
              "fbs_date" => "08\/2022",
              "diabetec_ratinopathy" => null,
              "urine_microalbumin_value" => null,
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "108",
              "ldl_date" => "08\/2022",
              "patient_has_ascvd" => "No",
              "statin_prescribed" => "Yes",
              "statintype_dosage" => "Atorvastatin40 to 80 mg",
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "ldlvalue_190ormore" => "No",
              "pure_hypercholesterolemia" => "Yes",
              "active_diabetes" => null,
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_value" => "122\/80",
              "bp_date" => "08\/22\/2022",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "24.59",
              "completed" => "1"
            ],
            "physical_exam" => [
              "completed" => "0"
            ],
            "misc" => [
              "height" => "67",
              "weight" => "157",
              "asprin_use" => "check",
              "high_blood_pressure" => "check",
              "behavioral_counselling" => "check",
              "completed" => "1",
              "time_spent" => "10"
            ],
            "depression_phq9" => [
              "feltdown_depressed_hopeless" => 1,
              "little_interest_pleasure" => 0,
              "trouble_sleep" => 2,
              "tired_little_energy" => 0,
              "poor_over_appetite" => 0,
              "feeling_bad_failure" => 0,
              "trouble_concentrating" => 0,
              "slow_fidgety" => 0,
              "suicidal_thoughts" => 0,
              "problem_difficulty" => "Not difficult at all",
              "completed" => "1"
            ],
            "general_health" => [
              "health_level" => "Very Good",
              "feeling_caused_distress" => "No",
              "mouth_and_teeth" => "Good",
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Always"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "None"
            ],
            "cognitive_assessment" => [
              "year_recalled" => "correct",
              "month_recalled" => "correct",
              "hour_recalled" => "correct",
              "reverse_count" => "correct",
              "reverse_month" => "Correct",
              "address_recalled" => "correct",
              "completed" => "1"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "3",
              "exercise_intensity" => "moderate",
              "mins_of_exercise" => "60",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "7",
              "drinks_per_day" => "0",
              "drink_drive_yes" => "No",
              "completed" => "1"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => null,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "smoked_in_fifteen_years" => "No",
              "smokeless_product_use" => "No",
              "average_smoking_years" => null,
              "average_packs_per_day" => null,
              "quit_tobacco" => null,
              "completed" => "1"
            ],
            "nutrition" => [
              "fruits_vegs" => "1",
              "whole_grain_food" => "0",
              "high_fat_food" => "0",
              "sugar_beverages" => "0",
              "completed" => "1"
            ],
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "No",
              "flu_vaccine_recieved_on" => null,
              "flu_vaccine_recieved_at" => null,
              "flu_vaccine_refused" => "Yes",
              "flu_vaccine_script_given" => null,
              "pneumococcal_vaccine_recieved" => "No",
              "pneumococcal_vaccine_refused" => "Yes",
              "pneumococcal_vaccine_script_given" => null,
              "completed" => "1",
              "pneumococcal_prevnar_recieved_on" => null,
              "pneumococcal_prevnar_recieved_at" => null,
              "pneumococcal_ppsv23_recieved_on" => null,
              "pneumococcal_ppsv23_recieved_at" => null
            ]
        ];
        
      $data7 = 
        [
            "physical_exam" => [
              "completed" => "1"
            ],
            "medicareOptions" => "subsequent",
            "q" => "\/api\/questionaire\/update\/2835",
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "general_health" => [
              "feeling_caused_distress" => "No",
              "completed" => "1",
              "health_level" => "Excellent",
              "mouth_and_teeth" => "Good"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "None"
            ],
            "fall_screening" => [
              "fall_in_one_year" => "No",
              "number_of_falls" => null,
              "injury" => null,
              "physical_therapy" => null,
              "blackingout_from_bed" => "No",
              "unsteady_todo_things" => "No",
              "assistance_device" => "None",
              "completed" => "1"
            ],
            "depression_phq9" => [
              "referred_to_mh_professional" => null,
              "enroll_in_bhi" => null,
              "poor_over_appetite" => 0,
              "problem_difficulty" => "Not difficult at all",
              "suicidal_thoughts" => 0,
              "slow_fidgety" => 0,
              "feeling_bad_failure" => 0,
              "trouble_concentrating" => 0,
              "trouble_sleep" => 0,
              "tired_little_energy" => 0,
              "little_interest_pleasure" => 0,
              "feltdown_depressed_hopeless" => 0,
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Always"
            ],
            "cognitive_assessment" => [
              "year_recalled" => "correct",
              "month_recalled" => "correct",
              "hour_recalled" => "correct",
              "reverse_count" => "correct",
              "reverse_month" => "Correct",
              "address_recalled" => "correct",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "0",
              "drinks_per_day" => null,
              "drinks_per_occasion" => null,
              "average_usage" => null,
              "drink_drive_yes" => null,
              "completed" => "1"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => null,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "smoked_in_fifteen_years" => "No",
              "smokeless_product_use" => "No",
              "quit_tobacco" => null,
              "average_smoking_years" => null,
              "average_packs_per_day" => null,
              "completed" => "1"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "7",
              "exercise_intensity" => "light",
              "mins_of_exercise" => "30",
              "completed" => "1"
            ],
            "nutrition" => [
              "fruits_vegs" => "0",
              "whole_grain_food" => "1",
              "high_fat_food" => "1",
              "sugar_beverages" => "0",
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "Yes",
              "flu_vaccine_refused" => null,
              "flu_vaccine_script_given" => null,
              "flu_vaccine_recieved_on" => "11\/2024",
              "flu_vaccine_recieved_at" => "SAFEWAY",
              "pneumococcal_vaccine_recieved" => "Yes",
              "pneumococcal_vaccine_refused" => null,
              "pneumococcal_vaccine_script_given" => null,
              "pneumococcal_prevnar_recieved_at" => "UPTOWN PHARMACY",
              "pneumococcal_ppsv23_recieved_at" => "UPTOWN PHARMACY",
              "completed" => "1"
            ],
            "screening" => [
              "mammogram_done" => "Yes",
              "mammogram_refused" => null,
              "mammogram_script" => null,
              "mammogram_done_on" => "12\/2022",
              "next_mommogram" => "03\/2025",
              "mommogram_report_reviewed" => "0",
              "mammogram_done_at" => "KRMC",
              "completed" => "1",
              "colonoscopy_done" => "No",
              "colon_test_type" => null,
              "colonoscopy_done_on" => null,
              "colonoscopy_done_at" => null,
              "next_colonoscopy" => null,
              "colonoscopy_report_reviewed" => null
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "No",
              "fbs_in_year" => "Yes",
              "fbs_value" => "89",
              "fbs_date" => "04\/2023",
              "hba1c_value" => null,
              "hba1c_date" => null,
              "diabetec_eye_exam" => null,
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "ratinavue_ordered" => null,
              "urine_microalbumin" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "132",
              "ldl_date" => "04\/2023",
              "patient_has_ascvd" => "No",
              "ldlvalue_190ormore" => "No",
              "pure_hypercholesterolemia" => "No",
              "statin_prescribed" => null,
              "statintype_dosage" => null,
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "active_diabetes" => "No",
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_value" => "126\/62 mmHg",
              "bp_date" => "11\/01\/2023",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "25.90",
              "completed" => "1"
            ],
            "misc" => [
              "height" => "60 in",
              "weight" => "132.6",
              "behavioral_counselling" => "check",
              "time_spent" => "6",
              "asprin_use" => "check",
              "high_blood_pressure" => "check",
              "patient_on_asprin" => "No",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ]
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "Yes",
              "completed" => "1",
              "provider" => [
                [
                  "key" => 1,
                  "full_name" => "Dr. Hack",
                  "speciality" => "GASTROENTEROLOGIST"
                ],
                [
                  "key" => 2,
                  "full_name" => "Dr. Daulat",
                  "speciality" => "Dermatologist"
                ]
              ]
            ]
        ];

      $data8 = 
        [
            "physical_exam" => [
              "completed" => "1"
            ],
            "medicareOptions" => "initial",
            "q" => "\/api\/questionaire\/update\/2833",
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "0",
              "drinks_per_day" => null,
              "drinks_per_occasion" => null,
              "average_usage" => null,
              "drink_drive_yes" => null,
              "completed" => "1"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => null,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "smoked_in_fifteen_years" => "No",
              "smokeless_product_use" => "No",
              "quit_tobacco" => null,
              "average_smoking_years" => null,
              "average_packs_per_day" => null,
              "completed" => "0"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "Yes",
              "flu_vaccine_refused" => null,
              "flu_vaccine_script_given" => null,
              "flu_vaccine_recieved_on" => "09\/2023",
              "flu_vaccine_recieved_at" => "SAFEWAY",
              "pneumococcal_vaccine_recieved" => "Yes",
              "pneumococcal_vaccine_refused" => null,
              "pneumococcal_vaccine_script_given" => null,
              "pneumococcal_prevnar_recieved_on" => "06\/2023",
              "pneumococcal_prevnar_recieved_at" => "SAFEWAY",
              "completed" => "1"
            ],
            "screening" => [
              "colonoscopy_done" => "Yes",
              "colonoscopy_refused" => null,
              "refused_colonoscopy" => null,
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "colonoscopy_script" => null,
              "script_given_for" => null,
              "colon_test_type" => "Colonoscopy",
              "colonoscopy_done_on" => "04\/2021",
              "colonoscopy_done_at" => "KRMC",
              "next_colonoscopy" => "04\/2026",
              "colonoscopy_report_reviewed" => "0",
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "Yes",
              "fbs_in_year" => null,
              "fbs_value" => null,
              "fbs_date" => null,
              "hba1c_value" => "6.9",
              "hba1c_date" => "06\/2023",
              "diabetec_eye_exam" => "Yes",
              "diabetec_eye_exam_report" => "report_available",
              "ratinavue_ordered" => null,
              "eye_exam_doctor" => "Dr .Ryan Widdison",
              "eye_exam_facility" => "Mohave Eye Center",
              "eye_exam_date" => "11\/2022",
              "eye_exam_report_reviewed" => "1",
              "diabetec_ratinopathy" => "No",
              "urine_microalbumin" => "No",
              "urine_microalbumin_value" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => "No",
              "urine_microalbumin_inhibitor" => "ace_inhibitor",
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "83",
              "ldl_date" => "10\/2024",
              "patient_has_ascvd" => "No",
              "ldlvalue_190ormore" => "No",
              "pure_hypercholesterolemia" => "Yes",
              "active_diabetes" => null,
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "statin_prescribed" => "Yes",
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "statintype_dosage" => "Atorvastatin10 to 20 mg",
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_value" => "140\/71 mmHg",
              "bp_date" => "11\/08\/2023",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "24.48",
              "completed" => "1"
            ],
            "misc" => [
              "height" => "68 in",
              "weight" => "161",
              "behavioral_counselling" => "check",
              "asprin_use" => "check",
              "high_blood_pressure" => "check",
              "patient_on_asprin" => "Yes",
              "completed" => "0",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ]
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "Yes",
              "completed" => "1",
              "provider" => [
                [
                  "key" => 1,
                  "full_name" => "Dr. Garcia",
                  "speciality" => "Cardiologist"
                ],
                [
                  "key" => 2,
                  "full_name" => "Dr Kalantihi",
                  "speciality" => "Cardiologist"
                ],
                [
                  "key" => 3,
                  "full_name" => "Dr Kaplan",
                  "speciality" => "GASTROENTEROLOGIST"
                ],
                [
                  "key" => 4,
                  "full_name" => "Dr. Jalbert",
                  "speciality" => "Psychologist"
                ],
                [
                  "key" => 5,
                  "full_name" => "Dr. Phillip Glotser",
                  "speciality" => "Neurologist"
                ],
                [
                  "key" => 6,
                  "full_name" => "Dr. Matheny",
                  "speciality" => "Pulmonology"
                ],
                [
                  "key" => 7,
                  "full_name" => "Dr Widdison",
                  "speciality" => "Ophthalmologist"
                ]
              ]
            ]
        ];

      $data9 = 
        [
            "alcohol_use" => [
              "days_of_alcoholuse" => "0",
              "drinks_per_day" => null,
              "drinks_per_occasion" => null,
              "average_usage" => null,
              "drink_drive_yes" => null,
              "completed" => "1"
            ],
            "medicareOptions" => "subsequent",
            "q" => "\/api\/questionaire\/update\/2829",
            "tobacco_use" => [
              "average_packs_per_year" => null,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "smoked_in_fifteen_years" => "No",
              "smokeless_product_use" => "No",
              "quit_tobacco" => null,
              "average_smoking_years" => null,
              "average_packs_per_day" => null,
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "Yes",
              "flu_vaccine_refused" => null,
              "flu_vaccine_script_given" => null,
              "flu_vaccine_recieved_on" => "12\/2023",
              "flu_vaccine_recieved_at" => "SAFEWAY",
              "pneumococcal_vaccine_recieved" => "Yes",
              "pneumococcal_vaccine_refused" => null,
              "pneumococcal_vaccine_script_given" => null,
              "pneumococcal_prevnar_recieved_on" => "10\/2019",
              "pneumococcal_prevnar_recieved_at" => "SAFEWAY",
              "completed" => "0"
            ],
            "screening" => [
              "colonoscopy_done" => "Yes",
              "colon_test_type" => "Cologuard",
              "colonoscopy_done_on" => "04\/2023",
              "colonoscopy_done_at" => null,
              "next_colonoscopy" => "04\/2025",
              "colonoscopy_report_reviewed" => null,
              "completed" => "0",
              "mammogram_done" => "Yes",
              "mammogram_refused" => null,
              "mammogram_script" => null,
              "mammogram_done_on" => "12\/2022",
              "next_mommogram" => "03\/2025",
              "mammogram_done_at" => "KRMC",
              "mommogram_report_reviewed" => "0",
              "colonoscopy_refused" => null,
              "refused_colonoscopy" => null,
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "colonoscopy_script" => null,
              "script_given_for" => null
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "Yes",
              "fbs_in_year" => null,
              "fbs_value" => null,
              "fbs_date" => null,
              "hba1c_value" => "7.6",
              "hba1c_date" => "06\/2023",
              "diabetec_eye_exam" => "Yes",
              "diabetec_eye_exam_report" => "report_available",
              "ratinavue_ordered" => null,
              "eye_exam_doctor" => "Kelly Corbridge",
              "eye_exam_facility" => "Desert Family Eye Center",
              "eye_exam_date" => "04\/2023",
              "eye_exam_report_reviewed" => "1",
              "diabetec_ratinopathy" => "No",
              "urine_microalbumin" => "Yes",
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "urine_microalbumin_value" => "30",
              "urine_microalbumin_date" => "02\/2023",
              "urine_microalbumin_report" => "Positive",
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "60",
              "ldl_date" => "02\/2023",
              "patient_has_ascvd" => "No",
              "ldlvalue_190ormore" => "No",
              "active_diabetes" => null,
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "pure_hypercholesterolemia" => "Yes",
              "statin_prescribed" => "Yes",
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "statintype_dosage" => "Simvastatin20 to 40 mg",
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_value" => "140\/88 mmHg",
              "bp_date" => "09\/07\/2023",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "39.45",
              "completed" => "1"
            ],
            "physical_exam" => [
              "completed" => "1"
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "Yes",
              "completed" => "1",
              "provider" => [
                [
                  "key" => 1,
                  "full_name" => "Dr. Bellew",
                  "speciality" => "Dermatology"
                ],
                [
                  "key" => 2,
                  "full_name" => "Dr. Agarwal",
                  "speciality" => "ENT"
                ],
                [
                  "key" => 3,
                  "full_name" => "Dr. Mohtaseb",
                  "speciality" => "ONCOLOGY"
                ]
              ]
            ],
            "misc" => [
              "height" => "60",
              "weight" => "202",
              "asprin_use" => "check",
              "high_blood_pressure" => "check",
              "behavioral_counselling" => "check",
              "patient_on_asprin" => "Yes",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ]
            ]
        ];

      $data10 = 
        [
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "medicareOptions" => "initial",
            "q" => "\/api\/questionaire\/update\/2828",
            "physical_exam" => [
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "No",
              "flu_vaccine_recieved_on" => null,
              "flu_vaccine_recieved_at" => null,
              "pneumococcal_vaccine_recieved" => "Yes",
              "pneumococcal_prevnar_recieved_on" => null,
              "pneumococcal_prevnar_recieved_at" => null,
              "pneumococcal_ppsv23_recieved_on" => null,
              "pneumococcal_ppsv23_recieved_at" => null,
              "completed" => "1",
              "pneumococcal_vaccine_refused" => null,
              "pneumococcal_vaccine_script_given" => null
            ],
            "screening" => [
              "colonoscopy_done" => "Yes",
              "colonoscopy_refused" => null,
              "refused_colonoscopy" => null,
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "colonoscopy_script" => null,
              "script_given_for" => null,
              "colon_test_type" => "Colonoscopy",
              "colonoscopy_done_on" => "05\/2023",
              "colonoscopy_done_at" => "AIMS",
              "next_colonoscopy" => "05\/2033",
              "colonoscopy_report_reviewed" => "0",
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "No",
              "hba1c_value" => null,
              "hba1c_date" => null,
              "diabetec_eye_exam" => null,
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "ratinavue_ordered" => null,
              "urine_microalbumin" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "fbs_in_year" => "Yes",
              "fbs_value" => "107",
              "fbs_date" => "05\/2023",
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "patient_has_ascvd" => "No",
              "ldlvalue_190ormore" => "No",
              "pure_hypercholesterolemia" => "Yes",
              "active_diabetes" => null,
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "statin_prescribed" => "Yes",
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "ldl_date" => "05\/2023",
              "ldl_value" => "97",
              "statintype_dosage" => "Atorvastatin40 to 80 mg",
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_value" => "140\/86 mmHg",
              "bp_date" => "04\/24\/2023",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "26.87",
              "completed" => "1"
            ],
            "misc" => [
              "height" => "75 in",
              "weight" => "215",
              "behavioral_counselling" => "check",
              "patient_on_asprin" => "No",
              "high_blood_pressure" => "check",
              "asprin_use" => "check",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ],
              "time_spent" => "6"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => 50,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "smoked_in_fifteen_years" => "Yes",
              "smokeless_product_use" => "No",
              "quit_tobacco" => null,
              "average_smoking_years" => "50",
              "average_packs_per_day" => "1",
              "completed" => "1"
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "Yes",
              "completed" => "1",
              "provider" => [
                [
                  "key" => 1,
                  "full_name" => "Dr. Asokan",
                  "speciality" => "Allergist And Immunologist"
                ],
                [
                  "key" => 2,
                  "full_name" => "Dr. Azam",
                  "speciality" => "GASTROENTEROLOGIST"
                ]
              ]
            ],
            "general_health" => [
              "feeling_caused_distress" => "No",
              "completed" => "1",
              "health_level" => "Very Good",
              "mouth_and_teeth" => "Good"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "None"
            ],
            "fall_screening" => [
              "fall_in_one_year" => "No",
              "number_of_falls" => null,
              "injury" => null,
              "physical_therapy" => null,
              "blackingout_from_bed" => "No",
              "unsteady_todo_things" => "No",
              "assistance_device" => "None",
              "completed" => "1"
            ],
            "depression_phq9" => [
              "referred_to_mh_professional" => null,
              "enroll_in_bhi" => null,
              "feltdown_depressed_hopeless" => 0,
              "feeling_bad_failure" => 0,
              "slow_fidgety" => 0,
              "suicidal_thoughts" => 0,
              "problem_difficulty" => "Not difficult at all",
              "tired_little_energy" => 0,
              "little_interest_pleasure" => 0,
              "trouble_concentrating" => 0,
              "poor_over_appetite" => 0,
              "trouble_sleep" => 0,
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Always"
            ],
            "cognitive_assessment" => [
              "year_recalled" => "correct",
              "month_recalled" => "correct",
              "hour_recalled" => "correct",
              "reverse_month" => "Correct",
              "address_recalled" => "correct",
              "completed" => "1"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "0",
              "mins_of_exercise" => null,
              "exercise_intensity" => null,
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "7",
              "drinks_per_day" => "50",
              "drink_drive_yes" => "No",
              "drinks_per_occasion" => "10",
              "completed" => "1"
            ],
            "nutrition" => [
              "fruits_vegs" => "1",
              "whole_grain_food" => "0",
              "high_fat_food" => "1",
              "sugar_beverages" => "0",
              "completed" => "1"
            ]
        ];

      $data11 = 
        [
            "physical_exam" => [
              "completed" => "1"
            ],
            "medicareOptions" => "initial",
            "q" => "\/api\/questionaire\/update\/2807",
            "other_Provider" => [
              "other_provider_beside_pcp" => "No",
              "full_name" => null,
              "speciality" => null,
              "completed" => "1",
              "provider" => [
                
              ]
            ],
            "misc" => [
              "height" => "61 in",
              "weight" => "164.1",
              "patient_on_asprin" => "No",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ],
              "time_spent" => "6",
              "asprin_use" => "check",
              "behavioral_counselling" => "check",
              "high_blood_pressure" => "check"
            ],
            "bp_assessment" => [
              "bp_value" => "130\/84",
              "bp_date" => "12\/05\/2023",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "31.01",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "0",
              "drinks_per_day" => null,
              "drinks_per_occasion" => null,
              "average_usage" => null,
              "drink_drive_yes" => null,
              "completed" => "1"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => null,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "smoked_in_fifteen_years" => "No",
              "smokeless_product_use" => "No",
              "quit_tobacco" => null,
              "average_smoking_years" => null,
              "average_packs_per_day" => null,
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "Yes",
              "flu_vaccine_refused" => null,
              "flu_vaccine_script_given" => null,
              "flu_vaccine_recieved_on" => "12\/2023",
              "flu_vaccine_recieved_at" => "NAMG",
              "pneumococcal_vaccine_recieved" => "No",
              "pneumococcal_prevnar_recieved_on" => null,
              "pneumococcal_prevnar_recieved_at" => null,
              "pneumococcal_ppsv23_recieved_on" => null,
              "pneumococcal_ppsv23_recieved_at" => null,
              "pneumococcal_vaccine_refused" => "No",
              "pneumococcal_vaccine_script_given" => "No",
              "completed" => "1"
            ],
            "screening" => [
              "colonoscopy_done" => "No",
              "colon_test_type" => null,
              "colonoscopy_done_on" => null,
              "colonoscopy_done_at" => null,
              "next_colonoscopy" => null,
              "colonoscopy_report_reviewed" => null,
              "colonoscopy_refused" => "No",
              "refused_colonoscopy" => null,
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "script_given_for" => null,
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "Yes",
              "fbs_in_year" => null,
              "fbs_value" => null,
              "fbs_date" => null,
              "hba1c_value" => "7.5",
              "hba1c_date" => "12\/2023",
              "diabetec_eye_exam" => "No",
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "diabetec_ratinopathy" => null,
              "urine_microalbumin" => "Yes",
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "urine_microalbumin_value" => "80",
              "urine_microalbumin_date" => "12\/2023",
              "urine_microalbumin_report" => "Positive",
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "122",
              "ldl_date" => "12\/2023",
              "patient_has_ascvd" => "No",
              "ldlvalue_190ormore" => "No",
              "pure_hypercholesterolemia" => "No",
              "statin_prescribed" => null,
              "statintype_dosage" => null,
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "active_diabetes" => "Yes",
              "diabetes_patient_age" => "No",
              "ldl_range_in_past_two_years" => null,
              "completed" => "1"
            ],
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "general_health" => [
              "feeling_caused_distress" => "No",
              "completed" => "1",
              "health_level" => "Very Good",
              "mouth_and_teeth" => "Fair"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "Some"
            ],
            "fall_screening" => [
              "fall_in_one_year" => "Yes",
              "number_of_falls" => "One",
              "injury" => "No",
              "physical_therapy" => null,
              "blackingout_from_bed" => "No",
              "unsteady_todo_things" => "Yes",
              "assistance_device" => "Cane",
              "completed" => "1"
            ],
            "depression_phq9" => [
              "referred_to_mh_professional" => null,
              "enroll_in_bhi" => null,
              "feltdown_depressed_hopeless" => 0,
              "trouble_sleep" => 0,
              "little_interest_pleasure" => 0,
              "tired_little_energy" => 0,
              "poor_over_appetite" => 0,
              "feeling_bad_failure" => 0,
              "slow_fidgety" => 0,
              "suicidal_thoughts" => 0,
              "problem_difficulty" => "Not difficult at all",
              "trouble_concentrating" => 1,
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Always"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "7",
              "exercise_intensity" => "light",
              "mins_of_exercise" => "20",
              "completed" => "1"
            ],
            "nutrition" => [
              "fruits_vegs" => "1",
              "whole_grain_food" => "1",
              "high_fat_food" => "0",
              "sugar_beverages" => "0",
              "completed" => "1"
            ]
        ];

      $data12 = 
        [
            "physical_exam" => [
              "completed" => "1"
            ],
            "medicareOptions" => "initial",
            "q" => "\/api\/questionaire\/update\/2807",
            "other_Provider" => [
              "other_provider_beside_pcp" => "No",
              "full_name" => null,
              "speciality" => null,
              "completed" => "1",
              "provider" => [
                
              ]
            ],
            "misc" => [
              "height" => "61 in",
              "weight" => "164.1",
              "patient_on_asprin" => "No",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ],
              "time_spent" => "6",
              "asprin_use" => "check",
              "behavioral_counselling" => "check",
              "high_blood_pressure" => "check"
            ],
            "bp_assessment" => [
              "bp_value" => "130\/84",
              "bp_date" => "12\/05\/2023",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "31.01",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "0",
              "drinks_per_day" => null,
              "drinks_per_occasion" => null,
              "average_usage" => null,
              "drink_drive_yes" => null,
              "completed" => "1"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => null,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "smoked_in_fifteen_years" => "No",
              "smokeless_product_use" => "No",
              "quit_tobacco" => null,
              "average_smoking_years" => null,
              "average_packs_per_day" => null,
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "Yes",
              "flu_vaccine_refused" => null,
              "flu_vaccine_script_given" => null,
              "flu_vaccine_recieved_on" => "12\/2023",
              "flu_vaccine_recieved_at" => "NAMG",
              "pneumococcal_vaccine_recieved" => "No",
              "pneumococcal_prevnar_recieved_on" => null,
              "pneumococcal_prevnar_recieved_at" => null,
              "pneumococcal_ppsv23_recieved_on" => null,
              "pneumococcal_ppsv23_recieved_at" => null,
              "pneumococcal_vaccine_refused" => "No",
              "pneumococcal_vaccine_script_given" => "No",
              "completed" => "1"
            ],
            "screening" => [
              "colonoscopy_done" => "No",
              "colon_test_type" => null,
              "colonoscopy_done_on" => null,
              "colonoscopy_done_at" => null,
              "next_colonoscopy" => null,
              "colonoscopy_report_reviewed" => null,
              "colonoscopy_refused" => "No",
              "refused_colonoscopy" => null,
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "script_given_for" => null,
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "Yes",
              "fbs_in_year" => null,
              "fbs_value" => null,
              "fbs_date" => null,
              "hba1c_value" => "7.5",
              "hba1c_date" => "12\/2023",
              "diabetec_eye_exam" => "No",
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "diabetec_ratinopathy" => null,
              "urine_microalbumin" => "Yes",
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "urine_microalbumin_value" => "80",
              "urine_microalbumin_date" => "12\/2023",
              "urine_microalbumin_report" => "Positive",
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "122",
              "ldl_date" => "12\/2023",
              "patient_has_ascvd" => "No",
              "ldlvalue_190ormore" => "No",
              "pure_hypercholesterolemia" => "No",
              "statin_prescribed" => null,
              "statintype_dosage" => null,
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "active_diabetes" => "Yes",
              "diabetes_patient_age" => "No",
              "ldl_range_in_past_two_years" => null,
              "completed" => "1"
            ],
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "general_health" => [
              "feeling_caused_distress" => "No",
              "completed" => "1",
              "health_level" => "Very Good",
              "mouth_and_teeth" => "Fair"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "Some"
            ],
            "fall_screening" => [
              "fall_in_one_year" => "Yes",
              "number_of_falls" => "One",
              "injury" => "No",
              "physical_therapy" => null,
              "blackingout_from_bed" => "No",
              "unsteady_todo_things" => "Yes",
              "assistance_device" => "Cane",
              "completed" => "1"
            ],
            "depression_phq9" => [
              "referred_to_mh_professional" => null,
              "enroll_in_bhi" => null,
              "feltdown_depressed_hopeless" => 0,
              "trouble_sleep" => 0,
              "little_interest_pleasure" => 0,
              "tired_little_energy" => 0,
              "poor_over_appetite" => 0,
              "feeling_bad_failure" => 0,
              "slow_fidgety" => 0,
              "suicidal_thoughts" => 0,
              "problem_difficulty" => "Not difficult at all",
              "trouble_concentrating" => 1,
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Always"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "7",
              "exercise_intensity" => "light",
              "mins_of_exercise" => "20",
              "completed" => "1"
            ],
            "nutrition" => [
              "fruits_vegs" => "1",
              "whole_grain_food" => "1",
              "high_fat_food" => "0",
              "sugar_beverages" => "0",
              "completed" => "1"
            ]
        ];

      $data13 =
        [
            "alcohol_use" => [
              "days_of_alcoholuse" => "0",
              "drinks_per_day" => null,
              "drinks_per_occasion" => null,
              "average_usage" => null,
              "drink_drive_yes" => null,
              "completed" => "1"
            ],
            "medicareOptions" => "initial",
            "q" => "\/api\/questionaire\/update\/2803",
            "tobacco_use" => [
              "average_packs_per_year" => 50,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "Yes",
              "smoked_in_fifteen_years" => "Yes",
              "smokeless_product_use" => "No",
              "average_smoking_years" => "50",
              "average_packs_per_day" => "1",
              "completed" => "0"
            ],
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "physical_exam" => [
              "completed" => "1"
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "Yes",
              "completed" => "1",
              "provider" => [
                [
                  "key" => 1,
                  "full_name" => "Dr. Khan",
                  "speciality" => "GASTROENTEROLOGIST"
                ],
                [
                  "key" => 2,
                  "full_name" => "Dr. Ngo",
                  "speciality" => "Physical Medicine & Rehabilitation Specialist"
                ]
              ]
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "No",
              "flu_vaccine_recieved_on" => null,
              "flu_vaccine_recieved_at" => null,
              "pneumococcal_vaccine_recieved" => "No",
              "pneumococcal_prevnar_recieved_on" => null,
              "pneumococcal_prevnar_recieved_at" => null,
              "pneumococcal_ppsv23_recieved_on" => null,
              "pneumococcal_ppsv23_recieved_at" => null,
              "completed" => "0"
            ],
            "screening" => [
              "colonoscopy_done" => "Yes",
              "colonoscopy_refused" => null,
              "refused_colonoscopy" => null,
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "colonoscopy_script" => null,
              "script_given_for" => null,
              "colon_test_type" => "FIT Test",
              "colonoscopy_done_on" => "04\/2023",
              "colonoscopy_done_at" => "NAMG",
              "next_colonoscopy" => "04\/2024",
              "colonoscopy_report_reviewed" => "0",
              "mammogram_done" => "No",
              "mammogram_done_on" => null,
              "mammogram_done_at" => null,
              "next_mommogram" => null,
              "mommogram_report_reviewed" => null,
              "completed" => "0"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "No",
              "hba1c_value" => null,
              "hba1c_date" => null,
              "diabetec_eye_exam" => null,
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "ratinavue_ordered" => null,
              "urine_microalbumin" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "fbs_in_year" => "Yes",
              "fbs_value" => "87",
              "fbs_date" => "04\/2023",
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "121",
              "ldl_date" => "04\/2023",
              "patient_has_ascvd" => "No",
              "ldlvalue_190ormore" => "No",
              "pure_hypercholesterolemia" => "No",
              "active_diabetes" => "No",
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "statin_prescribed" => null,
              "statintype_dosage" => null,
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_value" => "132\/84 mmHg",
              "bp_date" => "08\/30\/2023",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "36.85",
              "completed" => "1"
            ],
            "misc" => [
              "patient_on_asprin" => "No",
              "high_blood_pressure" => "check",
              "asprin_use" => "check",
              "behavioral_counselling" => "check",
              "height" => "63 in",
              "weight" => "208",
              "completed" => "0",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ]
            ]
        ];

      $data14 = 
        [
            "physical_exam" => [
              "completed" => "1"
            ],
            "medicareOptions" => "initial",
            "q" => "\/api\/questionaire\/update\/2799",
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "0",
              "drinks_per_day" => null,
              "drinks_per_occasion" => null,
              "average_usage" => null,
              "drink_drive_yes" => null,
              "completed" => "1"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => 6.5,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "smoked_in_fifteen_years" => "Yes",
              "smokeless_product_use" => "No",
              "completed" => "1",
              "quit_tobacco" => null,
              "average_smoking_years" => "13",
              "average_packs_per_day" => "0.5"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "No",
              "flu_vaccine_recieved_on" => null,
              "flu_vaccine_recieved_at" => null,
              "flu_vaccine_refused" => "Yes",
              "flu_vaccine_script_given" => null,
              "pneumococcal_vaccine_recieved" => "Yes",
              "pneumococcal_vaccine_refused" => null,
              "pneumococcal_vaccine_script_given" => null,
              "completed" => "1",
              "pneumococcal_prevnar_recieved_on" => null,
              "pneumococcal_prevnar_recieved_at" => null,
              "pneumococcal_ppsv23_recieved_on" => null,
              "pneumococcal_ppsv23_recieved_at" => null
            ],
            "screening" => [
              "mammogram_done" => "No",
              "mammogram_done_on" => null,
              "mammogram_done_at" => null,
              "next_mommogram" => null,
              "mommogram_report_reviewed" => null,
              "mammogram_refused" => "No",
              "colonoscopy_done" => "Yes",
              "colonoscopy_refused" => null,
              "refused_colonoscopy" => null,
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "colonoscopy_script" => null,
              "script_given_for" => null,
              "colon_test_type" => "Colonoscopy",
              "colonoscopy_done_on" => "01\/2022",
              "colonoscopy_done_at" => "Kaiser facility in California",
              "next_colonoscopy" => "01\/2032",
              "colonoscopy_report_reviewed" => null,
              "mammogram_script" => "Yes",
              "comments" => "Colonoscopy - 2022 at Kaiser in California need record",
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "No",
              "hba1c_value" => null,
              "hba1c_date" => null,
              "diabetec_eye_exam" => null,
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "ratinavue_ordered" => null,
              "urine_microalbumin" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "fbs_in_year" => "Yes",
              "fbs_date" => "12\/2023",
              "fbs_value" => "74",
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "98.80",
              "ldl_date" => "12\/2022",
              "patient_has_ascvd" => "No",
              "ldlvalue_190ormore" => "No",
              "pure_hypercholesterolemia" => "No",
              "statin_prescribed" => null,
              "statintype_dosage" => null,
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "active_diabetes" => "No",
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_value" => "144\/92 mmHg",
              "bp_date" => "10\/17\/2023",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "31.02",
              "completed" => "1"
            ],
            "misc" => [
              "height" => "64 in",
              "patient_on_asprin" => "No",
              "high_blood_pressure" => "check",
              "asprin_use" => "check",
              "behavioral_counselling" => "check",
              "weight" => "180.7",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ],
              "time_spent" => "6"
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "No",
              "full_name" => null,
              "speciality" => null,
              "completed" => "1",
              "provider" => [
                
              ]
            ],
            "general_health" => [
              "feeling_caused_distress" => "No",
              "completed" => "1",
              "mouth_and_teeth" => "Fair",
              "health_level" => "Good"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "None"
            ],
            "fall_screening" => [
              "fall_in_one_year" => "No",
              "number_of_falls" => null,
              "injury" => null,
              "physical_therapy" => null,
              "assistance_device" => "None",
              "blackingout_from_bed" => "No",
              "unsteady_todo_things" => "No",
              "completed" => "1"
            ],
            "depression_phq9" => [
              "referred_to_mh_professional" => null,
              "enroll_in_bhi" => null,
              "poor_over_appetite" => 0,
              "little_interest_pleasure" => 0,
              "tired_little_energy" => 0,
              "feeling_bad_failure" => 0,
              "slow_fidgety" => 0,
              "suicidal_thoughts" => 0,
              "problem_difficulty" => "Not difficult at all",
              "trouble_concentrating" => 0,
              "trouble_sleep" => 0,
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Always"
            ],
            "cognitive_assessment" => [
              "year_recalled" => "correct",
              "month_recalled" => "correct",
              "hour_recalled" => "correct",
              "reverse_count" => "correct",
              "reverse_month" => "Correct",
              "address_recalled" => "correct",
              "completed" => "1"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "7",
              "mins_of_exercise" => "60",
              "exercise_intensity" => "light",
              "completed" => "1"
            ],
            "nutrition" => [
              "fruits_vegs" => "1",
              "whole_grain_food" => "1",
              "high_fat_food" => "1",
              "sugar_beverages" => "0",
              "completed" => "1"
            ]
        ];


      $data15 = 
        [
            "fall_screening" => [
              "completed" => "1",
              "fall_in_one_year" => "No",
              "number_of_falls" => null,
              "injury" => null,
              "physical_therapy" => null,
              "blackingout_from_bed" => "No",
              "unsteady_todo_things" => "Yes",
              "assistance_device" => "Cane"
            ],
            "medicareOptions" => "subsequent",
            "q" => "\/api\/questionaire\/update\/2792",
            "physical_exam" => [
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "patient_has_ascvd" => "Yes",
              "ldlvalue_190ormore" => null,
              "pure_hypercholesterolemia" => null,
              "active_diabetes" => null,
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "statin_prescribed" => "Yes",
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "statintype_dosage" => "Rosuvastatin5 to 10 mg",
              "completed" => "1",
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "95",
              "ldl_date" => "08\/2023"
            ],
            "general_health" => [
              "feeling_caused_distress" => "No",
              "mouth_and_teeth" => "Good",
              "completed" => "1",
              "health_level" => "Good"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "Some"
            ],
            "depression_phq9" => [
              "referred_to_mh_professional" => null,
              "enroll_in_bhi" => null,
              "feltdown_depressed_hopeless" => 0,
              "little_interest_pleasure" => 0,
              "poor_over_appetite" => 0,
              "completed" => "1",
              "trouble_sleep" => 1,
              "feeling_bad_failure" => 0,
              "trouble_concentrating" => 0,
              "slow_fidgety" => 0,
              "suicidal_thoughts" => 0,
              "problem_difficulty" => "Not difficult at all",
              "tired_little_energy" => 1
            ],
            "nutrition" => [
              "fruits_vegs" => "1",
              "high_fat_food" => "0",
              "whole_grain_food" => "1",
              "sugar_beverages" => "0",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "0",
              "drinks_per_day" => null,
              "drinks_per_occasion" => null,
              "average_usage" => null,
              "drink_drive_yes" => null,
              "completed" => "1"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => null,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "smoked_in_fifteen_years" => "No",
              "smokeless_product_use" => "No",
              "quit_tobacco" => null,
              "average_smoking_years" => null,
              "average_packs_per_day" => null,
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "No",
              "flu_vaccine_recieved_on" => null,
              "flu_vaccine_recieved_at" => null,
              "flu_vaccine_refused" => "Yes",
              "flu_vaccine_script_given" => null,
              "pneumococcal_vaccine_recieved" => "No",
              "pneumococcal_prevnar_recieved_on" => null,
              "pneumococcal_prevnar_recieved_at" => null,
              "pneumococcal_ppsv23_recieved_on" => null,
              "pneumococcal_ppsv23_recieved_at" => null,
              "pneumococcal_vaccine_refused" => "Yes",
              "pneumococcal_vaccine_script_given" => null,
              "completed" => "1"
            ],
            "screening" => [
              "colonoscopy_done" => "No",
              "colon_test_type" => null,
              "colonoscopy_done_on" => null,
              "colonoscopy_done_at" => null,
              "next_colonoscopy" => null,
              "colonoscopy_report_reviewed" => null,
              "colonoscopy_refused" => "No",
              "refused_colonoscopy" => null,
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "script_given_for" => null,
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Always"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "0",
              "mins_of_exercise" => null,
              "exercise_intensity" => null,
              "completed" => "1"
            ],
            "cognitive_assessment" => [
              "year_recalled" => "correct",
              "month_recalled" => "correct",
              "hour_recalled" => "correct",
              "reverse_month" => "Correct",
              "address_recalled" => "correct",
              "reverse_count" => "correct",
              "completed" => "1"
            ],
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "misc" => [
              "time_spent" => "6",
              "asprin_use" => "check",
              "high_blood_pressure" => "check",
              "patient_on_asprin" => "No",
              "behavioral_counselling" => "check",
              "height" => "63.4",
              "weight" => "199.9",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ]
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "Yes",
              "completed" => "1",
              "provider" => [
                [
                  "key" => 1,
                  "full_name" => "Dr Pozun",
                  "speciality" => "cardiologist"
                ],
                [
                  "key" => 2,
                  "full_name" => "Dr Burhan",
                  "speciality" => "rheumatologist"
                ],
                [
                  "key" => 3,
                  "full_name" => "Dr. Malik",
                  "speciality" => "Hematologist\/oncologist"
                ]
              ]
            ],
            "weight_assessment" => [
              "bmi_value" => "34.96",
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "No",
              "hba1c_value" => null,
              "hba1c_date" => null,
              "diabetec_eye_exam" => null,
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "ratinavue_ordered" => null,
              "urine_microalbumin" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "fbs_in_year" => "Yes",
              "fbs_value" => "91",
              "fbs_date" => "12\/2023",
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_value" => "132\/78",
              "bp_date" => "12\/12\/2023",
              "completed" => "1"
            ]
        ];

      $data16 = 
        [
            "fall_screening" => [
              "completed" => "1",
              "fall_in_one_year" => "No",
              "number_of_falls" => null,
              "injury" => null,
              "physical_therapy" => null,
              "blackingout_from_bed" => "No",
              "unsteady_todo_things" => "Yes",[
                "alcohol_use" => [
                  "days_of_alcoholuse" => "0",
                  "drinks_per_day" => null,
                  "drinks_per_occasion" => null,
                  "average_usage" => null,
                  "drink_drive_yes" => null,
                  "completed" => "1"
                ],
                "medicareOptions" => "initial",
                "q" => "\/api\/questionaire\/update\/2788",
                "tobacco_use" => [
                  "average_packs_per_year" => null,
                  "perform_ldct" => null,
                  "smoked_in_thirty_days" => "No",
                  "smoked_in_fifteen_years" => "No",
                  "smokeless_product_use" => "No",
                  "quit_tobacco" => null,
                  "average_smoking_years" => null,
                  "average_packs_per_day" => null,
                  "completed" => "1"
                ],
                "seatbelt_use" => [
                  "wear_seat_belt" => "Yes",
                  "completed" => "1"
                ],
                "immunization" => [
                  "flu_vaccine_recieved" => "No",
                  "flu_vaccine_recieved_on" => null,
                  "flu_vaccine_recieved_at" => null,
                  "pneumococcal_vaccine_recieved" => "No",
                  "pneumococcal_prevnar_recieved_on" => null,
                  "pneumococcal_prevnar_recieved_at" => null,
                  "pneumococcal_ppsv23_recieved_on" => null,
                  "pneumococcal_ppsv23_recieved_at" => null,
                  "completed" => "1",
                  "flu_vaccine_refused" => "Yes",
                  "flu_vaccine_script_given" => null,
                  "comments" => "FLU shot => allergic\nSensitive with vaccines",
                  "pneumococcal_vaccine_refused" => "Yes",
                  "pneumococcal_vaccine_script_given" => null
                ],
                "screening" => [
                  "mammogram_done" => "No",
                  "mammogram_done_on" => null,
                  "mammogram_done_at" => null,
                  "next_mommogram" => null,
                  "mommogram_report_reviewed" => null,
                  "mammogram_refused" => "No",
                  "comments" => "Mammogram => s\/p right mastectomy left mammogram not done since treatment started in 2017",
                  "colonoscopy_done" => "No",
                  "colon_test_type" => null,
                  "colonoscopy_done_on" => null,
                  "colonoscopy_done_at" => null,
                  "next_colonoscopy" => null,
                  "colonoscopy_report_reviewed" => null,
                  "colonoscopy_refused" => null,
                  "refused_colonoscopy" => null,
                  "refused_fit_test" => null,
                  "refused_cologuard" => null,
                  "colonoscopy_script" => null,
                  "script_given_for" => null,
                  "completed" => "1"
                ],
                "diabetes" => [
                  "ckd_stage_4" => null,
                  "diabetec_patient" => "No",
                  "hba1c_value" => null,
                  "hba1c_date" => null,
                  "diabetec_eye_exam" => null,
                  "diabetec_eye_exam_report" => null,
                  "eye_exam_doctor" => null,
                  "eye_exam_facility" => null,
                  "eye_exam_date" => null,
                  "eye_exam_report_reviewed" => null,
                  "ratinavue_ordered" => null,
                  "urine_microalbumin" => null,
                  "urine_microalbumin_date" => null,
                  "urine_microalbumin_report" => null,
                  "urine_microalbumin_ordered" => null,
                  "urine_microalbumin_inhibitor" => null,
                  "fbs_in_year" => "Yes",
                  "fbs_value" => "86",
                  "fbs_date" => "11\/2023",
                  "completed" => "1"
                ],
                "cholesterol_assessment" => [
                  "ldl_in_last_12months" => "Yes",
                  "ldl_value" => "116.40",
                  "ldl_date" => "11\/2023",
                  "patient_has_ascvd" => "No",
                  "ldlvalue_190ormore" => "No",
                  "pure_hypercholesterolemia" => "No",
                  "statin_prescribed" => null,
                  "statintype_dosage" => null,
                  "medical_reason_for_nostatin0" => null,
                  "medical_reason_for_nostatin1" => null,
                  "medical_reason_for_nostatin2" => null,
                  "medical_reason_for_nostatin3" => null,
                  "medical_reason_for_nostatin4" => null,
                  "medical_reason_for_nostatin5" => null,
                  "active_diabetes" => "No",
                  "diabetes_patient_age" => null,
                  "ldl_range_in_past_two_years" => null,
                  "completed" => "1"
                ],
                "bp_assessment" => [
                  "bp_value" => "106\/70 mmHg",
                  "bp_date" => "11\/20\/2023",
                  "completed" => "1"
                ],
                "weight_assessment" => [
                  "bmi_value" => "21.51",
                  "completed" => "1"
                ],
                "physical_exam" => [
                  "completed" => "1"
                ],
                "misc" => [
                  "height" => "62 in",
                  "weight" => "117.6",
                  "patient_on_asprin" => "No",
                  "high_blood_pressure" => "check",
                  "asprin_use" => "check",
                  "behavioral_counselling" => "check",
                  "completed" => "1",
                  "Suppliers" => [
                    
                  ],
                  "Pharmacy" => [
                    
                  ],
                  "time_spent" => "12"
                ],
                "other_Provider" => [
                  "other_provider_beside_pcp" => "Yes",
                  "completed" => "1",
                  "provider" => [
                    [
                      "key" => 1,
                      "full_name" => "Dr Rivera",
                      "speciality" => "ONCOLOGIST"
                    ],
                    [
                      "key" => 2,
                      "full_name" => "Dr. Y. Malik",
                      "speciality" => "OBGYN"
                    ],
                    [
                      "key" => 3,
                      "full_name" => "Dr. Ohri",
                      "speciality" => "Neurologist"
                    ]
                  ]
                ],
                "fall_screening" => [
                  "completed" => "1",
                  "fall_in_one_year" => "No",
                  "number_of_falls" => null,
                  "injury" => null,
                  "physical_therapy" => null,
                  "blackingout_from_bed" => "Yes",
                  "unsteady_todo_things" => "No",
                  "assistance_device" => "None"
                ],
                "depression_phq9" => [
                  "referred_to_mh_professional" => null,
                  "enroll_in_bhi" => null,
                  "completed" => "1",
                  "trouble_sleep" => 2,
                  "feeling_bad_failure" => 0,
                  "slow_fidgety" => 0,
                  "suicidal_thoughts" => 0,
                  "problem_difficulty" => "Not difficult at all",
                  "tired_little_energy" => 1,
                  "little_interest_pleasure" => 0,
                  "feltdown_depressed_hopeless" => 1,
                  "poor_over_appetite" => 0,
                  "trouble_concentrating" => 0
                ],
                "general_health" => [
                  "feeling_caused_distress" => "No",
                  "completed" => "1",
                  "mouth_and_teeth" => "Very Good",
                  "health_level" => "Good"
                ],
                "pain" => [
                  "pain_felt" => "Some"
                ],
                "high_stress" => [
                  "stress_problem" => "Often"
                ],
                "nutrition" => [
                  "fruits_vegs" => "1",
                  "whole_grain_food" => "0",
                  "high_fat_food" => "0",
                  "sugar_beverages" => "0",
                  "completed" => "1"
                ],
                "social_emotional_support" => [
                  "get_social_emotional_support" => "Always"
                ],
                "physical_activities" => [
                  "does_not_apply" => null,
                  "days_of_exercise" => "7",
                  "exercise_intensity" => "light",
                  "mins_of_exercise" => "30",
                  "completed" => "1"
                ],
                "cognitive_assessment" => [
                  "year_recalled" => "correct",
                  "month_recalled" => "correct",
                  "hour_recalled" => "correct",
                  "reverse_count" => "correct",
                  "reverse_month" => "Correct",
                  "address_recalled" => "correct",
                  "completed" => "1"
                ]
              ]
            ],
            "medicareOptions" => "subsequent",
            "q" => "\/api\/questionaire\/update\/2792",
            "physical_exam" => [
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "patient_has_ascvd" => "Yes",
              "ldlvalue_190ormore" => null,
              "pure_hypercholesterolemia" => null,
              "active_diabetes" => null,
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "statin_prescribed" => "Yes",
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "statintype_dosage" => "Rosuvastatin5 to 10 mg",
              "completed" => "1",
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "95",
              "ldl_date" => "08\/2023"
            ],
            "general_health" => [
              "feeling_caused_distress" => "No",
              "mouth_and_teeth" => "Good",
              "completed" => "1",
              "health_level" => "Good"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "Some"
            ],
            "depression_phq9" => [
              "referred_to_mh_professional" => null,
              "enroll_in_bhi" => null,
              "feltdown_depressed_hopeless" => 0,
              "little_interest_pleasure" => 0,
              "poor_over_appetite" => 0,
              "completed" => "1",
              "trouble_sleep" => 1,
              "feeling_bad_failure" => 0,
              "trouble_concentrating" => 0,
              "slow_fidgety" => 0,
              "suicidal_thoughts" => 0,
              "problem_difficulty" => "Not difficult at all",
              "tired_little_energy" => 1
            ],
            "nutrition" => [
              "fruits_vegs" => "1",
              "high_fat_food" => "0",
              "whole_grain_food" => "1",
              "sugar_beverages" => "0",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "0",
              "drinks_per_day" => null,
              "drinks_per_occasion" => null,
              "average_usage" => null,
              "drink_drive_yes" => null,
              "completed" => "1"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => null,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "smoked_in_fifteen_years" => "No",
              "smokeless_product_use" => "No",
              "quit_tobacco" => null,
              "average_smoking_years" => null,
              "average_packs_per_day" => null,
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "No",
              "flu_vaccine_recieved_on" => null,
              "flu_vaccine_recieved_at" => null,
              "flu_vaccine_refused" => "Yes",
              "flu_vaccine_script_given" => null,
              "pneumococcal_vaccine_recieved" => "No",
              "pneumococcal_prevnar_recieved_on" => null,
              "pneumococcal_prevnar_recieved_at" => null,
              "pneumococcal_ppsv23_recieved_on" => null,
              "pneumococcal_ppsv23_recieved_at" => null,
              "pneumococcal_vaccine_refused" => "Yes",
              "pneumococcal_vaccine_script_given" => null,
              "completed" => "1"
            ],
            "screening" => [
              "colonoscopy_done" => "No",
              "colon_test_type" => null,
              "colonoscopy_done_on" => null,
              "colonoscopy_done_at" => null,
              "next_colonoscopy" => null,
              "colonoscopy_report_reviewed" => null,
              "colonoscopy_refused" => "No",
              "refused_colonoscopy" => null,
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "script_given_for" => null,
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Always"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "0",
              "mins_of_exercise" => null,
              "exercise_intensity" => null,
              "completed" => "1"
            ],
            "cognitive_assessment" => [
              "year_recalled" => "correct",
              "month_recalled" => "correct",
              "hour_recalled" => "correct",
              "reverse_month" => "Correct",
              "address_recalled" => "correct",
              "reverse_count" => "correct",
              "completed" => "1"
            ],
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "misc" => [
              "time_spent" => "6",
              "asprin_use" => "check",
              "high_blood_pressure" => "check",
              "patient_on_asprin" => "No",
              "behavioral_counselling" => "check",
              "height" => "63.4",
              "weight" => "199.9",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ]
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "Yes",
              "completed" => "1",
              "provider" => [
                [
                  "key" => 1,
                  "full_name" => "Dr Pozun",
                  "speciality" => "cardiologist"
                ],
                [
                  "key" => 2,
                  "full_name" => "Dr Burhan",
                  "speciality" => "rheumatologist"
                ],
                [
                  "key" => 3,
                  "full_name" => "Dr. Malik",
                  "speciality" => "Hematologist\/oncologist"
                ]
              ]
            ],
            "weight_assessment" => [
              "bmi_value" => "34.96",
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "No",
              "hba1c_value" => null,
              "hba1c_date" => null,
              "diabetec_eye_exam" => null,
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "ratinavue_ordered" => null,
              "urine_microalbumin" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "fbs_in_year" => "Yes",
              "fbs_value" => "91",
              "fbs_date" => "12\/2023",
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_value" => "132\/78",
              "bp_date" => "12\/12\/2023",
              "completed" => "1"
            ]
        ];

      $data17 =
        [
            "tobacco_use" => [
              "average_packs_per_year" => 45,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "smoked_in_fifteen_years" => "Yes",
              "smokeless_product_use" => "No",
              "quit_tobacco" => null,
              "average_smoking_years" => "45",
              "average_packs_per_day" => "1",
              "completed" => "1"
            ],
            "medicareOptions" => "initial",
            "q" => "\/api\/questionaire\/update\/2783",
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "Yes",
              "flu_vaccine_refused" => null,
              "flu_vaccine_script_given" => null,
              "flu_vaccine_recieved_on" => "10\/2023",
              "flu_vaccine_recieved_at" => "Uptown Drug",
              "pneumococcal_vaccine_recieved" => "Yes",
              "pneumococcal_prevnar_recieved_on" => "10\/2023",
              "pneumococcal_prevnar_recieved_at" => "Uptown Drug",
              "pneumococcal_ppsv23_recieved_on" => "10\/2023",
              "pneumococcal_ppsv23_recieved_at" => "Uptown Drug",
              "completed" => "1",
              "pneumococcal_vaccine_refused" => null,
              "pneumococcal_vaccine_script_given" => null
            ],
            "screening" => [
              "colonoscopy_done" => "Yes",
              "colonoscopy_refused" => null,
              "refused_colonoscopy" => null,
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "colonoscopy_script" => null,
              "script_given_for" => null,
              "colon_test_type" => "FIT Test",
              "colonoscopy_done_on" => "11\/2023",
              "colonoscopy_done_at" => "Everlywell",
              "next_colonoscopy" => "11\/2024",
              "colonoscopy_report_reviewed" => "0",
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "No",
              "hba1c_value" => null,
              "hba1c_date" => null,
              "diabetec_eye_exam" => null,
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "ratinavue_ordered" => null,
              "urine_microalbumin" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "fbs_in_year" => "Yes",
              "fbs_value" => "100",
              "fbs_date" => "12\/2023",
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "81",
              "ldl_date" => "12\/2023",
              "patient_has_ascvd" => "No",
              "ldlvalue_190ormore" => "No",
              "pure_hypercholesterolemia" => "No",
              "statin_prescribed" => null,
              "statintype_dosage" => null,
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "active_diabetes" => "No",
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_value" => "132\/80 mmHg",
              "bp_date" => "12\/20\/2023",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "41.37",
              "completed" => "1"
            ],
            "physical_exam" => [
              "completed" => "1"
            ],
            "misc" => [
              "height" => "70 in",
              "patient_on_asprin" => "No",
              "high_blood_pressure" => "check",
              "behavioral_counselling" => "check",
              "asprin_use" => "check",
              "weight" => "288.3",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ],
              "time_spent" => "6"
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "Yes",
              "completed" => "1",
              "provider" => [
                [
                  "key" => 1,
                  "full_name" => "Dr. Payne",
                  "speciality" => "Oncologist"
                ],
                [
                  "key" => 2,
                  "full_name" => "Dr. Lock",
                  "speciality" => "Orthopedic"
                ],
                [
                  "key" => 3,
                  "full_name" => "Dr Cunning",
                  "speciality" => "Otolaryngology"
                ],
                [
                  "key" => 4,
                  "full_name" => "Dr. Elongo",
                  "speciality" => "Cardiologist"
                ],
                [
                  "key" => 5,
                  "full_name" => "Dr. Maghoub",
                  "speciality" => "Pulmonologist"
                ]
              ]
            ],
            "general_health" => [
              "feeling_caused_distress" => "No",
              "completed" => "1",
              "health_level" => "Good",
              "mouth_and_teeth" => "Poor"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "None"
            ],
            "fall_screening" => [
              "fall_in_one_year" => "No",
              "number_of_falls" => null,
              "injury" => null,
              "physical_therapy" => null,
              "blackingout_from_bed" => "No",
              "unsteady_todo_things" => "No",
              "assistance_device" => "None",
              "completed" => "1"
            ],
            "depression_phq9" => [
              "referred_to_mh_professional" => null,
              "enroll_in_bhi" => null,
              "feltdown_depressed_hopeless" => 0,
              "little_interest_pleasure" => 0,
              "poor_over_appetite" => 0,
              "tired_little_energy" => 0,
              "feeling_bad_failure" => 0,
              "slow_fidgety" => 0,
              "suicidal_thoughts" => 0,
              "problem_difficulty" => "Not difficult at all",
              "trouble_sleep" => 0,
              "trouble_concentrating" => 0,
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Always"
            ],
            "cognitive_assessment" => [
              "year_recalled" => "correct",
              "month_recalled" => "correct",
              "hour_recalled" => "correct",
              "reverse_count" => "correct",
              "reverse_month" => "Correct",
              "address_recalled" => "correct",
              "completed" => "1"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "2",
              "exercise_intensity" => "light",
              "mins_of_exercise" => "45",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "0",
              "drinks_per_day" => null,
              "drinks_per_occasion" => null,
              "average_usage" => null,
              "drink_drive_yes" => null,
              "completed" => "1"
            ],
            "nutrition" => [
              "fruits_vegs" => "1",
              "whole_grain_food" => "1",
              "high_fat_food" => "0",
              "sugar_beverages" => "0",
              "completed" => "1"
            ]
        ];

      $data18 =
        [
            "physical_exam" => [
              "completed" => "1"
            ],
            "medicareOptions" => "subsequent",
            "q" => "\/api\/questionaire\/update\/2776",
            "fall_screening" => [
              "fall_in_one_year" => "No",
              "number_of_falls" => null,
              "injury" => null,
              "physical_therapy" => null,
              "blackingout_from_bed" => "No",
              "unsteady_todo_things" => "No",
              "assistance_device" => "None",
              "completed" => "1"
            ],
            "depression_phq9" => [
              "referred_to_mh_professional" => null,
              "enroll_in_bhi" => null,
              "trouble_sleep" => 0,
              "trouble_concentrating" => 0,
              "tired_little_energy" => 0,
              "slow_fidgety" => 0,
              "feeling_bad_failure" => 0,
              "problem_difficulty" => "Not difficult at all",
              "completed" => "1",
              "little_interest_pleasure" => 0,
              "feltdown_depressed_hopeless" => 0,
              "poor_over_appetite" => 0,
              "suicidal_thoughts" => 0
            ],
            "general_health" => [
              "mouth_and_teeth" => "Good",
              "feeling_caused_distress" => "No",
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Always"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "Alot"
            ],
            "nutrition" => [
              "fruits_vegs" => "1",
              "whole_grain_food" => "1",
              "sugar_beverages" => "0",
              "high_fat_food" => "0",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "0",
              "drinks_per_day" => null,
              "drinks_per_occasion" => null,
              "average_usage" => null,
              "drink_drive_yes" => null,
              "completed" => "1"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => null,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "smoked_in_fifteen_years" => "No",
              "smokeless_product_use" => "No",
              "quit_tobacco" => null,
              "average_smoking_years" => null,
              "average_packs_per_day" => null,
              "completed" => "1"
            ],
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "7",
              "exercise_intensity" => "light",
              "mins_of_exercise" => "30",
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "Yes",
              "flu_vaccine_refused" => null,
              "flu_vaccine_script_given" => null,
              "flu_vaccine_recieved_on" => "11\/2023",
              "flu_vaccine_recieved_at" => "NAMG",
              "pneumococcal_vaccine_recieved" => "Yes",
              "pneumococcal_vaccine_refused" => null,
              "pneumococcal_vaccine_script_given" => null,
              "pneumococcal_prevnar_recieved_on" => "01\/2019",
              "pneumococcal_prevnar_recieved_at" => "Walgreens",
              "pneumococcal_ppsv23_recieved_on" => "01\/2012",
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "No",
              "fbs_in_year" => "Yes",
              "fbs_value" => "87",
              "fbs_date" => "09\/2023",
              "hba1c_value" => null,
              "hba1c_date" => null,
              "diabetec_eye_exam" => null,
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "ratinavue_ordered" => null,
              "urine_microalbumin" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "91",
              "ldl_date" => "09\/2023",
              "patient_has_ascvd" => "No",
              "ldlvalue_190ormore" => "No",
              "pure_hypercholesterolemia" => "No",
              "statin_prescribed" => null,
              "statintype_dosage" => null,
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "active_diabetes" => "No",
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_date" => "12\/05\/2023",
              "bp_value" => "126\/80",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "23.66",
              "completed" => "1"
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "Yes",
              "completed" => "1",
              "provider" => [
                [
                  "key" => 1,
                  "full_name" => "Dr. Azam Khan",
                  "speciality" => "gastroenterologist"
                ]
              ]
            ],
            "misc" => [
              "time_spent" => "5",
              "behavioral_counselling" => "check",
              "asprin_use" => "check",
              "high_blood_pressure" => "check",
              "patient_on_asprin" => "No",
              "height" => "68",
              "weight" => "155.6",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ]
            ],
            "screening" => [
              "colonoscopy_done" => "No",
              "colon_test_type" => null,
              "colonoscopy_done_on" => null,
              "colonoscopy_done_at" => null,
              "next_colonoscopy" => null,
              "colonoscopy_report_reviewed" => null,
              "completed" => "1"
            ]
        ];

      $data19 = 
        [
            "fall_screening" => [
              "fall_in_one_year" => "No",
              "number_of_falls" => null,
              "injury" => null,
              "physical_therapy" => null,
              "blackingout_from_bed" => "No",
              "unsteady_todo_things" => "No",
              "assistance_device" => "None",
              "completed" => "1"
            ],
            "medicareOptions" => "subsequent",
            "q" => "\/api\/questionaire\/update\/2767",
            "general_health" => [
              "feeling_caused_distress" => "No",
              "mouth_and_teeth" => "Good",
              "completed" => "1",
              "health_level" => "Good"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "None"
            ],
            "depression_phq9" => [
              "referred_to_mh_professional" => null,
              "enroll_in_bhi" => null,
              "feltdown_depressed_hopeless" => 0,
              "feeling_bad_failure" => 0,
              "trouble_concentrating" => 0,
              "little_interest_pleasure" => 0,
              "trouble_sleep" => 0,
              "slow_fidgety" => 0,
              "suicidal_thoughts" => 0,
              "tired_little_energy" => 0,
              "poor_over_appetite" => 0,
              "problem_difficulty" => "Not difficult at all",
              "completed" => "1"
            ],
            "nutrition" => [
              "fruits_vegs" => "1",
              "whole_grain_food" => "1",
              "high_fat_food" => "0",
              "sugar_beverages" => "1",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "0",
              "drinks_per_day" => null,
              "drinks_per_occasion" => null,
              "average_usage" => null,
              "drink_drive_yes" => null,
              "completed" => "1"
            ],
            "tobacco_use" => [
              "average_packs_per_year" => null,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "No",
              "smoked_in_fifteen_years" => "No",
              "smokeless_product_use" => "No",
              "quit_tobacco" => null,
              "average_smoking_years" => null,
              "average_packs_per_day" => null,
              "completed" => "1"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "7",
              "mins_of_exercise" => "30",
              "exercise_intensity" => "moderate",
              "completed" => "1"
            ],
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "immunization" => [
              "flu_vaccine_recieved" => "No",
              "flu_vaccine_recieved_on" => null,
              "flu_vaccine_recieved_at" => null,
              "flu_vaccine_refused" => "Yes",
              "flu_vaccine_script_given" => null,
              "pneumococcal_vaccine_recieved" => "No",
              "pneumococcal_prevnar_recieved_on" => null,
              "pneumococcal_prevnar_recieved_at" => null,
              "pneumococcal_ppsv23_recieved_on" => null,
              "pneumococcal_ppsv23_recieved_at" => null,
              "pneumococcal_vaccine_refused" => "Yes",
              "pneumococcal_vaccine_script_given" => null,
              "completed" => "1"
            ],
            "screening" => [
              "colonoscopy_done" => "Yes",
              "colonoscopy_refused" => null,
              "refused_colonoscopy" => null,
              "refused_fit_test" => null,
              "refused_cologuard" => null,
              "colonoscopy_script" => null,
              "script_given_for" => null,
              "colon_test_type" => "Colonoscopy",
              "colonoscopy_done_on" => "11\/2023",
              "colonoscopy_done_at" => "KRMC",
              "next_colonoscopy" => "11\/2033",
              "colonoscopy_report_reviewed" => "0",
              "mammogram_done" => "Yes",
              "mammogram_refused" => null,
              "mammogram_script" => null,
              "mammogram_done_on" => "09\/2023",
              "next_mommogram" => "12\/2025",
              "mammogram_done_at" => "KRMC",
              "mommogram_report_reviewed" => "0",
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Always"
            ],
            "cognitive_assessment" => [
              "year_recalled" => "correct",
              "month_recalled" => "correct",
              "hour_recalled" => "correct",
              "reverse_month" => "Correct",
              "reverse_count" => "correct",
              "address_recalled" => "correct",
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "Yes",
              "fbs_in_year" => null,
              "fbs_value" => null,
              "fbs_date" => null,
              "hba1c_value" => "5.7",
              "hba1c_date" => "10\/2023",
              "diabetec_eye_exam" => "No",
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "diabetec_ratinopathy" => null,
              "urine_microalbumin" => "No",
              "urine_microalbumin_value" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => "No",
              "urine_microalbumin_inhibitor" => "ace_inhibitor",
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_date" => "02\/2023",
              "ldl_value" => "49",
              "patient_has_ascvd" => "No",
              "ldlvalue_190ormore" => "No",
              "pure_hypercholesterolemia" => "Yes",
              "active_diabetes" => null,
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "statin_prescribed" => "Yes",
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "statintype_dosage" => "Rosuvastatin5 to 10 mg",
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_date" => "10\/24\/2023",
              "bp_value" => "118\/72",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "24.31",
              "completed" => "1"
            ],
            "physical_exam" => [
              "completed" => "1"
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "Yes",
              "completed" => "1",
              "provider" => [
                [
                  "key" => 1,
                  "full_name" => "Dr. Bokhari",
                  "speciality" => "cardiologist"
                ]
              ]
            ],
            "misc" => [
              "height" => "65 in",
              "weight" => "146.1",
              "time_spent" => "6",
              "behavioral_counselling" => "check",
              "asprin_use" => "check",
              "high_blood_pressure" => "check",
              "patient_on_asprin" => "No",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ]
            ]
        ];
      $data20 = 
        [
            "seatbelt_use" => [
              "wear_seat_belt" => "Yes",
              "completed" => "1"
            ],
            "medicareOptions" => "initial",
            "q" => "\/api\/questionaire\/update\/2761",
            "immunization" => [
              "flu_vaccine_recieved" => "Yes",
              "flu_vaccine_refused" => null,
              "flu_vaccine_script_given" => null,
              "flu_vaccine_recieved_on" => "11\/2023",
              "flu_vaccine_recieved_at" => "NAMG",
              "pneumococcal_vaccine_recieved" => "No",
              "pneumococcal_prevnar_recieved_on" => null,
              "pneumococcal_prevnar_recieved_at" => null,
              "pneumococcal_ppsv23_recieved_on" => null,
              "pneumococcal_ppsv23_recieved_at" => null,
              "completed" => "1"
            ],
            "screening" => [
              "colonoscopy_done" => "No",
              "colon_test_type" => null,
              "colonoscopy_done_on" => null,
              "colonoscopy_done_at" => null,
              "next_colonoscopy" => null,
              "colonoscopy_report_reviewed" => null,
              "completed" => "1"
            ],
            "diabetes" => [
              "ckd_stage_4" => null,
              "diabetec_patient" => "No",
              "hba1c_value" => "6.2",
              "hba1c_date" => "10\/2023",
              "diabetec_eye_exam" => null,
              "diabetec_eye_exam_report" => null,
              "eye_exam_doctor" => null,
              "eye_exam_facility" => null,
              "eye_exam_date" => null,
              "eye_exam_report_reviewed" => null,
              "ratinavue_ordered" => null,
              "urine_microalbumin" => null,
              "urine_microalbumin_date" => null,
              "urine_microalbumin_report" => null,
              "urine_microalbumin_ordered" => null,
              "urine_microalbumin_inhibitor" => null,
              "fbs_in_year" => "Yes",
              "fbs_value" => "104",
              "fbs_date" => "07\/2023",
              "diabetec_ratinopathy" => null,
              "urine_microalbumin_value" => null,
              "completed" => "1"
            ],
            "cholesterol_assessment" => [
              "ldl_in_last_12months" => "Yes",
              "ldl_value" => "79",
              "ldl_date" => "07\/2023",
              "patient_has_ascvd" => "No",
              "ldlvalue_190ormore" => "No",
              "pure_hypercholesterolemia" => "No",
              "statin_prescribed" => null,
              "statintype_dosage" => null,
              "medical_reason_for_nostatin0" => null,
              "medical_reason_for_nostatin1" => null,
              "medical_reason_for_nostatin2" => null,
              "medical_reason_for_nostatin3" => null,
              "medical_reason_for_nostatin4" => null,
              "medical_reason_for_nostatin5" => null,
              "active_diabetes" => "No",
              "diabetes_patient_age" => null,
              "ldl_range_in_past_two_years" => null,
              "completed" => "1"
            ],
            "bp_assessment" => [
              "bp_value" => "138\/90 mmHg",
              "bp_date" => "10\/30\/2023",
              "completed" => "1"
            ],
            "weight_assessment" => [
              "bmi_value" => "22.67",
              "completed" => "1"
            ],
            "physical_exam" => [
              "completed" => "1"
            ],
            "misc" => [
              "height" => "74.5 in",
              "weight" => "179",
              "high_blood_pressure" => "check",
              "asprin_use" => "check",
              "behavioral_counselling" => "check",
              "patient_on_asprin" => "Yes",
              "completed" => "1",
              "Suppliers" => [
                
              ],
              "Pharmacy" => [
                
              ],
              "time_spent" => "10"
            ],
            "other_Provider" => [
              "other_provider_beside_pcp" => "Yes",
              "completed" => "1",
              "provider" => [
                [
                  "key" => 1,
                  "full_name" => "Dr Valpiani",
                  "speciality" => "Anesthesiology"
                ],
                [
                  "key" => 2,
                  "full_name" => "Dr. Kalanithi",
                  "speciality" => "Cardiologist"
                ],
                [
                  "key" => 3,
                  "full_name" => "Dr. Bates",
                  "speciality" => "Echocardiography"
                ],
                [
                  "key" => 4,
                  "full_name" => "Dr. Lock",
                  "speciality" => "Orthopedic Surgeon"
                ]
              ]
            ],
            "fall_screening" => [
              "fall_in_one_year" => "No",
              "number_of_falls" => null,
              "injury" => null,
              "physical_therapy" => null,
              "blackingout_from_bed" => "No",
              "unsteady_todo_things" => "No",
              "assistance_device" => "None",
              "completed" => "1"
            ],
            "depression_phq9" => [
              "referred_to_mh_professional" => null,
              "enroll_in_bhi" => null,
              "feltdown_depressed_hopeless" => 0,
              "little_interest_pleasure" => 0,
              "trouble_sleep" => 2,
              "tired_little_energy" => 0,
              "poor_over_appetite" => 0,
              "feeling_bad_failure" => 0,
              "trouble_concentrating" => 0,
              "slow_fidgety" => 0,
              "suicidal_thoughts" => 0,
              "problem_difficulty" => "Not difficult at all",
              "completed" => "1"
            ],
            "general_health" => [
              "health_level" => "Good",
              "mouth_and_teeth" => "Good",
              "feeling_caused_distress" => "No",
              "completed" => "1"
            ],
            "social_emotional_support" => [
              "get_social_emotional_support" => "Sometimes"
            ],
            "high_stress" => [
              "stress_problem" => "Never or Rarely"
            ],
            "pain" => [
              "pain_felt" => "Some"
            ],
            "cognitive_assessment" => [
              "year_recalled" => "correct",
              "month_recalled" => "correct",
              "hour_recalled" => "correct",
              "reverse_count" => "correct",
              "reverse_month" => "Correct",
              "address_recalled" => "correct",
              "completed" => "1"
            ],
            "physical_activities" => [
              "does_not_apply" => null,
              "days_of_exercise" => "3",
              "mins_of_exercise" => "60",
              "exercise_intensity" => "light",
              "completed" => "1"
            ],
            "alcohol_use" => [
              "days_of_alcoholuse" => "0",
              "drinks_per_day" => null,
              "drinks_per_occasion" => null,
              "drink_drive_yes" => null,
              "completed" => "1",
              "average_usage" => null
            ],
            "tobacco_use" => [
              "average_packs_per_year" => 100,
              "perform_ldct" => null,
              "smoked_in_thirty_days" => "Yes",
              "smoked_in_fifteen_years" => "Yes",
              "smokeless_product_use" => "No",
              "average_smoking_years" => "50",
              "average_packs_per_day" => "2",
              "completed" => "1",
              "quit_tobacco" => "Yes"
            ],
            "nutrition" => [
              "fruits_vegs" => "1",
              "whole_grain_food" => "0",
              "high_fat_food" => "0",
              "sugar_beverages" => "1",
              "completed" => "1"
            ]
        ];


        DB::table('questionaires')->insert(
          [
            [
              'serial_no'         => 'AWV-1001',
              'patient_id'        => 1,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 1,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data1),
              'date_of_service'   => '2024-02-27',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1002',
              'patient_id'        => 2,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 1,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data2),
              'date_of_service'   => '2024-02-28',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1003',
              'patient_id'        => 3,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 1,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data3),
              'date_of_service'   => '2024-02-28',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1004',
              'patient_id'        => 4,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 1,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data4),
              'date_of_service'   => '2024-03-07',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1005',
              'patient_id'        => 5,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 2,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data5),
              'date_of_service'   => '2024-03-08',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1006',
              'patient_id'        => 6,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 2,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data6),
              'date_of_service'   => '2024-03-08',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1007',
              'patient_id'        => 7,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 2,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data7),
              'date_of_service'   => '2024-03-08',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1008',
              'patient_id'        => 8,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 3,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data8),
              'date_of_service'   => '2024-03-08',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1009',
              'patient_id'        => 9,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 3,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data9),
              'date_of_service'   => '2024-03-08',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1010',
              'patient_id'        => 10,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 3,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data10),
              'date_of_service'   => '2024-03-08',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1011',
              'patient_id'        => 11,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 4,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data11),
              'date_of_service'   => '2024-02-27',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1012',
              'patient_id'        => 12,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 4,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data12),
              'date_of_service'   => '2024-02-28',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1013',
              'patient_id'        => 13,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 4,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data13),
              'date_of_service'   => '2024-02-28',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1014',
              'patient_id'        => 14,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 4,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data14),
              'date_of_service'   => '2024-03-07',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1015',
              'patient_id'        => 15,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 4,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data15),
              'date_of_service'   => '2024-03-08',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1016',
              'patient_id'        => 16,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 4,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data16),
              'date_of_service'   => '2024-03-08',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1017',
              'patient_id'        => 17,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 4,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data17),
              'date_of_service'   => '2024-03-08',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1018',
              'patient_id'        => 18,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 4,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data18),
              'date_of_service'   => '2024-03-08',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1019',
              'patient_id'        => 19,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 4,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data19),
              'date_of_service'   => '2024-03-08',
              'created_user'      => 1,
            ],
            [
              'serial_no'         => 'AWV-1020',
              'patient_id'        => 20,
              'program_id'        => 1,
              'doctor_id'         => 2,
              'insurance_id'      => 4,
              'clinic_id'         => 1,
              'questions_answers' => json_encode($data20),
              'date_of_service'   => '2024-03-08',
              'created_user'      => 1,
            ] 

          ]
        );


    }
}
// delete all record with insurance id from patient table and patient status table
// delete all record from caregaps table with respect to their insurances