@extends('./layout/layout')

@section('content')

@php
    $dateofBirth = \Carbon\Carbon::parse($patient['dob'])->format('m/d/Y');
    $dateofService = \Carbon\Carbon::parse($date_of_service)->format('m/d/Y');
@endphp

<div class="container-fluid mt-3">
    <div class="card">
		<div class="card-header">
            <div class="container-fluid">
                <div class="card-body">
                    <h6 class="d-inline">Patient Name:</h6>
                    <p class="d-inline"> {{@$patient['first_name'].' '.@$patient['last_name']}} </p>

                    <h6 class="d-inline ms-5">Date of Birth:</h6>
                    <p class="d-inline"> {{$dateofBirth}} </p>
                    
                    <h6 class="d-inline ms-5">Age:</h6>
                    <p class="d-inline"> {{$patient['age']}} </p>

                    <h6 class="d-inline ms-5">Gender:</h6>
                    <p class="d-inline"> {{$patient['gender']}} </p> <br>
                </div>

                <div class="card-body">
                    <h6 class="d-inline">Program:</h6>
                    <p class="d-inline"> {{$program['name']}} ({{$program['short_name']}}) </p>

                    <h6 class="d-inline ms-5">Date of service:</h6>
                    <p class="d-inline"> {{$dateofService}} </p>
                    
                    <h6 class="d-inline ms-5">Next Due:</h6>
                    <p class="d-inline"> {{\Carbon\Carbon::create($created_at)->addYear(1)->format('m/d/Y')}} </p>
                </div> 
            </div>
        </div>

        <div class="card-header">
            <div class="container-fluid">

                {{-- PHYSICAL HEALTH - FALL SCREENING START --}}
                @if (!empty ($questionaire['fall_screening']))
                <div class="card-body">
                    <h5 class="card-title">Physical Health - Fall Screening</h5>
                    
                    <h6 class="d-inline"> Have you fallen in the past 1 year? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['fall_screening']['fall_in_one_year']) ? $questionaire['fall_screening']['fall_in_one_year'] : '' !!}</p> <br>
                    
                    @if($questionaire['fall_screening']['fall_in_one_year']!= "No")
                    <h6 class="d-inline"> Number of times you fell in last 1 year </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['fall_screening']['number_of_falls']) ? $questionaire['fall_screening']['number_of_falls'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Was their any injury? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['fall_screening']['injury']) ? $questionaire['fall_screening']['injury'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Physical Therapy </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['fall_screening']['physical_therapy']) ? $questionaire['fall_screening']['physical_therapy'] : '' !!}</p> <br>
                    @endif   
                    <h6 class="d-inline"> Do you feel unsteady or do thing move when standing or Walking ? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['fall_screening']['unsteady_todo_things']) ? $questionaire['fall_screening']['unsteady_todo_things'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Do you feel like “blacking out” when getting up from bed or chair? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['fall_screening']['blackingout_from_bed']) ? $questionaire['fall_screening']['blackingout_from_bed'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Do you use any assistance device? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['fall_screening']['assistance_device']) ? $questionaire['fall_screening']['assistance_device'] : '' !!}</p> <br>
                </div>
                @endif
                {{-- PHYSICAL HEALTH - FALL SCREENING END --}}

                @if(!empty($questionaire['depression_phq9']))

                {{-- DEPRESSION PHQ-9 START --}}
                <div class="card-body">
                
                    <h5 class="card-title">Depression PHQ-9</h5>

                    <h6 class="d-inline"> How often have you felt down, depressed, or hopeless? </h6>
                    <p class="d-inline ms-3">{!! !empty($questionaire['depression_phq9']['feltdown_depressed_hopeless']) ? $questionaire['depression_phq9']['feltdown_depressed_hopeless'] : '' !!}</p> <br>

                    <h6 class="d-inline"> How often have you felt little interest or pleasure in doing things? </h6>
                    <p class="d-inline ms-3">{!! !empty($questionaire['depression_phq9']['little_interest_pleasure']) ? $questionaire['depression_phq9']['little_interest_pleasure'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Trouble falling or staying asleep, or sleeping too much? </h6>
                    <p class="d-inline ms-3">{!! !empty($questionaire['depression_phq9']['trouble_sleep']) ? $questionaire['depression_phq9']['trouble_sleep'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Feeling tired or having little energy? </h6>
                    <p class="d-inline ms-3">{!! !empty($questionaire['depression_phq9']['tired_little_energy']) ? $questionaire['depression_phq9']['tired_little_energy'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Poor appetite or overeating </h6>
                    <p class="d-inline ms-3">{!! !empty($questionaire['depression_phq9']['poor_over_appetite']) ? $questionaire['depression_phq9']['poor_over_appetite'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Feeling bad about yourself or that you are a failure or have let yourself or your family down </h6>
                    <p class="d-inline ms-3">{!! !empty($questionaire['depression_phq9']['feeling_bad_failure']) ? $questionaire['depression_phq9']['feeling_bad_failure'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Trouble concentrating on things, such as reading the newspaper or watching television </h6>
                    <p class="d-inline ms-3">{!! !empty($questionaire['depression_phq9']['trouble_concentrating']) ? $questionaire['depression_phq9']['trouble_concentrating'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Moving or speaking so slowly that other people could have noticed? Or the opposite - being so fidgety or restless that you have been moving around a lot more than usual? </h6>
                    <p class="d-inline ms-3">{!! !empty($questionaire['depression_phq9']['slow_fidgety']) ? $questionaire['depression_phq9']['slow_fidgety'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Thoughts that you would be better off dead, or of hurting yourself in some way? </h6>
                    <p class="d-inline ms-3">{!! !empty($questionaire['depression_phq9']['suicidal_thoughts']) ? $questionaire['depression_phq9']['suicidal_thoughts'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> If you checked off any problems, how difficult have these problems made it for you to do your work, take care of things at home, or get along with other people? </h6>
                    <p class="d-inline ms-3">{!! !empty($questionaire['depression_phq9']['problem_difficulty']) ? $questionaire['depression_phq9']['problem_difficulty'] : '' !!}</p> <br>
                   
                    <h6 class="d-inline"> Comments </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['depression_phq9']['comments']}}</p> <br>

                </div>
                {{-- DEPRESSION PHQ-9 END --}}
                @endif

                {{-- HIGH STRESS START --}}
                @if (!empty ($questionaire['high_stress']))
                <div class="card-body">
                    <h5 class="card-title">High Stress</h5>
                    <h6 class="d-inline"> How often is stress a problem for you in handling such things as: Your health, Your finances, Your family or social relationships, Your Work? </h6>
                    <p class="d-inline ms-3"> {{ $questionaire['high_stress']['stress_problem'] }}</p> <br>
                </div>
                @endif
                {{-- HIGH STRESS END --}}
                
                {{-- GENERAL HEALTH START --}}
                @if (!empty ($questionaire['general_health']))
                <div class="card-body">
                    <h5 class="card-title">General Health</h5>
                    <h6 class="d-inline"> How often is stress a problem for you in handling such things as: Your health, Your finances, Your family or social relationships, Your Work? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['general_health']['health_level']) ? $questionaire['general_health']['health_level'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> How would you describe the condition of your mouth and teeth—including false teeth or dentures? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['general_health']['mouth_and_teeth']) ? $questionaire['general_health']['mouth_and_teeth'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> How often is stress a problem for you in handling such things as: Your health, Your finances, Your family or social relationships, Your Work? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['general_health']['feeling_caused_distress']) ? $questionaire['general_health']['feeling_caused_distress'] : '' !!}</p> <br>
                </div>
                @endif
                {{-- GENERAL HEALTH END --}}

                {{-- SOCIAL/EMOTIONAL SUPPORT START --}}
                @if (!empty ($questionaire['social_emotional_support']))
                <div class="card-body">
                    <h5 class="card-title">Social/Emotional Support</h5>
                    <h6 class="d-inline"> How often is stress a problem for you in handling such things as: Your health, Your finances, Your family or social relationships, Your Work? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['social_emotional_support']['get_social_emotional_support']) ? $questionaire['social_emotional_support']['get_social_emotional_support'] : '' !!}</p> <br>
                </div>
                @endif
                {{-- SOCIAL/EMOTIONAL SUPPORT END --}}
                
                {{-- PAIN START --}}
                @if (!empty ($questionaire['social_emotional_support']))
                <div class="card-body">
                    <h5 class="card-title">Pain</h5>
                    <h6 class="d-inline"> In the past 7 days, how much pain have you felt? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['pain']['pain_felt']) ? $questionaire['pain']['pain_felt'] : '' !!}</p> <br>
                </div>
                @endif
                {{-- PAIN END --}}

                {{-- COGNITIVE ASSESSMENT START --}}
                @if (!empty ($questionaire['cognitive_assessment']))
                <div class="card-body">
                    <h5 class="card-title">Cognitive Assessment</h5>
                    
                    <h6 class="d-inline"> Clock Drawn </h6>
                    
                    <h6 class="d-inline"> Number are marked right ?</h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['cognitive_assessment']['clock_number_right']) ? $questionaire['cognitive_assessment']['clock_number_right'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Hands are pointing to 11 & 2 ? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['cognitive_assessment']['clock_hands_right']) ? $questionaire['cognitive_assessment']['clock_hands_right'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Number of Words Recalled </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['cognitive_assessment']['no_of_words_recalled']) ? $questionaire['cognitive_assessment']['no_of_words_recalled'] : '' !!}</p> <br>
                </div>
                @endif
                {{-- COGNITIVE ASSESSMENT END--}}

                {{-- PHYSICAL ACTIVITIES START --}}
                @if (!empty ($questionaire['physical_activities']))
                <div class="card-body">
                    
                    <h5 class="card-title">Physical Activity</h5>   

                    <h6 class="d-inline pl-5"> In the past 7 days, how many days did you exercise? </h6>
                    <p class="d-inline ms-3"> {{ $questionaire['physical_activities']['days_of_exercise'] }} Days</p> <br>
                    
                    <h6 class="d-inline"> On days when you exercised, for how long did you exercise (in minutes)? </h6>
                    <p class="d-inline ms-3"> {{ $questionaire['physical_activities']['mins_of_exercise'] }} Minutes</p> <br>
                    
                    <h6 class="d-inline"> How intense was your typical exercise? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['physical_activities']['exercise_intensity']) ? $questionaire['physical_activities']['exercise_intensity'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Does not apply </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['physical_activities']['does_not_apply']) ? $questionaire['physical_activities']['does_not_apply'] : '' !!}</p> <br>
                </div>
                @endif
                {{-- PHYSICAL ACTIVITIES END --}}

                {{-- ALCOHOL USE START --}}
                @if (!empty ($questionaire['alcohol_use']))
                <div class="card-body">
                    
                    <h5 class="card-title">Alcohol Use</h5>

                    <h6 class="d-inline"> In the past 7 days,on how many days did you drink alcohol? </h6>
                    <p class="d-inline ms-3"> {{ $questionaire['alcohol_use']['days_of_alcoholuse'] }} Days</p> <br>
                @if($questionaire['alcohol_use']['days_of_alcoholuse']!=0)
                    <h6 class="d-inline"> How many drinks per day? </h6>
                    <p class="d-inline ms-3"> {{ $questionaire['alcohol_use']['drinks_per_day'] }} Drinks</p> <br>
                    
                    <h6 class="d-inline"> On days when you drank alcohol, how often did you have alcoholic drinks on one occasion? </h6>
                    <p class="d-inline ms-3"> {{ $questionaire['alcohol_use']['drinks_per_occasion'] }} Drinks per occasion</p> <br>
                    
                    <h6 class="d-inline"> On days when you drank alcohol, how often did you have alcoholic drinks on one occasion? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['alcohol_use']['average_usage']) ? Config('constants.alcohol_average_use')[$questionaire['alcohol_use']['average_usage']] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Do you ever drive after drinking, or ride with a driver who has been drinking? </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['alcohol_use']['drink_drive_yes']) ? $questionaire['alcohol_use']['drink_drive_yes']  : ''  !!} </p> <br>
                @endif
                </div>
                @endif
                {{-- ALCOHOL USE END --}}
                
                @if (!empty($questionaire['tobacco_use']))
                {{-- TOBACCO USE START --}}
                <div class="card-body">
                    
                    <h5 class="card-title">Tobacco Use</h5>

                    <h6 class="d-inline"> In the last 30 days, have you used tobacco? </h6>
                    <p class="d-inline ms-3"> {{ @$questionaire['tobacco_use']['smoked_in_thirty_days'] ?? '' }} </p> <br>
                    
                    <h6 class="d-inline"> Used a smokeless tobacco product? </h6>
                    <p class="d-inline ms-3"> {{$questionaire['tobacco_use']['smokeless_product_use'] ?? ''}} </p> <br>
                    
                    @if(!empty($questionaire['tobacco_use']['smoked_in_fifteen_years']))
                        <h6 class="d-inline"> In the last 15 years, have you used tobacco? </h6>
                        <p class="d-inline ms-3"> {{ @$questionaire['tobacco_use']['smoked_in_fifteen_years'] ?? '' }} </p> <br>
                    @endif

                    <h6 class="d-inline"> Average smoking years? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['tobacco_use']['average_smoking_years'] ?? ''}} </p> <br>
                    
                    <h6 class="d-inline"> Average packs per day? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['tobacco_use']['average_packs_per_day'] ?? ''}} </p> <br>
                    
                    <h6 class="d-inline"> Average packs per year? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['tobacco_use']['average_packs_per_year'] ?? ''}} </p> <br>
                    
                    @if(!empty($questionaire['tobacco_use']['perform_ldct']))
                        <h6 class="d-inline"> Would you be interested to Perform LDCT?</h6>
                        <p class="d-inline ms-3"> {{@$questionaire['tobacco_use']['perform_ldct'] ?? '' }} </p> <br>
                    @endif

                    <h6 class="d-inline"> Would you be interested in quitting tobacco use within the next month? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['tobacco_use']['quit_tobacco'] ?? ''}} </p> <br>

                    <h6 class="d-inline"> Would you be interested in using any alternate? </h6>
                    @if (!empty($questionaire['tobacco_use']['tobacoo_alternate']))
                        <p class="d-inline ms-3"> 
                            {{ $questionaire['tobacco_use']['tobacoo_alternate'] }}
                            @if(!empty($questionaire['tobacco_use']['tobacoo_alternate_qty']))
                            {{$questionaire['tobacco_use']['tobacoo_alternate_qty']}}
                            @endif
                        </p> <br>
                    @endif
                </div>
                {{-- TOBACCO USE END --}}
                @endif

                {{-- NUTRITION USE START --}}
                <div class="card-body">
                
                    <h5 class="card-title">Nutrition</h5>

                    <h6 class="d-inline"> In the past 7 days, how many servings of fruits and vegetables did you typically eat each day? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['nutrition']['fruits_vegs'] ?? 'None'}} </p> <br>
                    
                    <h6 class="d-inline"> In the past 7 days, how many servings of high fiber or whole (not refined) grain foods did you typically eat each day? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['nutrition']['whole_grain_food'] ?? 'None'}}</p> <br>
                    
                    <h6 class="d-inline"> In the past 7 days, how many servings of fried or high-fat foods did you typically eat each day? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['nutrition']['high_fat_food'] ?? 'None'}} </p> <br>
                    
                    <h6 class="d-inline"> In the past 7 days, how many sugar-sweetened (not diet) beverages did you typically consume each day? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['nutrition']['sugar_beverages'] ?? 'None'}}</p> <br>
                </div>
                {{-- NUTRITION USE END --}}

                {{-- SEAT BELT USE START --}}
                <div class="card-body">
                
                    <h5 class="card-title">Seat Belt Use</h5>

                    <h6 class="d-inline"> Do you always fasten your seat belt when you are in a car? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['seatbelt_use']['wear_seal_belt'] ?? ''}} </p> <br>
                    
                </div>
                {{-- SEAT BELT USE END --}}
                
                {{-- IMMUNIZATION START --}}
                <div class="card-body">
                    <h5 class="card-title">Immunization</h5>
                    
                    <h6 class="d-inline"> Refused Flu Vaccine ?</h6>
                    <p class="d-inline ms-3"> {{@$questionaire['immunization']['flu_vaccine_refused'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Received Flu Vaccine ? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['immunization']['flu_vaccine_recieved'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Flu vaccine recieved on </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['immunization']['flu_vaccine_recieved_on'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Flu vaccine recieved at </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['immunization']['flu_vaccine_recieved_at'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Script given for Flu Vaccine </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['immunization']['flu_vaccine_script_given'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Refused Pneumococcal Vaccine ? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['immunization']['pneumococcal_vaccine_refused'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Received Pneumococcal Vaccine ? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['immunization']['pneumococcal_vaccine_recieved'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Recieved Prevnar 13 on </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['immunization']['pneumococcal_prevnar_recieved_on'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Recieved Prevnar 13 at </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['immunization']['pneumococcal_prevnar_recieved_at'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Recieved PPSV 23 on </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['immunization']['pneumococcal_ppsv23_recieved_on'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Recieved PPSV 23 at </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['immunization']['pneumococcal_ppsv23_recieved_at'] ?? ''}}</p> <br>
                   
                    <h6 class="d-inline"> Script given for Prevnar 13 / PPSV 23 </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['immunization']['pneumococcal_vaccine_script_given'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Comments </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['immunization']['comments'] ?? ''}}</p> <br>
                </div>
                {{-- IMMUNIZATION END--}}
                
                {{-- SCREENING START --}}
                <div class="card-body">
                    <h5 class="card-title">Screening</h5>
                    
                    <h6 class="d-inline"> Refused Mammogram ?</h6>
                    <p class="d-inline ms-3"> {{@$questionaire['screening']['mammogram_refused'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Mammogram done on ? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['screening']['mammogram_done_on'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Mammogram done at ? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['screening']['mammogram_done_at'] ?? ''}}</p> <br>

                    <h6 class="d-inline"> Report reviewed </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['screening']['mommogram_report_reviewed']) && $questionaire['screening']['mommogram_report_reviewed'] == 1 ? 'Yes'  : 'No' !!}</p> <br>

                    <h6 class="d-inline"> Next Mammogram due on </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['screening']['next_mommogram'] ?? ''}}</p> <br>

                    <h6 class="d-inline"> Script given for the Screening Mammogram ? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['screening']['mammogram_script'] ?? ''}}</p> <br>

                    <h6 class="d-inline"> Refused Colonoscopy & FIT Test ? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['screening']['colonoscopy_refused'] ?? ''}}</p> <br>

                    <h6 class="d-inline"> Colonoscopy / FIT Test / Cologuard done on </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['screening']['colonoscopy_done_on'] ?? ''}}</p> <br>

                    <h6 class="d-inline"> Colonoscopy / FIT Test / Cologuard done at </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['screening']['colonoscopy_done_at'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Report reviewed </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['screening']['colonoscopy_report_reviewed']) && $questionaire['screening']['colonoscopy_report_reviewed'] == 1 ? 'Yes' : 'No' !!}</p> <br>

                    <h6 class="d-inline"> Next Colonoscopy / FIT Test due on </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['screening']['next_colonoscopy'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Script given for the Screening Colonoscopy </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['screening']['colonoscopy_script'] ?? ''}}</p> <br>

                    <h6 class="d-inline"> Comments </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['screening']['comments'] ?? ''}}</p> <br>

                </div>
                {{-- SCREENING END--}}


                {{-- DIABESTES STARTS --}}
                @if (!empty($questionaire['diabetes']))
                <div class="card-body">
                    <h5 class="card-title">Diabetes</h5>
                    
                    <h6 class="d-inline"> Does Patient have active diagnosis of diabetes ?</h6>
                    <p class="d-inline ms-3"> {{@$questionaire['diabetes']['diabetec_patient'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> FBS done in last 12 months ?</h6>
                    <p class="d-inline ms-3"> {{@$questionaire['diabetes']['fbs_in_year'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Fasting Blood Sugar (FBS)</h6>
                    <p class="d-inline ms-3"> {{@$questionaire['diabetes']['fbs_value'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Fasting Blood Sugar date (FBS)</h6>
                    <p class="d-inline ms-3"> {{@$questionaire['diabetes']['fbs_date'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> HBA1C</h6>
                    <p class="d-inline ms-3"> {{@$questionaire['diabetes']['hba1c_value'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> HBA1C Date</h6>
                    <p class="d-inline ms-3"> {{@$questionaire['diabetes']['hba1c_date'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Diabetic Eye Examination in last 12 months ? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['diabetes']['diabetec_eye_exam'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Ratinavue Ordered </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['diabetes']['ratinavue_ordered'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Script given for Eye Examination</h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['diabetes']['ratinavue_ordered']) ? "Yes" : "" !!}</p> <br>
                    
                    <h6 class="d-inline"> Eye Exmaination Report</h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['diabetes']['diabetec_eye_exam_report']) ? ucfirst(str_replace('_', ' ', $questionaire['diabetes']['diabetec_eye_exam_report'])) : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Name of Doctor</h6>
                    <p class="d-inline ms-3">{{@$questionaire['diabetes']['eye_exam_doctor'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Facility</h6>
                    <p class="d-inline ms-3">{{@$questionaire['diabetes']['eye_exam_facility'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Eye Exam date</h6>
                    <p class="d-inline ms-3">{{@$questionaire['diabetes']['eye_exam_date'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Report Reviewed</h6>
                    <p class="d-inline ms-3">{!! !empty($questionaire['diabetes']['eye_exam_report_reviewed']) ? 'Yes' : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Report Shows Diabetic Retinopathy</h6>
                    <p class="d-inline ms-3">{{$questionaire['diabetes']['diabetec_ratinopathy'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Urine for microalbumin in last 6 months</h6>
                    <p class="d-inline ms-3">{{@$questionaire['diabetes']['urine_microalbumin'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Urine for Microalbumin date</h6>
                    <p class="d-inline ms-3">{{@$questionaire['diabetes']['urine_microalbumin_date'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Urine for Microalbumin report</h6>
                    <p class="d-inline ms-3">{{@$questionaire['diabetes']['urine_microalbumin_report'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Urine for Micro-albumin ordered</h6>
                    <p class="d-inline ms-3">{{@$questionaire['diabetes']['urine_microalbumin_ordered'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Does patient use</h6>
                    <p class="d-inline ms-3">{!! !empty($questionaire['diabetes']['urine_microalbumin_inhibitor']) ? ucfirst($questionaire['diabetes']['urine_microalbumin_inhibitor']) : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Does patient has</h6>
                    <p class="d-inline ms-3">{!! !empty($questionaire['diabetes']['ckd_stage_4']) ? ucfirst(str_replace('_', ' ', $questionaire['diabetes']['ckd_stage_4'])) : '' !!}</p> <br>

                </div>
                @endif
                {{-- DIABESTES ENDS --}}


                {{-- CHOLESTEROL STARTS --}}
                @if (!empty($questionaire['cholesterol_assessment']))
                <div class="card-body">
                    <h5 class="card-title">Cholesterol</h5>
                    
                    <h6 class="d-inline"> LDL Done in last 12 months ?</h6>
                    <p class="d-inline ms-3"> {{@$questionaire['cholesterol_assessment']['ldl_in_last_12months'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> LDL is </h6>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['cholesterol_assessment']['ldl_value']) ? $questionaire['cholesterol_assessment']['ldl_value'].' on' : '' !!}</p>
                    <p class="d-inline ms-3"> {!! !empty($questionaire['cholesterol_assessment']['ldl_date']) ? $questionaire['cholesterol_assessment']['ldl_date'] : '' !!}</p> <br>
                    
                    <h6 class="d-inline"> Does Patient have ASCVD?</h6>
                    <p class="d-inline ms-3"> {{@$questionaire['cholesterol_assessment']['patient_has_ascvd'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Fasting or direct LDL-C ≥ 190 mg/dL? Check from result above ?</h6>
                    <p class="d-inline ms-3"> {{@$questionaire['cholesterol_assessment']['ldlvalue_190ormore'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> History or active diagnosis of familial or pure hypercholesterolemia</h6>
                    <p class="d-inline ms-3"> {{@$questionaire['cholesterol_assessment']['pure_hypercholesterolemia'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Does Patient have active diagnosis of diabetes? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['cholesterol_assessment']['active_diabetes'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Patient age between 40-75 years? </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['cholesterol_assessment']['diabetes_patient_age'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Fasting or Direct LDL-C 70-189 mg/dL any time in past two years (2020-2022)?</h6>
                    <p class="d-inline ms-3"> {{@$questionaire['cholesterol_assessment']['ldl_range_in_past_two_years'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Was the patient prescribed any high or moderate intensity statin in the current calendar year?</h6>
                    <p class="d-inline ms-3">{{@$questionaire['cholesterol_assessment']['statin_prescribed'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Statin Type and dosage</h6>
                    <p class="d-inline ms-3">{{@$questionaire['cholesterol_assessment']['statintype_dosage'] ?? ''}}</p> <br>
                    
                    <h6 class="d-inline"> Documented medical reason for not being on statin therapy is:</h6>
                    @foreach(Config('constants.statin_medical_reason') as $key => $val)
                        <p class="d-inline ms-3">{!! !empty($questionaire['cholesterol_assessment']['medical_reason_for_nostatin'.$key]) ? $questionaire['cholesterol_assessment']['medical_reason_for_nostatin'.$key] : '' !!}</p>
                    @endforeach

                </div>
                @endif
                {{-- CHOLESTEROL ENDS --}}

               
                {{-- BP ASSESSMENT STARTS --}}
                @if (!empty($questionaire['bp_assessment']))
                <div class="card-body">
                
                    <h5 class="card-title">BP Assessment</h5>

                    <h6 class="d-inline"> BP </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['bp_assessment']['bp_value'] ?? ''}} </p> <br>
                    
                </div>
                @endif
                {{-- BP ASSESSMENT ENDS --}}
                
                {{-- WEIGHT ASSESSMENT STARTS --}}
                @if (!empty($questionaire['weight_assessment']))
                <div class="card-body">
                    <h5 class="card-title">Weight Assessment</h5>

                    <h6 class="d-inline"> BMI </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['weight_assessment']['bmi_value'] ?? ''}} </p> <br>
                    
                </div>
                @endif
                {{-- WEIGHT ASSESSMENT ENDS --}}
                
                {{-- Miscellaneous STARTS --}}
                @if (!empty($questionaire['miscellaneous']))
                <div class="card-body">
                    <h5 class="card-title">Miscellaneous</h5>

                    <p> <b> Vitals </b> </p>
                    <h6 class="d-inline"> Height </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['miscellaneous']['height'] ?? ''}} </p> <br>
                    
                    <h6 class="d-inline"> Weight </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['miscellaneous']['weight'].' lbs' ?? ''}} </p>
                </div>

                <div class="card-body">
                    <h6>Advance Care Plan</h6>
                    <p>Advanced Care planning was discussed with the patient. A packet is given to the patient. The patient shows understanding. The patient was by himself during the discussion</p>
                    <h6 class="d-inline"> Time Spent </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['miscellaneous']['time_spent'].' minutes' ?? ''}} </p>
                </div>
                
                <div class="card-body">
                    <h6>Intensive behavioral therapy for cardiovascular disease (CVD)</h6>

                    <h6 class="d-inline"> Encouraged aspirin use for primary prevention a cardiovascular disease when the benefits outweigh the risks for men age 45-79 and women 55-79. </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['miscellaneous']['asprin_use'] == 'check' ? 'Yes' : ''}} </p> <br>
                    
                    <h6 class="d-inline"> Screened for high blood pressure. </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['miscellaneous']['high_blood_pressure'] == 'check' ? 'Yes' : ''}} </p> <br>
                    
                    <h6 class="d-inline"> Intensive behavioral counseling provided to promote a healthy diet for adults who already have hyperlipidemia, hypertension, advancing age, and other known risk factors for cardiovascular and diet related chronic diseases. </h6>
                    <p class="d-inline ms-3"> {{@$questionaire['miscellaneous']['behavioral_counselling'] == 'check' ? 'Yes' : ''}} </p>

                </div>
                @endif
                {{-- Miscellaneous ENDS --}}
            </div>
        </div>
	</div>
</div>

@endsection