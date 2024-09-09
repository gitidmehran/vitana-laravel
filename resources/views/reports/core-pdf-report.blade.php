
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	  	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">  	
		<title>{{$page_title ?? 'Home'}}</title>
		<style type="text/css">
			.header{
				font-size: 25px;
				text-align: center;
				margin-bottom: 15px;
			}
			tr,th,td{
				border: 1px solid black;
			}
			tr > td {
				padding: 5px;				
				justify-content: left;
			}
			
			.answer{
				color: gray;
			}
		</style>

		@php
			$dateofBirth = \Carbon\Carbon::parse($patient['dob'])->format('m/d/Y');
			$dateofService = \Carbon\Carbon::parse($date_of_service)->format('m/d/Y');
		@endphp
		
		<body>
        	<div class="row">

				<div class="col-12">
        			<div>
        				<p class="header">Patient Survey Report</p>

            			<h6 class="d-inline">Patient Name:</h6>
		                <p class="d-inline"> {{@$patient['first_name'].' '.@$patient['last_name']}} </p>

						<h6 class="d-inline ms-2">Date of Birth:</h6>
						<p class="d-inline"> {{$dateofBirth}} </p>

		                <h6 class="d-inline ms-2">Age:</h6>
		                <p class="d-inline"> {{$patient['age']}} </p>

						<h6 class="d-inline ms-2">Gender:</h6>
		                <p class="d-inline"> {{$patient['gender']}} </p><br>
        			</div>
        			<div style="position: absolute;margin-left: 0px !important;">
		                <h6 class="d-inline">Program:</h6>
		                <p class="d-inline"> {{$program['name']}} ({{$program['short_name']}}) </p>
		            </div>
		            <div style="position: absolute;margin-left: -10px !important; margin-top: 25px !important;">
						<h6 class="d-inline ms-2">Date of service:</h6>
						<p class="d-inline"> {{$dateofService}} </p>
						
						<h6 class="d-inline ms-2">Next Due:</h6>
						<p class="d-inline"> {{\Carbon\Carbon::create($created_at)->addYear(1)->format('m/d/Y')}} </p>
        			</div>	
        		</div>
        		
        		<div class="col-12" style="margin-top:70px;">
        			<table>
        				<tbody>

							@if(!empty ($questionaire['fall_screening']))
							<tr>
        						<th>Physical Health - Fall Screening</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
        									<p class="d-inline"> Have you fallen in the past 1 year? </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['fall_screening']['fall_in_one_year']) ? $questionaire['fall_screening']['fall_in_one_year'] : '' !!}</p>
										</li>
									@if($questionaire['fall_screening']['fall_in_one_year']!= "No")
        								<li>
        									<p class="d-inline"> Number of times you fell in last 1 year </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['fall_screening']['number_of_falls']) ? $questionaire['fall_screening']['number_of_falls'] : '' !!}</p>
										</li>

        								<li>
        									<p class="d-inline"> Was their any injury? </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['fall_screening']['injury']) ? $questionaire['fall_screening']['injury'] : '' !!}</p>
										</li>
										
        								<li>
        									<p class="d-inline"> Physical Therapy </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['fall_screening']['physical_therapy']) ? $questionaire['fall_screening']['physical_therapy'] : '' !!}</p>
										</li>
									@endif
        								<li>
        									<p class="d-inline"> Do you feel unsteady or do thing move when standing or Walking ? </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['fall_screening']['unsteady_todo_things']) ? $questionaire['fall_screening']['unsteady_todo_things'] : '' !!}</p>
										</li>

        								<li>
        									<p class="d-inline"> Do you feel like “blacking out” when getting up from bed or chair? </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['fall_screening']['blackingout_from_bed']) ? $questionaire['fall_screening']['blackingout_from_bed'] : '' !!}</p>
										</li>
										
        								<li>
        									<p class="d-inline"> Do you use any assistance device? </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['fall_screening']['assistance_device']) ? $questionaire['fall_screening']['assistance_device'] : '' !!}</p>
										</li>
        							</ul>
        						</td>
        					</tr>
							@endif
        					
							@if (!empty($questionaire['depression_phq9']))
							<tr>
        						<th>Depression PHQ-9</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
        									<p class="d-inline">How often have you felt down, depressed, or hopeless? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['depression_phq9']['feltdown_depressed_hopeless']) ? $questionaire['depression_phq9']['feltdown_depressed_hopeless'] : '' !!}</p>
										</li>
										<li>
        									<p class="d-inline">How often have you felt little interest or pleasure in doing things? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['depression_phq9']['little_interest_pleasure']) ? $questionaire['depression_phq9']['little_interest_pleasure'] : '' !!}</p>
										</li>
										
										<li>
        									<p class="d-inline">Trouble falling or staying asleep, or sleeping too much? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['depression_phq9']['trouble_sleep']) ? $questionaire['depression_phq9']['trouble_sleep'] : '' !!}</p>
										</li>
										
										<li>
        									<p class="d-inline">Feeling tired or having little energy? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['depression_phq9']['tired_little_energy']) ? $questionaire['depression_phq9']['tired_little_energy'] : '' !!}</p>
										</li>
										
										<li>
        									<p class="d-inline">Poor appetite or overeating </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['depression_phq9']['poor_over_appetite']) ? $questionaire['depression_phq9']['poor_over_appetite'] : '' !!}</p>
										</li>
										
										<li>
        									<p class="d-inline">Feeling bad about yourself or that you are a failure or have let yourself or your family down </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['depression_phq9']['feeling_bad_failure']) ? $questionaire['depression_phq9']['feeling_bad_failure'] : '' !!}</p>
										</li>
										
										<li>
        									<p class="d-inline">Trouble concentrating on things, such as reading the newspaper or watching television </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['depression_phq9']['trouble_concentrating']) ? $questionaire['depression_phq9']['trouble_concentrating'] : '' !!}</p>
										</li>
										
										<li>
        									<p class="d-inline">Moving or speaking so slowly that other people could have noticed? Or the opposite - being so fidgety or restless that you have been moving around a lot more than usual? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['depression_phq9']['slow_fidgety']) ? $questionaire['depression_phq9']['slow_fidgety'] : '' !!}</p>
										</li>
										
										<li>
        									<p class="d-inline">Thoughts that you would be better off dead, or of hurting yourself in some way? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['depression_phq9']['suicidal_thoughts']) ? $questionaire['depression_phq9']['suicidal_thoughts'] : '' !!}</p>
										</li>
										
										<li>
        									<p class="d-inline">If you checked off any problems, how difficult have these problems made it for you to do your work, take care of things at home, or get along with other people? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['depression_phq9']['problem_difficulty']) ? $questionaire['depression_phq9']['problem_difficulty'] : '' !!}</p>
        								</li>

										<li>
											<p class="d-inline"> Comments </p>
						                    <p class="d-inline text-success"> {{@$questionaire['depression_phq9']['comments']}}</p> <br>
        								</li>
        							</ul>
        						</td>
        					</tr>
							@endif
							
							@if (!empty($questionaire['high_stress']))
							<tr>
        						<th>High Stress</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
        									<p class="d-inline">How often is stress a problem for you in handling such things as: Your health, Your finances, Your family or social relationships, Your Work? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['high_stress']['stress_problem']) ? $questionaire['high_stress']['stress_problem'] : '' !!}</p>
										</li>
        							</ul>
        						</td>
        					</tr>
							@endif
							
							@if (!empty($questionaire['general_health']))
							<tr>
        						<th>General Health</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
        									<p class="d-inline">In general, would you say your health is? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['general_health']['health_level']) ? $questionaire['general_health']['health_level'] : '' !!}</p>
										</li>
        								<li>
        									<p class="d-inline">How would you describe the condition of your mouth and teeth—including false teeth or dentures? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['general_health']['mouth_and_teeth']) ? $questionaire['general_health']['mouth_and_teeth'] : '' !!}</p>
										</li>
        								<li>
        									<p class="d-inline">Have your feelings caused you distress or interfered with your ability to get along socially with family or friends? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['general_health']['feeling_caused_distress']) ? $questionaire['general_health']['feeling_caused_distress'] : '' !!}</p>
										</li>
        							</ul>
        						</td>
        					</tr>
							@endif
							
							@if (!empty($questionaire['social_emotional_support']))
							<tr>
        						<th>Social/Emotional Support</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
        									<p class="d-inline">How often do you get the social and emotional support you need? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['social_emotional_support']['get_social_emotional_support']) ? $questionaire['social_emotional_support']['get_social_emotional_support'] : '' !!}</p>
										</li>
        							</ul>
        						</td>
        					</tr>
							@endif
							
							@if (!empty($questionaire['pain']))
							<tr>
        						<th>Pain</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
        									<p class="d-inline">In the past 7 days, how much pain have you felt? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['pain']['pain_felt']) ? $questionaire['pain']['pain_felt'] : '' !!}</p>
										</li>
        							</ul>
        						</td>
        					</tr>
							@endif

							{{-- COGNITIVE ASSESSMENT START --}}
							@if (!empty($questionaire['cognitive_assessment']))	
							<tr>
        						<th>Cognitive Assessment</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
        									<p class="d-inline"> Number are marked right ? </p>
											<p class="d-inline text-success"> {!! !empty($questionaire['cognitive_assessment']['clock_number_right']) ? $questionaire['cognitive_assessment']['clock_number_right'] : '' !!}</p>
										</li>

        								<li>
        									<p class="d-inline"> Hands are pointing to 11 & 2 ? </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['cognitive_assessment']['clock_hands_right']) ? $questionaire['cognitive_assessment']['clock_hands_right'] : '' !!}</p>
										</li>

        								<li>
        									<p class="d-inline"> Number of Words Recalled </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['cognitive_assessment']['no_of_words_recalled']) ? $questionaire['cognitive_assessment']['no_of_words_recalled'] : '' !!}</p>
										</li>
        							</ul>
        						</td>
        					</tr>
							@endif
							{{-- COGNITIVE ASSESSMENT END--}}

        					<tr>
        						<th>Physical Activities</th>
        						<td>
        							
        							<ul>
        								<li>
        									<p class="d-inline">In the past 7 days, how many days did you exercise?</p>
        									<p class="d-inline text-success">{{ $questionaire['physical_activities']['days_of_exercise'] }} Days</p>
        								</li>
        								<li>
        									<p class="d-inline">On days when you exercised, for how long did you exercise (in minutes)? </p>
        									<p class="d-inline text-success">{{ $questionaire['physical_activities']['mins_of_exercise'] }} Minutes</p>
        								</li>
        								<li>
        									<p class="d-inline">How intense was your typical exercise?</p>
        									<p class="d-inline text-success">{!! !empty($questionaire['physical_activities']['exercise_intensity']) ? $questionaire['physical_activities']['exercise_intensity'] : '-' !!}</p>
        								</li>
        								<li>
        									<p class="d-inline">Does not apply</p>
        									<p class="d-inline text-success">
        										{!! !empty($questionaire['physical_activities']['does_not_apply']) ? $questionaire['physical_activities']['does_not_apply'] : '-' !!}
        									</p>
        								</li>
        							</ul>
        						</td>
        					</tr>

        					<tr>
        						<th>Alcohol Use</th>
        						<td>
        							
        							<ul>
        								<li>
        									<p class="d-inline">In the past 7 days,on how many days did you drink alcohol?</p>
        									<p class="d-inline text-success">{{ $questionaire['alcohol_use']['days_of_alcoholuse'] }} Days</p>
        								</li>
        							@if($questionaire['alcohol_use']['days_of_alcoholuse']!=0)
        								<li>
        									<p class="d-inline">How many drinks per day? </p>
        									<p class="d-inline text-success">{{ $questionaire['alcohol_use']['drinks_per_day'] }} Drinks</p>
        								</li>
        								<li>
        									<p class="d-inline">On days when you drank alcohol, how often did you have alcoholic drinks on one occasion?</p>
        									<p class="d-inline text-success">{{ $questionaire['alcohol_use']['drinks_per_occasion'] }} Drinks per occasion</p>
        								</li>
        								<li>
        									<p class="d-inline">On days when you drank alcohol, how often did you have alcoholic drinks on one occasion? </p>
        									<p class="d-inline text-success">
        										{!! !empty($questionaire['alcohol_use']['average_usage']) ? Config('constants.alcohol_average_use')[$questionaire['alcohol_use']['average_usage']] : '-' !!}
        									</p>
        								</li>
        								<li>
        									<p class="d-inline"> Do you ever drive after drinking, or ride with a driver who has been drinking? </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['alcohol_use']['drink_drive_yes']) ? $questionaire['alcohol_use']['drink_drive_yes'] : "" !!} </p>
        								</li>
        							@endif
        							</ul>
        						</td>
        					</tr>

        					<tr>
        						<th>Nutrition</th>
        						<td>
        							
        							<ul>
        								<li>
        									<p class="d-inline">In the past 7 days, how many servings of fruits and vegetables did you typically eat each day? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['nutrition']['fruits_vegs']) ? $questionaire['nutrition']['fruits_vegs']. ' Serving per day' : 'None' !!}</p>
        								</li>
        								<li>
        									<p class="d-inline">In the past 7 days, how many servings of high fiber or whole (not refined) grain foods did you typically eat each day? </p>
        									<p class="d-inline text-success">
        										{!! !empty($questionaire['nutrition']['whole_grain_food']) ? $questionaire['nutrition']['whole_grain_food']. ' Serving per day' : 'None' !!}
        									</p>
        								</li>
        								<li>
        									<p class="d-inline">In the past 7 days, how many servings of fried or high-fat foods did you typically eat each day? </p>
        									<p class="d-inline text-success">
        										{!! !empty($questionaire['nutrition']['high_fat_food']) ? $questionaire['nutrition']['high_fat_food']. ' Serving per day' : 'None' !!}
        									</p>
        								</li>
        								<li>
        									<p class="d-inline">
        										In the past 7 days, how many sugar-sweetened (not diet) beverages did you typically consume each day? 
        									</p>
        									<p class="d-inline text-success">
        										{!! !empty($questionaire['nutrition']['sugar_beverages']) ? $questionaire['nutrition']['sugar_beverages']. ' Serving per day' : 'None' !!}
        									</p>
        								</li>
        							</ul>
        						</td>
        					</tr>

        					<tr>
        						<th>Seat Belt Use</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
        									<p class="d-inline">Do you always fasten your seat belt when you are in a car? </p>
        									<p class="d-inline text-success">{!! !empty($questionaire['seatbelt_use']['wear_seal_belt']) ? $questionaire['seatbelt_use']['wear_seal_belt'] : '' !!}</p>
        								</li>
        							</ul>
        						</td>
        					</tr>
							
							{{-- IMMUNIZATION START --}}
							<tr>
        						<th>Immunization</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
											<p class="d-inline"> Refused Flu Vaccine ?</p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['immunization']['flu_vaccine_refused']) ? $questionaire['immunization']['flu_vaccine_refused'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Received Flu Vaccine ? </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['immunization']['flu_vaccine_recieved']) ? $questionaire['immunization']['flu_vaccine_recieved'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Flu vaccine recieved on </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['immunization']['flu_vaccine_recieved_on']) ? $questionaire['immunization']['flu_vaccine_recieved_on'] : '' !!}</p> <br>
        								</li>
										
										<li>
											<p class="d-inline"> Flu vaccine recieved at </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['immunization']['flu_vaccine_recieved_at']) ? $questionaire['immunization']['flu_vaccine_recieved_at'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Script given for Flu Vaccine </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['immunization']['flu_vaccine_script_given']) ? $questionaire['immunization']['flu_vaccine_script_given'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Refused Pneumococcal Vaccine ? </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['immunization']['pneumococcal_vaccine_refused']) ? $questionaire['immunization']['pneumococcal_vaccine_refused'] : '' !!}</p> <br>
        								</li>
										
										<li>
											<p class="d-inline"> Received Pneumococcal Vaccine ? </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['immunization']['pneumococcal_vaccine_recieved']) ? $questionaire['immunization']['pneumococcal_vaccine_recieved'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Recieved Prevnar 13 on </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['immunization']['pneumococcal_prevnar_recieved_on']) ? $questionaire['immunization']['pneumococcal_prevnar_recieved_on'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Recieved Prevnar 13 at </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['immunization']['pneumococcal_prevnar_recieved_at']) ? $questionaire['immunization']['pneumococcal_prevnar_recieved_at'] : '' !!}</p> <br>
        								</li>
										
										<li>
											<p class="d-inline"> Recieved PPSV 23 on </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['immunization']['pneumococcal_ppsv23_recieved_on']) ? $questionaire['immunization']['pneumococcal_ppsv23_recieved_on'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Recieved PPSV 23 at </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['immunization']['pneumococcal_ppsv23_recieved_at']) ? $questionaire['immunization']['pneumococcal_ppsv23_recieved_at'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Script given for Prevnar 13 / PPSV 23 </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['immunization']['pneumococcal_vaccine_script_given']) ? $questionaire['immunization']['pneumococcal_vaccine_script_given'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Comments </p>
						                    <p class="d-inline text-success"> {{@$questionaire['immunization']['comments']}}</p> <br>
        								</li>
										
        							</ul>
        						</td>
        					</tr>
							{{-- IMMUNIZATION END--}}
							
							{{-- SCREENING START --}}
							<tr>
        						<th>Screening</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
											<p class="d-inline"> Refused Mammogram ?</p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['screening']['mammogram_refused']) ? $questionaire['screening']['mammogram_refused'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Mammogram done on ? </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['screening']['mammogram_done_on']) ? $questionaire['screening']['mammogram_done_on'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Mammogram done at ? </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['screening']['mammogram_done_at']) ? $questionaire['screening']['mammogram_done_at'] : '' !!}</p> <br>
        								</li>
										
										<li>
											<p class="d-inline"> Report reviewed </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['screening']['mommogram_report_reviewed']) && $questionaire['screening']['mommogram_report_reviewed'] == 1 ? "Yes" : "No" !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Next Mammogram due on </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['screening']['next_mommogram']) ? $questionaire['screening']['next_mommogram'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Script given for the Screening Mammogram ? </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['screening']['mammogram_script']) ? $questionaire['screening']['mammogram_script'] : '' !!}</p> <br>
        								</li>
										
										<li>
											<p class="d-inline"> Refused Colonoscopy & FIT Test ? </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['screening']['colonoscopy_refused']) ? $questionaire['screening']['colonoscopy_refused'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Colonoscopy / FIT Test / Cologuard done on </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['screening']['colonoscopy_done_on']) ? $questionaire['screening']['colonoscopy_done_on'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Colonoscopy / FIT Test / Cologuard done at </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['screening']['colonoscopy_done_at']) ? $questionaire['screening']['colonoscopy_done_at'] : '' !!}</p> <br>
        								</li>
										
										<li>
											<p class="d-inline"> Report reviewed </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['screening']['colonoscopy_report_reviewed']) && $questionaire['screening']['colonoscopy_report_reviewed'] == 1 ? "Yes" : "No" !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Next Colonoscopy / FIT Test due on </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['screening']['next_colonoscopy']) ? $questionaire['screening']['next_colonoscopy'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Script given for the Screening Colonoscopy </p>
						                    <p class="d-inline text-success"> {!! !empty($questionaire['screening']['colonoscopy_script']) ? $questionaire['screening']['colonoscopy_script'] : '' !!}</p> <br>
        								</li>

										<li>
											<p class="d-inline"> Comments </p>
						                    <p class="d-inline text-success"> {{@$questionaire['screening']['comments']}}</p> <br>
        								</li>
										
        							</ul>
        						</td>
        					</tr>
							{{-- SCREENING END--}}


							{{-- DIABETES START --}}
							<tr>
        						<th>Diabetes</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
											<p class="d-inline"> Does patient have active diagnosis of diabetes ?</p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['diabetes']['diabetec_patient']) ? $questionaire['diabetes']['diabetec_patient'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> FBS done in last 12 months ?</p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['diabetes']['fbs_in_year']) ? $questionaire['diabetes']['fbs_in_year'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Fasting Blood Sugar (FBS)</p>
											<p class="d-inline text-success"> {!! !empty($questionaire['diabetes']['fbs_value']) ? $questionaire['diabetes']['fbs_value'] : '' !!}</p> <br>
        								</li>
										
										<li>
											<p class="d-inline"> Fasting Blood Sugar date (FBS)</p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['diabetes']['fbs_date']) ? $questionaire['diabetes']['fbs_date'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> HBA1C</p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['diabetes']['hba1c_value']) ? $questionaire['diabetes']['hba1c_value'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> HBA1C Date</p>
                   							<p class="d-inline text-success"> {!! !empty($questionaire['diabetes']['hba1c_date']) ? $questionaire['diabetes']['hba1c_date'] : '' !!}</p> <br>
        								</li>
										
										<li>
											<p class="d-inline"> Diabetic Eye Examination in last 12 months ? </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['diabetes']['diabetec_eye_exam']) ? $questionaire['diabetes']['diabetec_eye_exam'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Ratinavue Ordered </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['diabetes']['ratinavue_ordered']) ? $questionaire['diabetes']['ratinavue_ordered'] : '' !!}</p> <br>

        								</li>

        								<li>
											<p class="d-inline"> Script given for Eye Examination</p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['diabetes']['ratinavue_ordered']) ? "Yes" : "" !!}</p> <br>

        								</li>
										
										<li>
											<p class="d-inline"> Eye Exmaination Report</p>
											<p class="d-inline text-success"> {!! !empty($questionaire['diabetes']['diabetec_eye_exam_report']) ? ucfirst(str_replace('_', ' ', $questionaire['diabetes']['diabetec_eye_exam_report'])) : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Name of Doctor</p>
											<p class="d-inline text-success">{!! !empty($questionaire['diabetes']['eye_exam_doctor']) ? $questionaire['diabetes']['eye_exam_doctor'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Facility</p>
											<p class="d-inline text-success">{!! !empty($questionaire['diabetes']['eye_exam_facility']) ? $questionaire['diabetes']['eye_exam_facility'] : '' !!}</p> <br>
        								</li>
										
        								<li>
											<p class="d-inline"> Eye Exam date</p>
                    						<p class="d-inline text-success">{!! !empty($questionaire['diabetes']['eye_exam_date']) ? $questionaire['diabetes']['eye_exam_date'] : '' !!}</p> <br>
        								</li>
										
        								<li>
											<p class="d-inline"> Report Reviewed</p>
                    						<p class="d-inline text-success">{!! !empty($questionaire['diabetes']['eye_exam_report_reviewed']) ? 'Yes' : '' !!}</p> <br>
        								</li>
										
        								<li>
											<p class="d-inline"> Report Shows Diabetic Retinopathy</p>
                    						<p class="d-inline text-success">{!! !empty($questionaire['diabetes']['diabetec_ratinopathy']) ? $questionaire['diabetes']['diabetec_ratinopathy'] : '' !!}</p> <br>
        								</li>
										
        								<li>
											<p class="d-inline"> Urine for microalbumin in last 6 months</p>
                    						<p class="d-inline text-success">{!! !empty($questionaire['diabetes']['urine_microalbumin']) ? $questionaire['diabetes']['urine_microalbumin'] : '' !!}</p> <br>
        								</li>
										
        								<li>
											<p class="d-inline"> Urine for Microalbumin date</p>
                    						<p class="d-inline text-success">{!! !empty($questionaire['diabetes']['urine_microalbumin_date']) ? $questionaire['diabetes']['urine_microalbumin_date'] : '' !!}</p> <br>
        								</li>
										
        								<li>
											<p class="d-inline"> Urine for Microalbumin report</p>
                    						<p class="d-inline text-success">{!! !empty($questionaire['diabetes']['urine_microalbumin_report']) ? $questionaire['diabetes']['urine_microalbumin_report'] : '' !!}</p> <br>
        								</li>
										
        								<li>
											<p class="d-inline"> Urine for Micro-albumin ordered</p>
                    						<p class="d-inline text-success">{!! !empty($questionaire['diabetes']['urine_microalbumin_ordered']) ? $questionaire['diabetes']['urine_microalbumin_ordered'] : '' !!}</p> <br>
        								</li>
										
        								<li>
											<p class="d-inline"> Does patient use</p>
						                    <p class="d-inline text-success">{!! !empty($questionaire['diabetes']['urine_microalbumin_inhibitor']) ? ucfirst($questionaire['diabetes']['urine_microalbumin_inhibitor']) : '' !!}</p> <br>
        								</li>

										<li>
											<p class="d-inline"> Does patient has</p>
                    						<p class="d-inline text-success">{!! !empty($questionaire['diabetes']['ckd_stage_4']) ? ucfirst(str_replace('_', ' ', $questionaire['diabetes']['ckd_stage_4'])) : '' !!}</p> <br>
										</li>
										
        							</ul>
        						</td>
        					</tr>
							{{-- DIABETES END--}}
							
							
							{{-- CHOLESTEROL START --}}
							<tr>
        						<th>Cholesterol</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
											<p class="d-inline"> LDL Done in last 12 months ?</p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['cholesterol_assessment']['ldl_in_last_12months']) ? $questionaire['cholesterol_assessment']['ldl_in_last_12months'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> LDL is </p>
											<p class="d-inline text-success"> {!! !empty($questionaire['cholesterol_assessment']['ldl_value']) ? $questionaire['cholesterol_assessment']['ldl_value'].' on' : '' !!}</p>
											<p class="d-inline text-success"> {!! !empty($questionaire['cholesterol_assessment']['ldl_date']) ? $questionaire['cholesterol_assessment']['ldl_date'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Does Patient have ASCVD?</p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['cholesterol_assessment']['patient_has_ascvd']) ? $questionaire['cholesterol_assessment']['patient_has_ascvd'] : '' !!}</p> <br>
        								</li>
										
										<li>
											<p class="d-inline"> Fasting or direct LDL-C ≥ 190 mg/dL? Check from result above ?</p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['cholesterol_assessment']['ldlvalue_190ormore']) ? $questionaire['cholesterol_assessment']['ldlvalue_190ormore'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> History or active diagnosis of familial or pure hypercholesterolemia</p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['cholesterol_assessment']['pure_hypercholesterolemia']) ? $questionaire['cholesterol_assessment']['pure_hypercholesterolemia'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Does Patient have active diagnosis of diabetes? </p>
											<p class="d-inline text-success"> {!! !empty($questionaire['cholesterol_assessment']['active_diabetes']) ? $questionaire['cholesterol_assessment']['active_diabetes'] : '' !!}</p> <br>
        								</li>
										
										<li>
											<p class="d-inline"> Patient age between 40-75 years? </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['cholesterol_assessment']['diabetes_patient_age']) ? $questionaire['cholesterol_assessment']['diabetes_patient_age'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Fasting or Direct LDL-C 70-189 mg/dL any time in past two years (2020-2022)?</p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['cholesterol_assessment']['ldl_range_in_past_two_years']) ? $questionaire['cholesterol_assessment']['ldl_range_in_past_two_years'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Was the patient prescribed any high or moderate intensity statin in the current calendar year?</p>
                    						<p class="d-inline text-success">{!! !empty($questionaire['cholesterol_assessment']['statin_prescribed']) ? $questionaire['cholesterol_assessment']['statin_prescribed'] : '' !!}</p> <br>

        								</li>
										
										<li>
											<p class="d-inline"> Statin Type and dosage</p>
                    						<p class="d-inline text-success">{!! !empty($questionaire['cholesterol_assessment']['statintype_dosage']) ? $questionaire['cholesterol_assessment']['statintype_dosage'] : '' !!}</p> <br>
        								</li>

        								<li>
											<p class="d-inline"> Documented medical reason for not being on statin therapy is:</p>
											@foreach(Config('constants.statin_medical_reason') as $key => $val)
												<p class="d-inline text-success">{!! !empty($questionaire['cholesterol_assessment']['medical_reason_for_nostatin'.$key]) ? $questionaire['cholesterol_assessment']['medical_reason_for_nostatin'.$key] : '' !!}</p>
											@endforeach
        								</li>

        								
        							</ul>
        						</td>
        					</tr>
							{{-- CHOLESTEROL END--}}

							{{-- BP ASSESSMENT STARTS --}}
							<tr>
        						<th>BP Assessment</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
											<p class="d-inline"> BP </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['bp_assessment']['bp_value']) ? $questionaire['bp_assessment']['bp_value'] : '' !!}</p> <br>
        								</li>
									</ul>
								</td>
							</tr>
							{{-- BP ASSESSMENT ENDS --}}
							
							{{-- WEIGHT ASSESSMENT STARTS --}}
							<tr>
        						<th>Weight Assessment</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
											<p class="d-inline"> BMI </p>
                    						<p class="d-inline text-success"> {!! !empty($questionaire['weight_assessment']['bmi_value']) ? $questionaire['weight_assessment']['bmi_value'] : '' !!}</p> <br>
        								</li>
									</ul>
								</td>
							</tr>
							{{-- WEIGHT ASSESSMENT ENDS --}}
							
							{{-- Miscellaneous STARTS --}}
							<tr>
        						<th>Miscellaneous</th>
        						<td style="margin-top:5px">
        							<ul>
        								<li>
											<p class="d-inline"> Height </p>
                    						<p class="d-inline text-success"> {{@$questionaire['miscellaneous']['height'] ?? ''}}</p> <br>
        								</li>

										<li>
											<p class="d-inline"> Weight </p>
                    						<p class="d-inline text-success"> {{@$questionaire['miscellaneous']['weight'].' lbs' ?? ''}}</p> <br>
										</li>
										
										<li>
											<p class="d-inline"> Advanced Care planning was discussed with the patient. A packet is given to the patient. The patient shows understanding. The patient was by himself during the discussion </p> <br>
											<p class="d-inline"> Time spent </p>
                    						<p class="d-inline text-success"> {{@$questionaire['miscellaneous']['time_spent'].' minutes' ?? ''}}</p> <br>
										</li>
										
										<li>
											<p class="d-inline"> Encouraged aspirin use for primary prevention a cardiovascular disease when the benefits outweigh the risks for men age 45-79 and women 55-79. </p>
                    						<p class="d-inline text-success"> {{@$questionaire['miscellaneous']['asprin_use'] == 'check' ? 'Yes' : ''}}</p> <br>
										</li>
										
										<li>
											<p class="d-inline"> Screened for high blood pressure. </p>
                    						<p class="d-inline text-success"> {{@$questionaire['miscellaneous']['high_blood_pressure'] == 'check' ? 'Yes' : ''}}</p> <br>
										</li>
										
										<li>
											<p class="d-inline"> Intensive behavioral counseling provided to promote a healthy diet for adults who already have hyperlipidemia, hypertension, advancing age, and other known risk factors for cardiovascular and diet related chronic diseases. </p>
                    						<p class="d-inline text-success"> {{@$questionaire['miscellaneous']['behavioral_counselling'] == 'check' ? 'Yes' : ''}}</p> <br>
										</li>
									</ul>
								</td>
							</tr>
							{{-- Miscellaneous ENDS --}}

        				</tbody>
        			</table>
        		</div>
        	</div>
			<script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    		<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
		</body>
	</head>
</html>