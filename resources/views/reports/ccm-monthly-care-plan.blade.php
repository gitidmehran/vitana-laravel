<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<!-- Bootstrap 5 -->
	<link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('css/fontawesome.css') }}">
	<link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('css/bootstrap3.min.css') }}">


	<title>Patient Analytics Report</title>
	<style type="text/css">
		.header {
			font-size: 26px;
			text-align: center;
			margin-bottom: 15px;
		}

		tr,
		th,
		td {
			border: 1px solid #D3D3D3;
		}

		tr>td {
			padding: 5px;
			justify-content: left;
		}

		@font-face {
			font-family: 'fontawesome3';
			src: url({{"asset('fonts/fontawesome-webfont.ttf')"}}) format('truetype');
			font-weight: normal;
			font-style: normal;
		}

		.fa {
			display: inline-block;
			font: normal normal normal 14px/1 fontawesome3;
			text-rendering: auto;

			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
		}

		.coverh1 {
			text-align: center;
		}

		.coverh4 {
			text-align: center;
		}

		.center {
			width: 350px;
			height: 250px;
			margin: auto;
			position: relative;
			top: 30%;

		}

		.radial-repeating {
          width:100%;
          height:260mm;
		  border: 4px dotted #23468c;
		  border-style: double
		}
	</style>

	@php
		$next_assessment = \Carbon\Carbon::create(@$row['created_at'])->addYear(1)->format('m/Y');
		$dateofBirth = \Carbon\Carbon::parse(@$row['patient']['dob'])->format('m/d/Y');
		$dateOfService = \Carbon\Carbon::parse(@$row['date_of_service'])->format('m/d/Y');
		if (!empty($row['monthly_assessment'])) {
			$dateOfService = \Carbon\Carbon::parse(@$row['monthly_assessment']['date_of_service'])->format('m/d/Y');
		}
		$currentYear = \Carbon\Carbon::now()->year;
	@endphp

</head>

<body>


	
	<div size="A4" class="radial-repeating" style="margin-bottom:40px">
		<div class="center">
				<p>Monthly CCM 2023</p>
				<p>
					<b>{{@$row['patient']['name']}}</b> This is your Monthly CCM Care Plan
				</p>
				</p>
					<b> Visit Date: {{$dateOfService}}</b>
				</p>
		</div>
	</div> 
	<div style="padding: 20px; border: 1px solid #b8daff; ">

		<table style="border: none;">
			<tr style="border: none;">
				<th width="10%" style="font-size: 12px; border: none;text-align: left; ">Name: <span style="font-size: 11px; font-weight: normal;">{{@$row['patient']['name']}}</span></th>
				<th width="16%" style="font-size: 12px; border: none; padding-left:10px text-align: left;">Date of Birth: <span style="font-size: 11px; font-weight: normal;">{{$dateofBirth}}</span></th>
				<th width="8%" style="font-size: 12px; border: none; padding-left:10px text-align: left;">Age: <span style="font-size: 11px; font-weight: normal;">{{$row['patient']['age']}}</span></th>
				<th width="10%" style="font-size: 12px; border: none; padding-left:10px text-align: left;">Gender: <span style="font-size: 11px; font-weight: normal;">{{$row['patient']['gender']}}</span></th>
			</tr>
		<tr style="border: none;">
				<th width="25%" style="font-size: 12px; border: none; text-align: left;">Program: <span style="font-size: 11px; font-weight: normal;">{{$row['program']['name']}} ({{$row['program']['short_name']}})</span></th>
				<th width="10%" style="font-size: 12px; border: none; padding-left:10px text-align: left;">Primary care Physician: <span style="font-size: 11px; font-weight: normal;">{{@$row['primary_care_physician'] ?? ''}}</span></th>
			</tr>
			<tr style="border: none;">
				<th width="10%" style="font-size: 12px; border: none; ">Date of Service: <span style="font-size: 11px; font-weight: normal;">{{$dateOfService}}</span></th>
			</tr>
		</table>
	</div>

	<div  style="margin-top:50px;">

		<!-- Depression start -->
		@if ($chronic_disease['Depression'] == "true")
			<table style="width:100%;" class="table table-border">
				<tbody>
					<tr>
						<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
							Depression
						</th>
					</tr>
					<tr style="border-right:1px solid black">
						<th  width="10%">
							Prognosis
						</th>
						<td  width="90%" style="border-right:1px solid black">
							{{@$depression_out_comes['prognosis'] ?? ""}}
						</td>
					</tr>
					<tr style="border-right:1px solid black">
						<th  width="10%">
							Assessment
						</th>
						<td  width="90%" style="border-right:1px solid black">
							{{@$depression_out_comes['assessment'] ?? ""}}
						</td>
					</tr>
				</tbody>
			</table>

			<table style="width:100%;" class="table table-border">
				<tbody>
					<tr>
						<th colspan="9" class=" table-primary" style="width:65%; text-align:center;">
							Goals
						</th>
						<th colspan="1" class="table-primary">
							Start Date
						</th>
						<th colspan="1" class=" table-primary">
							End Date
						</th>
						<th colspan="1" class="table-primary">
							Status
						</th>
					</tr>

					{{-- Goal 1 --}}
					@if(@$depression_out_comes['goal1_status'] != "")
						{{-- GOAL --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									To acquire knowledge about depression and how it can affect you.
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$depression_out_comes['goal1_status'] ?? ""}}</td>
						</tr>

						{{-- Tasks --}}
						@if(isset($depression_out_comes['understand_about_disease_status']) && $depression_out_comes['understand_about_disease_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Assess the patient’s current knowledge and understanding regarding disease </b>
									<p>
										Depression (major depressive disorder) is a common and serious medical illness that negatively affects how you feel, the way you think and how you act. Fortunately, it is also treatable. Depression causes feelings of sadness and/or a loss of interest in activities you once enjoyed. It can lead to a variety of emotional and physical problems and can decrease your ability to function at work and at home.
									</p>
								</td>
								<td align="center">{{@$depression_out_comes['understand_about_disease_start_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['understand_about_disease_end_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['understand_about_disease_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($depression_out_comes['monitor_phq9_status']) && $depression_out_comes['monitor_phq9_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Monitor PHQ-9 levels of patients </b>
									<p>
										The PHQ-9 can function as a screening tool, an aid in diagnosis, and as a symptom tracking tool that can help track a patient's overall depression severity as well as track the improvement of specific symptoms with treatment. 
									</p>
								</td>
								<td align="center">{{@$depression_out_comes['monitor_phq9_start_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['monitor_phq9_end_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['monitor_phq9_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($depression_out_comes['advantages_of_phq9_status']) && $depression_out_comes['advantages_of_phq9_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>ADVANTAGES OF THE PHQ-9</b>
									<p>
										Shorter than other depression rating scales. Can be administered in person by a clinician, by telephone, or self-administered by the patient. Facilitates diagnosis of major depression. Provides assessment of symptom severity. Is well validated and documented in a variety of populations. Can be used in adolescents as young as 12 years of age.
									</p>
								</td>
								<td align="center">{{@$depression_out_comes['advantages_of_phq9_start_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['advantages_of_phq9_end_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['advantages_of_phq9_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif
					
					{{-- GOAL 2 --}}
					@if(isset($depression_out_comes['goal2_status']) && $depression_out_comes['goal2_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									To understand the effect of depression on overall health.
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$depression_out_comes['goal2_status'] ?? ""}}</td>
						</tr>

						{{-- Tasks --}}
						@if(isset($depression_out_comes['effect_with_other_problems_status']) && $depression_out_comes['effect_with_other_problems_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>Understanding depression relationship with other medical problems</b>
								<p>
									Depression can co-occur with other medical problems, such as diabetes, heart disease, cancer, and many others. The relationship between depression and medical comorbidities is complicated. Depression can worsen in the face of medical problems and, at the same time, cause the medical conditions themselves to worsen. In part that's because depression makes it hard for people to take care of their medical conditions.
								</p>
								</td>
								<td align="center">{{@$depression_out_comes['effect_with_other_problems_start_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['effect_with_other_problems_end_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['effect_with_other_problems_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif 
						
					{{-- GOAL 3 --}}
					@if(isset($depression_out_comes['goal3_status']) && $depression_out_comes['goal3_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									To understand the importance of different approaches that are used to treat depression.
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$depression_out_comes['goal3_status'] ?? ""}}</td>
						</tr>

						@if(isset($depression_out_comes['relieve_depression_status']) && $depression_out_comes['relieve_depression_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Understanding Counseling (with a psychiatrist, psychologist, nurse, or social worker) & medicines that relieve depression </b>
									<p>
										People with depression that is not too severe can get better by taking medicines or talking with a counselor. People with severe depression usually need medicines to get better, and might also need to see a counselor.
									</p>
								</td>
								<td align="center">{{@$depression_out_comes['relieve_depression_start_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['relieve_depression_end_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['relieve_depression_status'] ?? ""}}</td>
							</tr>
						@endif

						@if(isset($depression_out_comes['understand_cbt_status']) && $depression_out_comes['understand_cbt_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Understanding CBT</b>
									<p>
										CBT (Cognitive Behavioral therapy) teaches you to become aware of and adjust negative patterns, which can help you reframe your thinking during moments of heightened anxiety or panic.It can also provide new coping skills, like meditation or journaling, for those struggling with a substance use disorder or depression.
									</p>
								</td>
								<td align="center">{{@$depression_out_comes['understand_cbt_start_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['understand_cbt_end_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['understand_cbt_status'] ?? ""}}</td>
							</tr>
						@endif

						@if(isset($depression_out_comes['physical_activity_importance_status']) && $depression_out_comes['physical_activity_importance_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Importance of Physical activity</b>
									<p>
										In addition to treatment, getting regular physical activity can also help you feel better.
									</p>
								</td>
								<td align="center">{{@$depression_out_comes['physical_activity_importance_start_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['physical_activity_importance_end_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['physical_activity_importance_status'] ?? ""}}</td>
							</tr>
						@endif

						@if(isset($depression_out_comes['waves_treatment_problems_status']) && $depression_out_comes['waves_treatment_problems_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Understanding treatments that pass magnetic waves or electricity into the brain</b>
									<p>
										Another treatment involves placing a device against the scalp to pass magnetic waves into the brain. This is called "transcranial magnetic stimulation" ("TMS"). Doctors might suggest TMS if medicines and counseling have not helped. 
										Some people with severe depression might need a treatment called "electroconvulsive therapy" ("ECT"). During ECT, doctors pass an electric current through a person's brain in a safe way.
									</p>
								</td>
								<td align="center">{{@$depression_out_comes['waves_treatment_start_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['waves_treatment_end_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['waves_treatment_problems_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif

					{{-- GOAL 4 --}}
					@if(isset($depression_out_comes['goal4_status']) && $depression_out_comes['goal4_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									To understand the importance of changes to your habits and lifestyle to treat depression.
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$depression_out_comes['goal4_status'] ?? ""}}</td>
						</tr>

						{{-- TASK --}}
						@if(isset($depression_out_comes['exercise_status']) && $depression_out_comes['exercise_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Exercise a specific number of days per week</b>
									<p>
										To set a goal to walk around your neighborhood for 30 minutes a day every weekday or go to the gym three times a week.
									</p>
								</td>
								<td align="center">{{@$depression_out_comes['exercise_start_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['exercise_end_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['exercise_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif

					{{-- GOAL 5 --}}
					@if(isset($depression_out_comes['goal5_status']) && $depression_out_comes['goal5_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									To understand the importance of regular follow-ups.
								</b>
								<p>
									It is very important to regularly follow up with your PCP or Psychiatrist to be evaluated for your depression. It is also very important to understand if for any reason you feel your depression is worsening or medications have stopped working you should let your PCP know and walk in to have your treatment adjusted
								</p>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$depression_out_comes['goal5_status'] ?? ""}}</td>
						</tr>

						{{-- TASK --}}
						@if(isset($depression_out_comes['regular_follow_ups_status']) && $depression_out_comes['regular_follow_ups_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b></b>
								</td>
								<td align="center">{{@$depression_out_comes['regular_follow_ups_start_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['regular_follow_ups_end_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['regular_follow_ups_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif

					{{-- GOAL 6 --}}
					@if(isset($depression_out_comes['goal6_status']) && $depression_out_comes['goal6_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									To understand what to do if you are having thoughts of harming yourself.
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$depression_out_comes['goal6_status'] ?? ""}}</td>
						</tr>

						{{-- TASK --}}
						@if(isset($depression_out_comes['helping_guides_status']) && $depression_out_comes['helping_guides_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>If you ever feel like you might hurt yourself or someone else, help is available:</b>
								<p>
									<ul>
										<li>In the US, contact the 988 Suicide & Crisis Lifeline:</li>
										<li>To speak to someone, call or text 988.</li>
										<li>Call your doctor or nurse, and tell them it is urgent.</li>
										<li>Call for an ambulance (in the US and Canada, call 9-1-1).</li>
										<li>Go to the emergency department at the nearest hospital.</li>
									</ul>
								</p>

								</td>
								<td align="center">{{@$depression_out_comes['helping_guides_start_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['helping_guides_end_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['helping_guides_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif

					{{-- GOAL 7 --}}
					@if(isset($depression_out_comes['goal7_status']) && $depression_out_comes['goal7_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									To utilize counseling/group support
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$depression_out_comes['goal7_status'] ?? ""}}</td>
						</tr>

						{{-- TASK --}}
						@if(isset($depression_out_comes['improve_relations_status']) && $depression_out_comes['improve_relations_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>To improve your relationships with other people can help to lower your risk of being affected by depression.</b>
								</td>
								<td align="center">{{@$depression_out_comes['improve_relations_start_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['improve_relations_end_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['improve_relations_status'] ?? ""}}</td>
							</tr>
						@endif

						{{-- TASK --}}
						@if(isset($depression_out_comes['psychotherapy_status']) && $depression_out_comes['psychotherapy_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>To take part in therapy on a regular basis not only lets you receive the mental health benefits of psychotherapy, but it can also help create a routine in your life.</b>
									<p>
										Simple but effective short-term goal for treating depression could be to make a note of at least one positive event that happens every day for a certain period of time, such as a whole month.
									</p>
								</td>
								<td align="center">{{@$depression_out_comes['psychotherapy_start_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['psychotherapy_end_date'] ?? ""}}</td>
								<td align="center">{{@$depression_out_comes['psychotherapy_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif
				</tbody>
			</table>
		@endif
		<!-- Hypercholesterolemia end -->


		<!-- Hypercholesterolemia start -->
		@if ($chronic_disease['Hypercholesterolemia'] == "true")
			<table style="width:100%;" class="table table-border">
				<tbody>
					<tr>
						<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
							Hypercholesterolemia
						</th>
					</tr>
					<tr style="border-right:1px solid black">
						<th  width="10%">
							Prognosis
						</th>
						<td  width="90%" style="border-right:1px solid black">
							{{@$hypercholestrolemia_outcomes['prognosis'] ?? ""}}
						</td>
					</tr>
					<tr style="border-right:1px solid black">
						<th  width="10%">
							Assessment
						</th>
						<td  width="90%" style="border-right:1px solid black">
							{{@$hypercholestrolemia_outcomes['assessment'] ?? ""}}
						</td>
					</tr>
				</tbody>
			</table>

			<table style="width:100%;" class="table table-border">
				<tbody>
					<tr>
						<th colspan="9" class=" table-primary" style="width:65%; text-align:center;">
							Goals
						</th>
						<th colspan="1" class="table-primary">
							Start Date
						</th>
						<th colspan="1" class=" table-primary">
							End Date
						</th>
						<th colspan="1" class="table-primary">
							Status
						</th>
					</tr>

					{{-- Goal 1 --}}
					@if(@$hypercholestrolemia_outcomes['goal1_status'] != "")
						{{-- GOAL --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									To develope an understanding regarding risk factors and monitoring for Hyperlipidemia.
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$hypercholestrolemia_outcomes['goal1_status'] ?? ""}}</td>
						</tr>

						{{-- Tasks --}}
						@if(isset($hypercholestrolemia_outcomes['causes_of_hyperlipidemia_status']) && $hypercholestrolemia_outcomes['causes_of_hyperlipidemia_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Patient will learn various causes of hyperlipidemia. </b>
									<p>
										Smoking, drinking excessive alcohol & eating foods that have a lot of saturated fats or trans fats. Most animal fats are saturated. The fats of plants and fish are generally healthy. Many processed foods like foods deep-fried  and sausage are high in saturated fat content and not healthy. Sitting too much instead of being active. Being stressed. Inheriting genes that make your cholesterol level unhealthy. Being overweight.
									</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['causes_of_hyperlipidemia_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['causes_of_hyperlipidemia_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['causes_of_hyperlipidemia_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($hypercholestrolemia_outcomes['saturated_trans_fat_status']) && $hypercholestrolemia_outcomes['saturated_trans_fat_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Patient will learn to avoid saturated & trans-fat. </b>
									<p>
										Unhealthy fats – "Trans" fats are especially unhealthy. They are found in margarines, many fast foods, and some store-bought baked goods. "Saturated" fats are found in animal products like meats, egg yolks, butter, cheese, and full-fat milk products. Unhealthy fats can raise your cholesterol level and increase your chance of getting heart disease.
									</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['saturated_trans_fat_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['saturated_trans_fat_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['saturated_trans_fat_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($hypercholestrolemia_outcomes['lab_mandatory_status']) && $hypercholestrolemia_outcomes['lab_mandatory_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Patient will learn importance of checking yearly Lipids & LDL goal.</b>
									<p>
										It should be mandatory to do labs like levels of triglycerides, cholesterol levels. Patient should know the levels of LDL. It should be less than 130 if patient is not Diabetic or have heart disease. If patient has heart disease, then it should be less than 100 and if Diabetes then the level be less than 70.
									</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['lab_mandatory_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['lab_mandatory_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['lab_mandatory_status'] ?? ""}}</td>
							</tr>
						@endif
					
						@if(isset($hypercholestrolemia_outcomes['monitor_comorbid_status']) && $hypercholestrolemia_outcomes['monitor_comorbid_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>Patient will learn other conditions that can co-exist and managing Lipid can help them</b>
								<p>
									Conditions like obesity, diabetes, hypertension, and heart disease. It can also coexist with arthritis, sleep apnea, and atrial fibrillation. In many cases, managing high cholesterol will help manage other conditions. Your doctor can provide personalized guidance depending on your comorbidities.
								</p>
							</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['monitor_comorbid_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['monitor_comorbid_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['monitor_comorbid_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif
					
					{{-- GOAL 2 --}}
					@if(isset($hypercholestrolemia_outcomes['goal2_status']) && $hypercholestrolemia_outcomes['goal2_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									To understand the effect of Lipids on Cardiovascular System
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$hypercholestrolemia_outcomes['goal2_status'] ?? ""}}</td>
						</tr>

						{{-- Tasks --}}
						@if(isset($hypercholestrolemia_outcomes['understand_etiology_status']) && $hypercholestrolemia_outcomes['understand_etiology_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>Understanding how high LDL leads to heart attack</b>
								<p>
									Total Cholesterol has LDL (Bad Cholesterol) and HDL (Good Cholesterol). If you have an excess amount of LDL (LIPIDS) in your bloodstream, waxy plaques can build up along your artery walls, causing arteries to narrow. Over time, arteries may become damaged with these plaques and susceptible to blood clots. These blood clots can dislodge and block a small vessel in the heart causing heart attack.
								</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['understand_etiology_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['understand_etiology_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['understand_etiology_status'] ?? ""}}</td>
							</tr>
						@endif
						
						
						@if(isset($hypercholestrolemia_outcomes['calculate_ASCVD_status']) && $hypercholestrolemia_outcomes['calculate_ASCVD_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Cholesterol is a factor in ASCVD score.</b>
									<p>
										The ASCVD (atherosclerotic cardiovascular disease) risk score is a national guideline developed by the American College of Cardiology. It is a calculation of your 10-year risk of having a cardiovascular problem, such as a heart attack or stroke. This risk estimate considers age, sex, race, cholesterol levels, blood pressure, medication use, diabetic status, and smoking status.
									</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['calculate_ASCVD_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['calculate_ASCVD_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['calculate_ASCVD_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif 
						
					{{-- GOAL 3 --}}
					@if(isset($hypercholestrolemia_outcomes['goal3_status']) && $hypercholestrolemia_outcomes['goal3_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									To understand the importance of healthy diet in controlling Lipids
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$hypercholestrolemia_outcomes['goal3_status'] ?? ""}}</td>
						</tr>

						@if(isset($hypercholestrolemia_outcomes['dietary_factors_status']) && $hypercholestrolemia_outcomes['dietary_factors_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Teaching about healthy diet </b>
									<p>
										The most beneficial changes result from reducing intake of saturated and trans fats; mostly found in commercially friend food like French fries and increasing intake of polyunsaturated and monounsaturated fats. Low-carbohydrate, or low-fat diet also has beneficial effects in reducing intake of dietary cholesterol. Increasing intake of soluble fiber and soy protein, and eating fatty marine fish or taking marine-derived omega-3 fatty acid supplements is also beneficial.
									</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['dietary_factors_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['dietary_factors_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['dietary_factors_status'] ?? ""}}</td>
							</tr>
						@endif

						@if(isset($hypercholestrolemia_outcomes['visiting_nutritionist_status']) && $hypercholestrolemia_outcomes['visiting_nutritionist_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Visiting to nutritionist for proper diet plan</b>
									<p>
										Your nutritionist will guide you toward healthy food choices while helping you enjoy the foods you are eating. Nutritionists can also teach you about healthy food habits and behaviors that encourage weight loss. Healthy habits may include eating at the dining table, weighing your food or avoiding late night snacks. The goal of this education is to help you develop healthy habits for the rest of your life.
									</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['visiting_nutritionist_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['visiting_nutritionist_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['visiting_nutritionist_status'] ?? ""}}</td>
							</tr>
						@endif					
					
					@endif

					{{-- GOAL 4 --}}
					@if(isset($hypercholestrolemia_outcomes['goal4_status']) && $hypercholestrolemia_outcomes['goal4_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									To understand the effect of Exercise on Lipids
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$hypercholestrolemia_outcomes['goal4_status'] ?? ""}}</td>
						</tr>

						{{-- TASK --}}
						@if(isset($hypercholestrolemia_outcomes['amount_of_exercise_status']) && $hypercholestrolemia_outcomes['amount_of_exercise_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>How much exercise is better?</b>
									<p>
										Any amount of exercise is better than sedentary lifestyle. It is important to incorporate exercise as a life habit. Individual should engage in moderate-intensity physical exercise performed for a minimum of 10 minutes four times a week or vigorous-intensity exercise (jogging) performed for a minimum of 20 minutes twice a week. Moderate-intensity exercise is defined as activity sufficient to break a sweat or noticeably raise the heart rate (eg, walking briskly, using an exercise bicycle).
									</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['amount_of_exercise_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['amount_of_exercise_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['amount_of_exercise_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($hypercholestrolemia_outcomes['effect_of_exercise_status']) && $hypercholestrolemia_outcomes['effect_of_exercise_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>What is the effect of exercise on Lipids?</b>
									<p>
										Exercise increase good lipids (HDL) and decreases Triglyceride and LDL (bad lipids).
									</p>
								<td align="center">{{@$hypercholestrolemia_outcomes['effect_of_exercise_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['effect_of_exercise_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['effect_of_exercise_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif
				</tbody>
			</table>
		@endif
		<!-- Hypercholesterolemia end -->


		<!-- Diabetes start -->
		@if ($chronic_disease['DiabetesMellitus'] == "true")
			<table style="width:100%;" class="table table-border">
				<tbody>
					<tr>
						<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
							Diabetes
						</th>
					</tr>
					<tr style="border-right:1px solid black">
						<th  width="10%">
							Prognosis
						</th>
						<td  width="90%" style="border-right:1px solid black">
							{{@$diabetes_outcome['prognosis'] ?? ""}}
						</td>
					</tr>
					<tr style="border-right:1px solid black">
						<th  width="10%">
							Assessment
						</th>
						<td  width="90%" style="border-right:1px solid black">
							{{@$diabetes_outcome['assessment'] ?? ""}}
						</td>
					</tr>
				</tbody>
			</table>

			<table style="width:100%;" class="table table-border">
				<tbody>
					<tr>
						<th colspan="9" class=" table-primary" style="width:65%; text-align:center;">
							Goals
						</th>
						<th colspan="1" class="table-primary">
							Start Date
						</th>
						<th colspan="1" class=" table-primary">
							End Date
						</th>
						<th colspan="1" class="table-primary">
							Status
						</th>
					</tr>

				{{-- GOAL 1 STARTS --}}
				@if(isset($diabetes_outcome['goal1_status']) && $diabetes_outcome['goal1_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>To understand the importance of Blood Glucose Monitoring and control.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$diabetes_outcome['goal1_status'] ?? ""}}</td>
					</tr>

					@if(isset($diabetes_outcome['monitoring_blood_sugar_status']) && $diabetes_outcome['monitoring_blood_sugar_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Assess the patients current knowledge and understanding regarding disease</b>
							<p>
								Monitoring blood sugar helps to determine if you are meeting your glucose targets which helps to reduce the unpleasant symptoms of high and low blood sugar and avoid long-term diabetes complications. It is helpful to remember that the numbers are neither good nor bad. They are simply information used to help you learn what is working well and identify areas for improvement in your diabetes management.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['monitoring_blood_sugar_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['monitoring_blood_sugar_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['monitoring_blood_sugar_status'] ?? ""}}</td>
						</tr>
						@endif
					
					@if(isset($diabetes_outcome['importance_of_weight_status']) && $diabetes_outcome['importance_of_weight_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Weight daily, Explain the importance of weight loss to obese patients with diabetes</b>
						<p>
							When you have diabetes, there are huge benefits to losing weight if you're carrying extra weight. You'll have more energy, and you'll reduce your risk of serious complications like heart disease and stroke. Losing weight can help with your diabetes control too. And if you have type 2 diabetes, losing weight could even mean going into diabetes remission. Extra weight around your waist means fat can build up around your organs, like your liver and pancreas. This can cause something called insulin resistance. So, losing this weight could help the insulin you produce or the insulin you inject work properly.
						</p>
						</td>
						<td align="center">{{@$diabetes_outcome['importance_of_weight_start_date'] ?? ""}}</td>
						<td align="center">{{@$diabetes_outcome['importance_of_weight_end_date'] ?? ""}}</td>
						<td align="center">{{@$diabetes_outcome['importance_of_weight_status'] ?? ""}}</td>
					</tr>
					@endif
					
					@if(isset($diabetes_outcome['assess_the_pattern_status']) && $diabetes_outcome['assess_the_pattern_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Assess the pattern of physical activity</b>
						<p>
							If you have diabetes, being active makes your body more sensitive to insulin (the hormone that allows cells in your body to use blood sugar for energy), which helps manage your diabetes. Physical activity also helps control blood sugar levels and lowers your risk of heart disease and nerve damage.
						</p>
						</td>
						<td align="center">{{@$diabetes_outcome['assess_the_pattern_start_date'] ?? ""}}</td>
						<td align="center">{{@$diabetes_outcome['assess_the_pattern_end_date'] ?? ""}}</td>
						<td align="center">{{@$diabetes_outcome['assess_the_pattern_status'] ?? ""}}</td>
					</tr>
					@endif
					
					@if(isset($diabetes_outcome['monitor_blood_glucose_status']) && $diabetes_outcome['monitor_blood_glucose_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Monitor blood glucose levels before meals and at bedtime to control</b>
						<p>
							Blood glucose testing is one part of managing your diabetes successfully. Testing your blood glucose both before and after a meal allows you to see how that meal affects your blood glucose levels and helps you to understand which meals may be best for your blood glucose control. The most powerful influence on blood glucose levels comes from food. Whether you have type 1 or type2 diabetes, the peak blood glucose levels are often likely to occur around two hours after meals.
						</p>
						</td>
						<td align="center">{{@$diabetes_outcome['monitor_blood_glucose_start_date'] ?? ""}}</td>
						<td align="center">{{@$diabetes_outcome['monitor_blood_glucose_end_date'] ?? ""}}</td>
						<td align="center">{{@$diabetes_outcome['monitor_blood_glucose_status'] ?? ""}}</td>
					</tr>
					@endif
				@endif
				{{-- GOAL 1 ENDS --}}

				{{-- GOAL 2 STARTS --}}
				@if(isset($diabetes_outcome['goal2_status']) && $diabetes_outcome['goal2_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>To Understand the importance of Diabetic Diet.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$diabetes_outcome['goal2_status'] ?? ""}}</td>
					</tr>

					@if(isset($diabetes_outcome['abc_of_diabetes_status']) && $diabetes_outcome['abc_of_diabetes_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Understanding A, B & C of Diabetes:</b>
							<p>
								Making changes to your diet is a key part of managing type 2 diabetes. You may have heard of the "ABCs of diabetes." 
								This refers to three aspects of your health that should be well-controlled in order to manage your diabetes:
								<ul>
									<li>
										<b>A</b>A1C (a blood test that measures your average blood sugar level over the past few months).
									</li>
									<li>
										<b>B</b>lood pressure.
									</li>
									<li>
										<b>C</b>holesterol.
									</li>
								</ul>
								<p>
									Keeping your blood sugar at or near your goal level helps decrease the risk of complications that can affect the eyes, kidneys, and nerves. 
									Keeping your blood pressure and cholesterol levels under control helps reduce your risk of cardiovascular (heart) disease, which is a common complication of type 2 diabetes.
								</p>
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['abc_of_diabetes_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['abc_of_diabetes_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['abc_of_diabetes_status'] ?? ""}}</td>
						</tr>
					@endif
					
					
					@if(isset($diabetes_outcome['undercontrol_weight_status']) && $diabetes_outcome['undercontrol_weight_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Keeping your Weight under control</b>
							<p>
								Many factors affect how well a person's diabetes is controlled. You can reduce your risk of complications by following your health care provider's guidance around diet, exercise, blood sugar monitoring, and medication regimens. Dietary changes are typically focused on eating nutritious foods and getting to (and maintaining) a healthy weight. If you take insulin, you may also need to be consistent about what you eat and when.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['undercontrol_weight_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['undercontrol_weight_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['undercontrol_weight_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['assess_the_pattern_status']) && $diabetes_outcome['assess_the_pattern_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Seeing a Dietician</b>
							<p>
								Having to pay careful attention to your diet can be challenging. It can help to work with a dietitian to create a plan that is tailored to your specific situation (including what diabetes medications you take), lifestyle, and personal preferences.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['seeing_dietician_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['seeing_dietician_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['seeing_dietician_status'] ?? ""}}</td>
						</tr>
					@endif
				@endif
				{{-- GOAL 2 ENDS --}}

				{{-- GOAL 3 STARTS --}}
				@if(isset($diabetes_outcome['goal3_status']) && $diabetes_outcome['goal3_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>To Understand Hypoglycemia, hyperglycemia and how to prevent them.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$diabetes_outcome['goal2_status'] ?? ""}}</td>
					</tr>

					
					@if(isset($diabetes_outcome['signs_of_hyperglycemia_status']) && $diabetes_outcome['signs_of_hyperglycemia_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Assess for signs of hyperglycemia/hypoglycemia</b>
								<p>
									Hyperglycemia can become an emergency if you begin to develop symptoms of DKA: shortness of breath, fruity-smelling breath, nausea and vomiting, confusion, or you lose consciousness. Likewise, hypoglycemia requires emergency care if you begin to experience confusion, a loss of consciousness, or seizures.
								</p>
								<p>
									If you start experiencing symptoms of DKA or severe hypoglycemia, or if a loved one notices signs of these symptoms, seek medical care immediately.
								</p>
								<p>
									Talk to your healthcare team if you are experiencing frequent episodes of hyperglycemia and/or hypoglycemia, if your glucose is consistently above 240 mg/dL, or anytime you experience severe hypoglycemia.
								</p>
							</td>
							<td align="center">{{@$diabetes_outcome['signs_of_hyperglycemia_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['signs_of_hyperglycemia_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['signs_of_hyperglycemia_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['prevention_of_hyperglycemia_status']) && $diabetes_outcome['prevention_of_hyperglycemia_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Prevention of hyperglycemia by exercise to help lower blood sugar, follow your meal plan maintain a healthy weight, don't smoke and limit alcohol</b>
							<p>
								Exercise to help lower blood sugar. Work with your healthcare provider to make a daily activity plan. Follow your meal plan if you have one. Learn how carbohydrates impact your blood sugar, and work with your diabetes care team to find the best meal plan for you. Maintain a healthy weight.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['prevention_of_hyperglycemia_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['prevention_of_hyperglycemia_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['prevention_of_hyperglycemia_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['lower_blood_sugar_status']) && $diabetes_outcome['lower_blood_sugar_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Prevention of hypoglycemia to help lower blood sugar, follow your meal plan maintain a healthy weight, don't smoke and limit alcohol</b>
							<p>
								Monitor your blood sugar. Depending on your treatment plan, you may check and record your blood sugar level several times a week or multiple times a day. Careful monitoring is the only way to make sure that your blood sugar level remains within your target range.
							</p>
							<p>
								Don't skip or delay meals or snacks. If you take insulin or oral diabetes medication, be consistent about the amount you eat and the timing of your meals and snacks.
							</p>
							<p>
								Measure medication carefully and take it on time. Take your medication as recommended by your health care provider.
							</p>
							<p>
								Adjust your medication or eat additional snacks if you increase your physical activity.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['lower_blood_sugar_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['lower_blood_sugar_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['lower_blood_sugar_status'] ?? ""}}</td>
						</tr>
					@endif
				@endif
				{{-- GOAL 3 ENDS --}}

				{{-- GOAL 4 STARTS --}}
				@if(isset($diabetes_outcome['goal4_status']) && $diabetes_outcome['goal4_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>To Understand the importance of Diabetic Eye exam.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$diabetes_outcome['goal2_status'] ?? ""}}</td>
					</tr>

					
					@if(isset($diabetes_outcome['sugar_effect_on_eye_status']) && $diabetes_outcome['sugar_effect_on_eye_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Understanding how high blood sugar effects Eyes</b>
								<p>
									There are several eye problems related to diabetes. The most common affects the retina, a layer at the back of the eye; this is called "diabetic retinopathy." 
									In diabetic retinopathy, the small blood vessels in the retina grow abnormally and leak, which can lead to vision loss and eventually blindness if not treated.
								</p>
							</td>
							<td align="center">{{@$diabetes_outcome['sugar_effect_on_eye_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['sugar_effect_on_eye_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['sugar_effect_on_eye_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['sugar_ways_to_effect_on_eye_status']) && $diabetes_outcome['sugar_ways_to_effect_on_eye_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Understanding different ways Diabetes can affect the Eyes</b>
							<p>
								Other eye problems associated with diabetes include diabetic macular edema (swelling of the central area of the retina that has the sharpest vision), glaucoma (high pressure in the eyeball), and cataracts (clouding of the lens of the eye). Regular eye exams are essential for detecting retinopathy and other eye problems at an early stage, when the condition can be monitored and treated to preserve vision.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['sugar_ways_to_effect_on_eye_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['sugar_ways_to_effect_on_eye_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['sugar_ways_to_effect_on_eye_status'] ?? ""}}</td>
						</tr>
					@endif
				@endif
				{{-- GOAL 4 ENDS --}}

				{{-- GOAL 5 STARTS --}}
				@if(isset($diabetes_outcome['goal5_status']) && $diabetes_outcome['goal5_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
							<b>Understanding how Diabetic damage the nerves in the Foot.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$diabetes_outcome['goal5_status'] ?? ""}}</td>
					</tr>
					
					@if(isset($diabetes_outcome['foot_nerves_damage_status']) && $diabetes_outcome['foot_nerves_damage_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Understanding how Diabetic damage the nerves in the Foot.</b>
								<p>
									Diabetes can decrease blood flow to the feet and damage the nerves that carry sensation; this nerve damage is known as "diabetic neuropathy." 
									Because people with neuropathy may lose their ability to sense pain, they are at increased risk for developing potentially serious foot-related complications such as ulcers. 
									Foot complications are very common among people with diabetes and sometimes go unnoticed until symptoms become severe.
								</p>
							</td>
							<td align="center">{{@$diabetes_outcome['foot_nerves_damage_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['foot_nerves_damage_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['foot_nerves_damage_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['protect_feet_status']) && $diabetes_outcome['protect_feet_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>How to protect your feet in Diabetes?</b>
							<p>
								Other eye problems associated with diabetes include diabetic macular edema (swelling of the central area of the retina that has the sharpest vision), glaucoma (high pressure in the eyeball), and cataracts (clouding of the lens of the eye). 
								Regular eye exams are essential for detecting retinopathy and other eye problems at an early stage, when the condition can be monitored and treated to preserve vision.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['protect_feet_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['protect_feet_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['protect_feet_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['foot_examination_status']) && $diabetes_outcome['foot_examination_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>How to do your foot examination?</b>
							<p>
								Self-exams and foot care — It is important to examine your feet every day. This should include looking carefully at all parts of your feet, especially the area between the toes. Look for broken skin, ulcers, blisters, areas of increased warmth or redness, or changes in callus formation; let your health care provider know if you notice if any of these changes or have any concerns. 
							</p>
							<p>
								It may help to make the foot exam a part of your daily bathing or dressing routine. You might need to use a mirror to see the bottoms of your feet clearly. If you are unable to reach your feet or see them completely, even with a mirror, ask another person (such as a spouse or other family member) to help you. It is important to dry your feet thoroughly after bathing and wear cotton socks and comfortable, well-fitting shoes.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['foot_examination_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['foot_examination_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['foot_examination_status'] ?? ""}}</td>
						</tr>
					@endif
				@endif
				{{-- GOAL 5 ENDS --}}

				{{-- GOAL 6 STARTS --}}
				@if(isset($diabetes_outcome['goal6_status']) && $diabetes_outcome['goal6_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
							<b>To understand Cardiovascular Complications secondary to Diabetes.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$diabetes_outcome['goal6_status'] ?? ""}}</td>
					</tr>
					
					@if(isset($diabetes_outcome['death_cause_in_diabetes_status']) && $diabetes_outcome['death_cause_in_diabetes_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Learning the leading cause of death in Diabetics.</b>
								<p>People with diabetes are at increased risk of cardiovascular disease, which can lead to heart attack and stroke. Cardiovascular disease is the leading cause of death in people with diabetes.
								</p>
							</td>
							<td align="center">{{@$diabetes_outcome['death_cause_in_diabetes_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['death_cause_in_diabetes_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['death_cause_in_diabetes_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['risk_of_cardio_disease_status']) && $diabetes_outcome['risk_of_cardio_disease_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Learning three ways to decrease the risk of Cardio-vascular disease?</b>
							<p>
								Whether you have type 1 or type 2 diabetes, you can lower your risk of cardiovascular disease by Quitting Smoking, controlling cholesterol and daily use of aspirin.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['risk_of_cardio_disease_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['risk_of_cardio_disease_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['risk_of_cardio_disease_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['cholesterol_healthy_range_status']) && $diabetes_outcome['cholesterol_healthy_range_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Learning to keep your cholesterol and triglyceride levels in a healthy range.</b>
							<p>
								Your health care provider can measure these with a blood test. In addition to making healthy lifestyle changes, most people with diabetes will also need to take a cholesterol-lowering medication. 
								If you are over 40 years old or have multiple risk factors for cardiovascular disease (e.g., family history, high cholesterol, high blood pressure, or obesity), 
								your doctor will likely prescribe a cholesterol-lowering medication called a statin. 
								In people with diabetes, statins have been shown to decrease the future risk of heart attacks, strokes, and death, even when cholesterol levels are normal.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['cholesterol_healthy_range_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['cholesterol_healthy_range_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['cholesterol_healthy_range_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['low_dose_aspirin_status']) && $diabetes_outcome['low_dose_aspirin_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Consider daily low-dose aspirin, depending on your other conditions.</b>
							<p>
								Most people with diabetes and heart disease (such as history of angina or heart attack) should take low-dose aspirin (for example, 81 mg per day).
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['low_dose_aspirin_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['low_dose_aspirin_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['low_dose_aspirin_status'] ?? ""}}</td>
						</tr>
					@endif
				@endif
				{{-- GOAL 6 ENDS --}}

				{{-- GOAL 7 STARTS --}}
				@if(isset($diabetes_outcome['goal7_status']) && $diabetes_outcome['goal7_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
							<b>To understand Kidney complications secondary to diabetes.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$diabetes_outcome['goal7_status'] ?? ""}}</td>
					</tr>
					
					@if(isset($diabetes_outcome['diabetes_effect_on_kidneys_status']) && $diabetes_outcome['diabetes_effect_on_kidneys_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Understanding the effect of diabetes on Kidneys.</b>
								<p>
									Diabetes can alter the normal function of the kidneys. Kidney problems related to diabetes are referred to as "diabetic kidney disease" or by the older term, "diabetic nephropathy." Over time, diabetic kidney disease can lead to chronic kidney disease and even kidney failure.
								</p>
							</td>
							<td align="center">{{@$diabetes_outcome['diabetes_effect_on_kidneys_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['diabetes_effect_on_kidneys_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['diabetes_effect_on_kidneys_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['know_how_kidneys_effected_status']) && $diabetes_outcome['know_how_kidneys_effected_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>How to know if your kidneys are being effected by diabetes?</b>
							<p>
								To monitor your kidney function, your health care provider will check your blood creatinine level and use this to calculate an estimated glomerular filtration rate, or eGFR, which measures how well your kidneys are working. Your provider will also order urine tests to measure the amount of protein in your urine. When the kidneys are working normally, they prevent protein from leaking into the urine, so finding protein (measured as albumin) in the urine (even in very small amounts) may be an early sign of kidney damage. These tests are usually checked once yearly.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['know_how_kidneys_effected_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['know_how_kidneys_effected_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['know_how_kidneys_effected_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['protect_kidneys_status']) && $diabetes_outcome['protect_kidneys_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>How to protect your kidneys if diabetes has started to damage it?</b>
							<p>
								If you continue to have protein in your urine over time, your health care provider may prescribe a medication called an angiotensin-converting enzyme (ACE) inhibitor or angiotensin receptor blocker (ARB). 
								These medications can help decrease the amount of protein in the urine and slow the progression of kidney disease. 
								These medications also help lower blood pressure; this is important as high blood pressure can speed up the development of kidney problems.
							</p>
							<p>
								A class of medications called sodium-glucose cotransporter 2 (SGLT2) inhibitors lowers blood glucose and blood pressure and prevents worsening of kidney function in people with early kidney damage, especially when the urine albumin level is high.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['protect_kidneys_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['protect_kidneys_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['protect_kidneys_status'] ?? ""}}</td>
						</tr>
					@endif
				@endif
				{{-- GOAL 7 ENDS --}}

				{{-- GOAL 8 STARTS --}}
				@if(isset($diabetes_outcome['goal8_status']) && $diabetes_outcome['goal8_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
							<b>To recognize the importance if Blood Pressure control in diabetic patients.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$diabetes_outcome['goal8_status'] ?? ""}}</td>
					</tr>
					
					@if(isset($diabetes_outcome['bp_recommendation_status']) && $diabetes_outcome['bp_recommendation_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>What should be your BP if you are diabetic?</b>
								<p>In general, experts recommend keeping blood pressure below 130/80 mmHg for adults with diabetes.</p>
							</td>
							<td align="center">{{@$diabetes_outcome['bp_recommendation_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['bp_recommendation_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['bp_recommendation_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['how_to_lower_bp_status']) && $diabetes_outcome['how_to_lower_bp_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>How to lower your BP?</b>
							<p>
								If you need to lower your blood pressure, your provider will probably recommend lifestyle changes such as weight loss, exercise, 
								changing your diet (to cut back on salt and processed foods and eat more fruits, vegetables, and whole grains), quitting smoking (if you smoke), and cutting back on alcohol. 
								Most people with type 2 diabetes also need to take medications to keep their blood pressure within the goal range. 
								Your health care provider can talk to you about the benefits and risks of the different treatment options.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['how_to_lower_bp_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['how_to_lower_bp_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['how_to_lower_bp_status'] ?? ""}}</td>
						</tr>
					@endif
				@endif
				{{-- GOAL 8 ENDS --}}

				{{-- GOAL 9 STARTS --}}
				@if(isset($diabetes_outcome['goal9_status']) && $diabetes_outcome['goal9_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>To recognize the signs and symptoms of exacerbation that must be reported to the doctor/nurse.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$diabetes_outcome['goal3_status'] ?? ""}}</td>
					</tr>

					@if(isset($diabetes_outcome['monitor_hunger_and_fatigue_status']) && $diabetes_outcome['monitor_hunger_and_fatigue_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Monitor hunger and fatigue that would be exacerbate later</b>
							<p>
								<b>Fatigue:</b>Your body isn't getting the energy it needs from the food you're eating, so you may feel very tired. Extreme hunger:Even after you eat, you may still feel very hungry. That's because your muscles aren't getting the energy they need from the food; your body's insulin resistance keeps glucose from entering the muscle and providing energy. Therefore, the muscles and other tissues send a “hunger” message, trying to get more energy into the body. Blood glucose fluctuation is often thought of as the first cause of fatigue and hunger in diabetes. Treating both conditions is most successful when regarded as whole, rather than separate, conditions. Healthy lifestyle habits, social support, and mental health therapies can positively impact on both at the same time.
							</p>
							<p>
								<b>Extreme hunger:</b>Even after you eat, you may still feel very hungry. That’s because your muscles aren’t getting the energy they need from the food; your body’s insulin resistance keeps glucose from entering the muscle and providing energy. Therefore, the muscles and other tissues send a “hunger” message, trying to get more energy into the body.
							</p>
							<p>
								Blood glucose fluctuation is often thought of as the first cause of fatigue and hunger in diabetes. Treating both conditions is most successful when regarded as whole, rather than separate, conditions. Healthy lifestyle habits, social support, and mental health therapies can positively impact on both at the same time.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['monitor_hunger_and_fatigue_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['monitor_hunger_and_fatigue_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['monitor_hunger_and_fatigue_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['assess_frequent_urination_status']) && $diabetes_outcome['assess_frequent_urination_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Assess Frequent urination, dry mouth, or blurred vision</b>
							<p>
								<b>Frequent urination:</b>This is related to drinking so much more to satisfy your thirst. Since you’re drinking more, you’ll have to urinate more. Additionally, the body will try to get rid of the excess glucose through urination.
							</p>
							<p>
								<b>Blurry vision:</b>To get more fluid into the blood to counteract the high blood glucose level, your body may pull fluid from the eyes. You may have trouble focusing then, leading to blurry vision.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['assess_frequent_urination_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['assess_frequent_urination_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['assess_frequent_urination_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($diabetes_outcome['assess_slow_healing_status']) && $diabetes_outcome['assess_slow_healing_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Assess slow healing of wound</b>
							<p>
								<b>Slow wound healing:</b>Like the body’s inability to fight off infections, it might take longer for wounds (even small cuts) to heal. The high blood glucose level affects how well the white blood cells (which are in charge of healing wounds) work.
							</p>
							</td>
							<td align="center">{{@$diabetes_outcome['assess_slow_healing_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['assess_slow_healing_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['assess_slow_healing_status'] ?? ""}}</td>
						</tr>
					@endif
				@endif
				{{-- GOAL 9 ENDS --}}
				</tbody>
			</table>
		@endif
		<!-- Diabetes end -->

		<!-- COPD start -->
		@if($chronic_disease['ChronicObstructivePulmonaryDisease'] == "true")
			<table style="width:100%;" class="table table-border">
				<tbody>
					<tr>
						<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
							COPD
						</th>
					</tr>
					<tr>
						<th colspan="2" width="10%">
							Prognosis
						</th>
						<td colspan="10">
							{{@$copd_outcomes['prognosis'] ?? ""}}
						</td>
					</tr>
					<tr>
						<th colspan="2" width="10%">
							Assessment
						</th>
						<td colspan="10">
						{{@$copd_outcomes['assessment'] ?? ""}}
						</td>
					</tr>
				</tbody>
			</table>

			<table style="width:100%;" class="table table-border">
				<tbody>
					<tr>
						<th colspan="9" class=" table-primary" style="width:65%; text-align:center;">
							Goals
						</th>
						<th colspan="1" class="table-primary">
							Start Date
						</th>
						<th colspan="1" class=" table-primary">
							End Date
						</th>
						<th colspan="1" class="table-primary">
							Status
						</th>
					</tr>

					{{-- GOAL 1 STARTS --}}
					@if(isset($copd_outcomes['goal1_status']) && $copd_outcomes['goal1_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									Provide education on COPD.
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$copd_outcomes['goal1_status'] ?? ""}}</td>
						</tr>
					@endif

					@if(isset($copd_outcomes['educate_on_disease_status']) && $copd_outcomes['educate_on_disease_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To educate the patient of symptoms and complications of COPD</b>
							<p>
								At first, COPD often causes no symptoms. As it gets worse it can make you:
								<ul>
									<li> Feel short of breath, especially when you are moving around </li>
									<li> Wheeze (make a whistling or squeaking noise as you breathe) </li>
									<li> Cough and spit up sputum (mucus) </li>
									<li> Cough and spit up sputum (mucus) </li>
								</ul>
							</p>
							<p>
								People who have COPD are also at increased risk for:
								<ul>
									<li> Infections, such as pneumonia </li>
									<li> Lung cancer </li>
									<li> Heart problems </li>
								</ul>
							</p>
							<td align="center">{{@$copd_outcomes['educate_on_disease_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['educate_on_disease_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['educate_on_disease_status'] ?? ""}}</td>
						</tr>
					@endif
					{{-- GOAL 1 ENDS --}}


					{{-- GOAL 2 STARTS --}}
					@if(isset($copd_outcomes['goal2_status']) && $copd_outcomes['goal2_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								Smoking Cessation.
							</td>
							<td align="center"></td>
							<td align="center"></td>
							<td align="center">{{@$copd_outcomes['goal2_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($copd_outcomes['smoking_cessation_status']) && $copd_outcomes['smoking_cessation_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To educate patients on the importance of smoking cessation (if applicable) for better COPD management.</b>
							<p>
								The most common cause of COPD is smoking. Smoke can damage the lungs forever and cause COPD. 
								If you continue to smoke the damage to the lungs will continue and your COPD will continue to worsen. 
								Quitting smoking might not repair the damage already done but will significantly reduce the progression of COPD, 
								in addition, it will also lower the chances that you'll have a heart attack or stroke, and it will help you feel better and live longer. 
								Quitting smoking is the most important thing that you can do for your health. This is true no matter how long you have smoked or how much you smoke. 
								If you are having trouble quitting, your doctor can help as many options are available to assist you.
							</p>
							<td align="center">{{@$copd_outcomes['smoking_cessation_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['smoking_cessation_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['smoking_cessation_status'] ?? ""}}</td>
						</tr>
					@endif
					{{-- GOAL 2 ENDS --}}


					{{-- GOAL 3 STARTS --}}
					@if(isset($copd_outcomes['goal3_status']) && $copd_outcomes['goal3_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									Lowering Risk of Infection
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$copd_outcomes['goal3_status'] ?? ""}}</td>
						</tr>
					@endif

					@if(isset($copd_outcomes['lowering_infection_risk_status']) && $copd_outcomes['lowering_infection_risk_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To educate the patient on lowering the risk of infections</b>
							<p>
								Certain infections can be very hard on your lungs and can cause COPD symptoms to flare up. You can lower your risk by getting certain vaccines. 
								These include the flu shot every year, the pneumonia vaccine at least once, and the COVID-19 vaccine and boosters.
							</p>
							<p>
								In addition, wash your hands often and stay away from people who are sick. Wearing a face mask in crowded places can also help lower your risk of infection.
							</p>
							<td align="center">{{@$copd_outcomes['lowering_infection_risk_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['lowering_infection_risk_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['lowering_infection_risk_status'] ?? ""}}</td>
						</tr>
					@endif
					{{-- GOAL 3 ENDS --}}


					{{-- GOAL 4 STARTS --}}
					@if(isset($copd_outcomes['goal4_status']) && $copd_outcomes['goal4_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									Lifestyle changes that can help with COPD.
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$copd_outcomes['goal4_status'] ?? ""}}</td>
						</tr>
					@endif
						
					@if(isset($copd_outcomes['educate_on_lifestyle_status']) && $copd_outcomes['educate_on_lifestyle_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To educate the patient on lifestyle changes.</b>
							<p>
								If things like fumes, pollution, or dust make your breathing worse, try to avoid these triggers. 
								Eating a healthy diet can help improve your health. 
								You can also improve your health by following your pulmonary rehab plan if you have one or finding other ways to move your body.
							</p>
							<td align="center">{{@$copd_outcomes['educate_on_lifestyle_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['educate_on_lifestyle_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['educate_on_lifestyle_status'] ?? ""}}</td>
						</tr>
					@endif
					{{-- GOAL 4 ENDS --}}


					{{-- GOAL 5 STARTS --}}
					@if(isset($copd_outcomes['goal5_status']) && $copd_outcomes['goal5_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									Know when it is an emergency.
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$copd_outcomes['goal5_status'] ?? ""}}</td>
						</tr>
					@endif
						
					@if(isset($copd_outcomes['educate_on_emergency_status']) && $copd_outcomes['educate_on_emergency_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To educate the patient on lifestyle changes.</b>
							<p>
								To educate the patient on when to contact emergency services.
							</p>
							<p>
								Call for an ambulance (in the US, call 9-1-1) if:
								<ul>
									<li>You are having trouble breathing, even when you are resting.</li>
									<li>You are coughing up blood.</li>
									<li>You have signs of a heart attack, such as:</li>
									<ul>
										<li>Severe chest pain, pressure, or discomfort with:</li>
										<ul>
											<li>Trouble breathing, sweating, upset stomach, or cold clammy skin</li>
											<li>Pain in your arms, back, or jaw</li>
											<li>Worse pain with activities like walking upstairs</li>
										</ul>
									</ul>
									
									<li>Fast or irregular heartbeat</li>
									<li>Feeling dizzy, faint, or weak</li>
								</ul>
							</p>
							<p>
								Call your regular doctor for advice if:
								<ul>
									<li>You have a fever of 100.4°F (38°C) or higher or chills.</li>
									<li>You are feeling weak or more short of breath than usual when doing your normal activities.</li>
									<li>You have a new or worsening cough, wheezing, sputum, or shortness of breath.</li>
								</ul>
							</p>
							<td align="center">{{@$copd_outcomes['educate_on_emergency_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['educate_on_emergency_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['educate_on_emergency_status'] ?? ""}}</td>
						</tr>
					@endif
					{{-- GOAL 5 ENDS --}}


					{{-- GOAL 6 STARTS --}}
					@if(isset($copd_outcomes['goal6_status']) && $copd_outcomes['goal6_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									Know when you are having a COPD flare.
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$copd_outcomes['goal6_status'] ?? ""}}</td>
						</tr>
					@endif
						
					@if(isset($copd_outcomes['having_copd_flare_status']) && $copd_outcomes['having_copd_flare_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To educate patients on COPD flare.</b>
							<p>
								A COPD flare is when symptoms suddenly get worse. Doctors sometimes call flares "exacerbations." 
								If you have a flare, you might need some new medicines until your symptoms improve. 
								Or you might need to take medicine in a different way than through an inhaler for a while. 
								Please contact your PCP right away in case of a flare-up.
							</p>
							<td align="center">{{@$copd_outcomes['having_copd_flare_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['having_copd_flare_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['having_copd_flare_status'] ?? ""}}</td>
						</tr>
					@endif
					{{-- GOAL 6 ENDS --}}


					{{-- GOAL 7 STARTS --}}
					@if(isset($copd_outcomes['goal7_status']) && $copd_outcomes['goal7_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									Prevention of COPD flare.
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$copd_outcomes['goal7_status'] ?? ""}}</td>
						</tr>
					@endif
						
					@if(isset($copd_outcomes['prevention_copd_flare_status']) && $copd_outcomes['prevention_copd_flare_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To educate the patient on the prevention of COPD flare.</b>
							<p>
								If you have COPD, you need a flu shot every fall and the pneumonia vaccine at least once. 
								You should also get vaccinated against COVID-19. This is because infections like the flu, pneumonia, and COVID-19 can be very hard on your lungs. 
								It is important to try to prevent them.
							</p>
							<p>
								People who have more than 2 COPD flares a year might need medicine to help prevent them. These include:
								<ul>
									<li>Azithromycin – This is an antibiotic pill that is taken at a low dose. It can help prevent flares in some people but is not used in everyone with COPD. That's because azithromycin can cause other problems.</li>
									<li>Roflumilast – This medicine comes in a pill you take by mouth. It can help reduce flares in some people with chronic bronchitis and severe COPD.</li>
								</ul>
								It is a good idea to keep a list of all the medicines you take and bring it with you every time you visit a doctor or nurse.
							</p>
							<td align="center">{{@$copd_outcomes['prevention_copd_flare_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['prevention_copd_flare_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['prevention_copd_flare_status'] ?? ""}}</td>
						</tr>
					@endif
					{{-- GOAL 7 ENDS --}}


					{{-- GOAL 8 STARTS --}}
					@if(isset($copd_outcomes['goal8_status']) && $copd_outcomes['goal8_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									Understand the importance of treatment adherence and regular follow-ups.
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$copd_outcomes['goal8_status'] ?? ""}}</td>
						</tr>
					@endif
						
					@if(isset($copd_outcomes['followup_imp_status']) && $copd_outcomes['followup_imp_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To educate the patient on the importance of treatment adherence and regular follow-ups with PCP and Pulmonologist.</b>
							<p>
								It is very important that you understand the risks COPD poses to your health and to get better outcomes we need to manage COPD as best as we can. 
								What you can do is regularly take your medications for COPD, quit smoking (if applicable), and schedule regular follow up with your Pulmonologist/PCP to be evaluated.
							</p>
							<td align="center">{{@$copd_outcomes['followup_imp_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['followup_imp_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['followup_imp_status'] ?? ""}}</td>
						</tr>
					@endif
					{{-- GOAL 8 ENDS --}}
				</tbody>
			</table>
		@endif
		<!-- COPD end -->

		<!-- CKD start -->
		@if($chronic_disease['CKD'] == "true")
			<table style="width:100%;" class="table table-border">
				<tbody>
					<tr>
						<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
							CKD
						</th>
					</tr>
					<tr>
						<th width="10%">
							Prognosis
						</th>
						<td width="90%">
							{{@$ckd_outcomes['prognosis'] ?? ""}}
						</td>
					</tr>
					<tr>
						<th width="10%">
							Assessment
						</th>
						<td width="90%">
							{{@$ckd_outcomes['assessment']}}
						</td>
					</tr>
				</tbody>
			</table>

			<table style="width:100%;" class="table table-border">
				<tbody>
					<tr>
						<th colspan="9" class=" table-primary" style="width:65%; text-align:center;">
							Goals
						</th>
						<th colspan="1" class="table-primary">
							Start Date
						</th>
						<th colspan="1" class=" table-primary">
							End Date
						</th>
						<th colspan="1" class="table-primary">
							Status
						</th>
					</tr>

					{{-- GOAL 1 STARTS --}}
					@if(isset($ckd_outcomes['goal1_status']) && $ckd_outcomes['goal1_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									Assess patient knowledge on CKD and its complications and educate on steps to prevent worsening of renal function. 
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$ckd_outcomes['goal1_status'] ?? ""}}</td>
						</tr>
					

						@if(isset($ckd_outcomes['educate_on_ckd_status']) && $ckd_outcomes['educate_on_ckd_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate patient on CKD.</b>
								<p>
									Chronic kidney disease ("CKD") is when the kidneys stop working as well as they should. When they are working normally, the kidneys filter the blood and remove waste and excess salt and water.	
								</p>
								<p>
									In people with CKD, the kidneys slowly lose the ability to filter blood. In time, the kidneys can stop working completely. That is why it is so important to keep CKD from getting worse
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['educate_on_ckd_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['educate_on_ckd_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['educate_on_ckd_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['worsening_symptoms_status']) && $ckd_outcomes['worsening_symptoms_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate patient on symptoms of worsening CKD.</b>
								<p>
									At first, CKD causes no symptoms. As the disease gets worse, it can:
									<ul>
										<li>Make your feet, ankles, or legs swell (doctors call this "edema")</li>
										<li>Give you high blood pressure</li>
										<li>Make you very tired</li>
										<li>Damage your bones</li>
									</ul>
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['worsening_symptoms_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['worsening_symptoms_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['worsening_symptoms_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['followup_importance_status']) && $ckd_outcomes['followup_importance_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To understand the importance of follow up with PCP and nephrologist if needed for management of CKD.</b>
								<p>
									Your doctor will want to see you regularly. You will probably have appointments at least once a year, and you will get regular tests to check your kidneys. These include blood and urine tests.
								</p>
								<p>
									If your CKD gets worse over time, you will probably need to see a "nephrologist." This is a doctor who takes care of people with kidney disease.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['followup_importance_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['followup_importance_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['followup_importance_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['prevent_worsening_status']) && $ckd_outcomes['prevent_worsening_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To understand what the patient can do to prevent worsening of kidney function.</b>
								<p>
									If you have CKD, you can protect your kidneys if you:
									<ul>
										<li>Take all of your prescribed medicines every day, and follow all of your doctor's instructions for how to take them.</li>
										<li>Keep your blood sugar in a healthy range, if you have diabetes.</li>
										<li>Change your diet, if your doctor or nurse recommends to. They might suggest working with a dietitian (nutrition expert).</li>
										<li>Quit smoking, if you smoke.</li>
										<li>Lose weight, if you are overweight.</li>
									</ul>
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['prevent_worsening_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['prevent_worsening_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['prevent_worsening_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['aviod_medications_status']) && $ckd_outcomes['aviod_medications_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To learn about the medication that you should avoid</b>
								<p>
									Avoid medicines that can harm the kidneys – One example is "nonsteroidal antiinflammatory drugs," or "NSAIDs." These medicines include ibuprofen (sample brand names: Advil, Motrin) and naproxen (sample brand name: Aleve). There are other medicines that people with CKD need to avoid, too. Check with your doctor, nurse, or kidney specialist before starting any new medicines or supplements, even those you can buy without a prescription.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['aviod_medications_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['aviod_medications_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['aviod_medications_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['ckd_treatment_status']) && $ckd_outcomes['ckd_treatment_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To understand how CKD is treated and importance of treatment compliance.</b>
								<p>
									People in the early stages of CKD can take medicines to keep the disease from getting worse. For example, many people with CKD should take medicines known as "ACE inhibitors" or "angiotensin receptor blockers." If your doctor prescribes these medicines, it is very important that you take them every day as directed. If they cause side effects or cost too much, tell your doctor. They might have solutions to offer.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['ckd_treatment_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['ckd_treatment_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['ckd_treatment_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif
					{{-- GOAL 1 ENDS --}}

					{{-- GOAL 2 STARTS --}}
					@if(isset($ckd_outcomes['goal2_status']) && $ckd_outcomes['goal2_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									Assess patient knowledge on risk factors of CKD and ways to prevent developing CKD. 
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$ckd_outcomes['goal2_status'] ?? ""}}</td>
						</tr>
					

						@if(isset($ckd_outcomes['educate_on_risk_factors_status']) && $ckd_outcomes['educate_on_risk_factors_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate on factors that can increase risk of developing CKD.</b>
								<p>
									A number of factors can increase the risk of developing CKD, including:
									<ul>
										<li>Diabetes mellitus</li>
										<li>High blood pressure</li>
										<li>A family history of kidney disease</li>
										<li>Obesity</li>
										<li>Smoking</li>
										<li>Older age</li>
										<li>Having protein in the urine</li>
										<li>Having autoimmune diseases such as lupus</li>
										<li>Being from a Black population or belonging to certain other underrepresented groups.</li>
									</ul>
									<p>
										If you have one or more of these risk factors you are at an increased risk so in order to lower the chances of developing CKD you should work with your PCP on addressing these factors if possible.
									</p>
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['educate_on_risk_factors_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['educate_on_risk_factors_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['educate_on_risk_factors_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['educate_on_lowering_risk_status']) && $ckd_outcomes['educate_on_lowering_risk_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate patient on lowering the risk of CKD development and rate of CKD progression.</b>
								<p>
									The first step in the treatment of CKD is to determine the underlying cause. Some causes are reversible, including use of medications that impair kidney function, blockage in the urinary tract, or decreased blood flow to the kidneys. Treatment of reversible causes may prevent CKD from worsening.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['educate_on_lowering_risk_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['educate_on_lowering_risk_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['educate_on_lowering_risk_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['hypertension_effects_risk_status']) && $ckd_outcomes['hypertension_effects_risk_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>Understanding effects of Hypertension on Kidneys</b>
								<p>
									Hypertension, or high blood pressure, is present in 80 to 85 percent of people with CKD. Maintaining good blood pressure control is the most important goal for trying to slow the progression of CKD. Taking a medication called an angiotensin-converting enzyme (ACE) inhibitor or angiotensin receptor blocker (ARB) reduces blood pressure and levels of protein in the urine and is thought to slow the progression of CKD to a greater extent than some of the other medicines used to treat high blood pressure. Newer medications have also become available in recent years that work with ACE inhibitors or ARBs to slow the progression of CKD.
								</p>
								<p>
									Sometimes, a diuretic (water pill) or other medication is also added. You may be asked to monitor your blood pressure at home to be sure that your blood pressure is well controlled
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['hypertension_effects_risk_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['hypertension_effects_risk_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['hypertension_effects_risk_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['healthy_diet_status']) && $ckd_outcomes['healthy_diet_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To understand healthy diet for Kidneys: </b>
								<p>
									Changes in your diet may be recommended to control or prevent some of the complications of CKD; most important is salt restriction to help control the blood pressure.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['healthy_diet_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['healthy_diet_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['healthy_diet_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['protein_effects_food_status']) && $ckd_outcomes['protein_effects_food_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To understand effect of Protein on Kidneys </b>
								<p>
									Restricting protein in the diet may slow the progression of CKD, although it is not clear if the benefits of protein restriction are worth the difficulty of sticking to a low-protein diet, particularly when other medications to slow progression of CKD are used. Although a reduced-protein diet may delay dialysis for several years, the unappetizing nature of the diet is difficult for most people to tolerate. Speak to your health care provider about the advantages and disadvantages of a low-protein diet. Some people may benefit from a plant-based diet.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['protein_effects_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['protein_effects_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['protein_effects_food_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['elevated_cholesterol_status']) && $ckd_outcomes['elevated_cholesterol_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To understand health effects of elevated Cholesterol and triglycerides with CKD</b>
								<p>
									High cholesterol and triglyceride levels are common in people with kidney disease. High triglycerides have been associated with an increased risk of coronary artery disease, which can lead to heart attack.
								</p>
								<p>
									Treatments to reduce the risk of coronary artery disease are usually recommended, including dietary changes, medications for high triglyceride and cholesterol levels, stopping smoking, and tight blood sugar control in people with diabetes.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['elevated_cholesterol_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['elevated_cholesterol_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['elevated_cholesterol_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif
					{{-- GOAL 2 ENDS --}}

					{{-- GOAL 3 STRATS --}}
					@if(isset($ckd_outcomes['goal3_status']) && $ckd_outcomes['goal3_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									Assess patient knowledge on Diabetic Kidney Disease. 
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$ckd_outcomes['goal3_status'] ?? ""}}</td>
						</tr>
					

						@if(isset($ckd_outcomes['educate_on_dkd_status']) && $ckd_outcomes['educate_on_dkd_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate patient on DKD.</b>
								<p>
									The key complication of diabetic kidney disease is more advanced kidney disease, called chronic kidney disease.  People who develop diabetic kidney disease usually have no symptoms early on, although the condition puts them at risk of developing more serious kidney disease.
								</p>
								<p>
									The kidneys play an important role in the body: they filter the blood, removing waste products and excess salt and water. If the kidneys become diseased, they falter in their task, leaving the blood polluted.
								</p>
								<p>
									Finding out that you have early diabetic kidney disease can alert you that your kidneys are in danger. It is important to take steps to protect your kidneys before the problem advances. Information about advanced kidney disease is also available.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['educate_on_dkd_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['educate_on_dkd_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['educate_on_dkd_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['dkd_symptoms_status']) && $ckd_outcomes['dkd_symptoms_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate patient on DKD symptoms.</b>
								<p>
									Diabetic kidney disease commonly causes no symptoms until at least 80 percent of your kidneys' function is lost. To detect diabetic kidney disease, health care providers rely on tests that measure protein (albumin) levels in the urine and blood tests to evaluate the level of kidney function.
								</p>
								<p>
									When the kidneys are working normally, they prevent albumin from leaking into the urine, so finding albumin in the urine is a sign that the kidneys are in trouble. Often people who have diabetic kidney disease also have high blood pressure.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['dkd_symptoms_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['dkd_symptoms_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['dkd_symptoms_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['dkd_risk_factors_status']) && $ckd_outcomes['dkd_risk_factors_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate patient on risk factors of DKD.</b>
								<p>
									Having a family history of kidney disease or belonging to certain ethnic groups (eg, African American, Mexican, Pima Indian) can increase your risk of diabetic kidney disease. Although you cannot do anything to change your family history, there are several factors that increase your risk of developing diabetic kidney disease that you can change and control. These include:
									<ul>
										<li>Having chronically elevated blood sugar levels</li>
										<li>Being overweight or obese</li>
										<li>Smoking</li>
										<li>Having high blood pressure</li>
										<li>Having high cholesterol</li>
										<li>Having a diabetes-related vision problem (diabetic retinopathy) or nerve damage (diabetic neuropathy)</li>
									</ul>
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['dkd_risk_factors_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['dkd_risk_factors_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['dkd_risk_factors_status'] ?? ""}}</td>
							</tr>
						@endif

						@if(isset($ckd_outcomes['dkd_progression_status']) && $ckd_outcomes['dkd_progression_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate patient on prevention of progression of DKD.</b>
								<p>
									People with diabetes often focus on keeping their blood sugar levels in the right ranges. And while it is important to control blood sugar, it turns out that controlling blood pressure is at least as important. That's because high blood sugar and high blood pressure work in concert to damage the blood vessels and organ systems.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['dkd_progression_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['dkd_progression_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['dkd_progression_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['healthy_lifestyle_effect_status']) && $ckd_outcomes['healthy_lifestyle_effect_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate the effect of healthy lifestyle on DKD</b>
								<p>
									The most important things you can do to stall kidney disease and protect against other diabetes complications are to:
									<ul>
										<li>healthy lifestyle choices</li>
									</ul>
									<p>
										Changing your lifestyle can have a big impact on the health of your kidneys. The following measures are recommended for everyone, but are especially important if you have diabetic kidney disease:
										<ul>
											<li>Limit the amount of sodium (salt) you eat to less than 2 grams per day)</li>
											<li>If you smoke, quit smoking</li>
											<li>Lose weight if you are overweight</li>
										</ul>
									</p>
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['healthy_lifestyle_effect_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['healthy_lifestyle_effect_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['healthy_lifestyle_effect_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['blood_sugar_control_status']) && $ckd_outcomes['blood_sugar_control_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate the effect of controlling blood sugar</b>
								<p> Keep your blood sugar as close to normal as possible. </p>
								<p> Keeping blood sugars close to normal can help prevent the long-term complications of diabetes mellitus. For most people, a target for fasting blood glucose and for blood glucose levels before each meal is 80 to 120 mg/dL (4.4 to 6.6 mmol/L); however, these targets may need to be individualized. </p>
								</td>
								<td align="center">{{@$ckd_outcomes['blood_sugar_control_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['blood_sugar_control_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['blood_sugar_control_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['hba1c_importance_status']) && $ckd_outcomes['hba1c_importance_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>educate importance of HBA1C</b>
								<p> 
									A blood test called A1C is also used to monitor blood sugar levels; the result provides an average of blood sugar levels over the last one to three months. An A1C of 7 percent or less is usually recommended; this corresponds to an average blood glucose of 150 mg/dL (8.3 mmol/L). Even small decreases in the A1C lower the risk of diabetes-related complications to some degree.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['hba1c_importance_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['hba1c_importance_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['hba1c_importance_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['control_blood_sugar_status']) && $ckd_outcomes['control_blood_sugar_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate how to bring blood sugars under control</b>
								<p>
									Managing your blood sugar involves lifestyle changes (eg, diet and exercise) as well as medications. Type 1 diabetes is treated with insulin. For type 2 diabetes, other medications are often used; some are not recommended for use in people with kidney problems, while others may help slow the progression of kidney disease. Your doctors will work with you to determine what combination of medications is best for you.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['control_blood_sugar_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['control_blood_sugar_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['control_blood_sugar_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['bp_effect_on_dkd_status']) && $ckd_outcomes['bp_effect_on_dkd_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate the effect of Blood Pressure on DKD</b>
								<p>
									Keep your blood pressure below 130/80, if possible
								</p>
								<p>
									Many people with diabetes have hypertension (high blood pressure). Although high blood pressure causes few symptoms, it has two negative effects: it stresses the cardiovascular system and speeds the development of diabetic complications of the kidney and eye. A health care provider can diagnose high blood pressure by measuring blood pressure on a regular basis.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['bp_effect_on_dkd_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['bp_effect_on_dkd_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['bp_effect_on_dkd_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($ckd_outcomes['hypertension_treatment_status']) && $ckd_outcomes['hypertension_treatment_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>To educate about the treatment of Hypertension</b>
									<p>
										The treatment of high blood pressure varies. If you have mild hypertension, your health care provider may recommend weight loss, exercise, decreasing the amount of salt in the diet, quitting smoking, and decreasing alcohol intake. These measures can sometimes reduce blood pressure to normal.
									</p>
									<p>
										If these measures are not effective or your blood pressure needs to be lowered quickly, your provider will likely recommend one of several high blood pressure medications. Your provider can discuss the pros and cons of each medication and the goals of treatment.
									</p>
									<p>
										A blood pressure reading below 130/80 is the recommended goal for most people with diabetic kidney disease, especially if you have more than 300 mg of albumin in your urine per day.
									</p>
								</td>
								<td align="center">{{@$ckd_outcomes['hypertension_treatment_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['hypertension_treatment_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['hypertension_treatment_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif
					{{-- GOAL 3 ENDS --}}
					
					{{-- GOAL 4 STARTS --}}
					@if(isset($ckd_outcomes['goal4_status']) && $ckd_outcomes['goal4_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									Assess knowledge of association between CKD and Cardiovascular disease. 
								</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$ckd_outcomes['goal4_status'] ?? ""}}</td>
						</tr>

						@if(isset($ckd_outcomes['ckd_heart_status']) && $ckd_outcomes['ckd_heart_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate patient on association between CKD and heart disease.</b>
								<p>
									There is a large body of evidence that patients with CKD have a substantial increase in cardiovascular risk that can be in part explained by an increase in traditional risk factors such as hypertension, diabetes, and the metabolic syndrome. CKD alone is also an independent risk factor for cardiovascular disease. So better management of CKD will result in lowering the risk of heart disease as well.
								</p>
								</td>
								<td align="center">{{@$ckd_outcomes['ckd_heart_start_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['ckd_heart_end_date'] ?? ""}}</td>
								<td align="center">{{@$ckd_outcomes['ckd_heart_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif
					{{-- GOAL 4 ENDS --}}
				</tbody>
			</table>
		@endif
		<!-- CKD end -->

		<!-- Hypertension start -->
		@if($chronic_disease['Hypertensions'] == "true")
			<table style="width:100%;" class="table table-border">
				<tbody>
					<tr>
						<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
							Hypertension
						</th>
					</tr>
					<tr>
						<th  width="10%">
							Prognosis
						</th>
						<td width="90%">
							{{@$hypertension_outcomes['prognosis'] ?? ""}}
						</td>
					</tr>
					<tr>
						<th width="10%">
							BP Assessment
						</th>
						<td width="90%">
						@if(isset($hypertension_outcomes['result']))
							@foreach($hypertension_outcomes['result'] as $key => $value)
								{{$value}}
							@endforeach
						@endif
						</td>
					</tr>
					<tr>
						<th width="10%">
							Assessment
						</th>
						<td width="90%">
							{{@$hypertension_outcomes['assessment'] ?? ""}}
						</td>
					</tr>
				</tbody>
			</table>

			<table style="width:100%;" class="table table-border">
				<tbody>
					<tr>
						<th colspan="9" class=" table-primary" style="width:65%; text-align:center;">
							Goals
						</th>
						<th colspan="1" class="table-primary">
							Start Date
						</th>
						<th colspan="1" class=" table-primary">
							End Date
						</th>
						<th colspan="1" class="table-primary">
							Status
						</th>
					</tr>

					@if(isset($hypertension_outcomes['goal1_status']) && $hypertension_outcomes['goal1_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To acquire Knowledge about Hypertension and its effect on the multiple body organs.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$hypertension_outcomes['goal1_status'] ?? ""}}</td>
						</tr>


						@if(isset($hypertension_outcomes['understanding_regarding_disease_status']) && $hypertension_outcomes['understanding_regarding_disease_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Educate patient on HTN and its long-term effects on the body.</b>
									<p>Educate patient on HTN and its long-term effects on the body.
										High blood pressure is a condition that puts you at risk for heart attack, stroke, and kidney disease. 
										It does not usually cause symptoms. But it can be serious. So, it is very important to have good BP control. 
										Most adults with hypertension have primary hypertension (formerly called "essential" hypertension), which means that the cause of the high blood pressure is not known. 
										A small subset of adults has secondary hypertension, 
										which means that there is an underlying and potentially correctable cause, usually a kidney or hormonal disorder. 
										Unfortunately, many people's blood pressure is not well controlled. According to a national survey, hypertension was in good control in only 47 percent of adults.
									</p>
								</td>
								<td align="center">{{@$hypertension_outcomes['understanding_regarding_disease_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['understanding_regarding_disease_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['understanding_regarding_disease_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif
					
					@if(isset($hypertension_outcomes['goal2_status']) && $hypertension_outcomes['goal2_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To educate patient on Lifestyle modifications to help with better BP control.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$hypertension_outcomes['goal2_status'] ?? ""}}</td>
						</tr>
					
				
						@if(isset($hypertension_outcomes['educate_about_dash_diet_status']) && $hypertension_outcomes['educate_about_dash_diet_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>
										Educate the patient about DASH diet
									</b>
									<p>
										DASH diet – The Dietary Approaches to Stop Hypertension (DASH) dietary pattern is high in vegetables, fruits, low-fat dairy products, whole grains, 
										poultry, fish, and nuts and low in sweets, sugar-sweetened beverages, and red meats. The DASH dietary pattern is consequently rich in potassium, 
										magnesium, calcium, protein, and fiber but low in saturated fat, total fat, and cholesterol. 
										Choose a diet rich in fruits, vegetables, and low-fat dairy products, and low in meats, sweets, and refined grains
									</p>
								</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_dash_diet_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_dash_diet_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_dash_diet_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($hypertension_outcomes['educate_about_sodium_diet_status']) && $hypertension_outcomes['educate_about_sodium_diet_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>
										Educate patient about low sodium diet
									</b>
									<p>
										Eat less salt (sodium): The most important thing you can do to cut down on sodium is to eat less processed food. 
										That means that you should avoid most foods that are sold in cans, boxes, jars, and bags. 
										You should also eat in restaurants less often
									</p>
								</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_sodium_diet_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_sodium_diet_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_sodium_diet_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($hypertension_outcomes['educate_about_excercise_status']) && $hypertension_outcomes['educate_about_excercise_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>
										Educate patient about importance of exercise
									</b>
									<p>
										Do something active for at least 30 minutes a day on most days of the week. 
										If you don't do any activity now, start by walking for just a few minutes every other day. Do that for a few weeks. 
										If you stick with it, try doing it for longer. But if you find that you don't like walking, try a different activity.
									</p>
								</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_excercise_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_excercise_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_excercise_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($hypertension_outcomes['educate_about_alcoholeffects_status']) && $hypertension_outcomes['educate_about_alcoholeffects_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>
										Educate patient on effects of alcohol on BP
									</b>
									<p>
										Limit the amount of alcohol you drink. (If applicable): If you are a woman, do not have more than 1 "standard drink" of alcohol a day. 
										If you are a man, do not have more than 2. A "standard drink"
									</p>
								</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_alcoholeffects_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_alcoholeffects_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_alcoholeffects_status'] ?? ""}}</td>
							</tr>
						@endif
						
						@if(isset($hypertension_outcomes['educate_about_smokingeffects_status']) && $hypertension_outcomes['educate_about_smokingeffects_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>
										Educate patients about the effect of smoking on BP
									</b>
									<p>
										If you have high blood pressure, it's also very important to quit smoking (if you smoke). 
										Quitting smoking might not bring your blood pressure down. 
										But it will lower the chances that you'll have a heart attack or stroke, and it will help you feel better and live longer.
									</p>
								</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_smokingeffects_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_smokingeffects_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_smokingeffects_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif
					
					@if(isset($hypertension_outcomes['goal3_status']) && $hypertension_outcomes['goal3_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Patient will understand the importance of Treatment Adherence and Regular BP monitoring.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$hypertension_outcomes['goal3_status'] ?? ""}}</td>
						</tr>
					
						@if(isset($hypertension_outcomes['regular_bp_monitoring_status']) && $hypertension_outcomes['regular_bp_monitoring_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Explain to the patient the role of regular BP monitoring and treatment adherence in BP control.</b>
									<p>
									You need to adhere to your treatment regimen for optimal Blood pressure control. Forgetting to take medications can result in high BP which will increase the risk of complications. 
									Also, you need to have an understanding of the medications you take for Blood pressure control. 
									If you don’t know then please bring all the prescription medications at your next visit and ask your PCP. 
									In addition to that, regular BP monitoring is very important as per the PCP/cardiologist recommendations.
									</p>
								</td>
								<td align="center">{{@$hypertension_outcomes['regular_bp_monitoring_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['regular_bp_monitoring_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['regular_bp_monitoring_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif
					
					@if(isset($hypertension_outcomes['goal4_status']) && $hypertension_outcomes['goal4_status'] != "")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Regular Follow up with PCP.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$hypertension_outcomes['goal4_status'] ?? ""}}</td>
						</tr>
					
						@if(isset($hypertension_outcomes['regular_bp_monitoring_status']) && $hypertension_outcomes['regular_bp_monitoring_status'] != "Not Started")
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Patient will understand the importance of regular follow ups with PCP for BP monitoring as well as overall health assessment periodically.</b>
									<p>
									Regularly seeing your PCP to be evaluated for your overall health and being specifically assessed for Blood Pressure monitoring is important. Changes in lifestyle and diet as well as other contributing factors can cause Blood Pressure to be affected and your treatment may need to be adjusted based on that. Therefore, it is important to regularly follow up to be evaluated for Blood Pressure as well as other health issues.
									</p>
								</td>
								<td align="center">{{@$hypertension_outcomes['regular_pcp_folloup_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['regular_pcp_folloup_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['regular_pcp_folloup_status'] ?? ""}}</td>
							</tr>
						@endif
					@endif
				</tbody>
			</table>
		@endif
		<!-- Hypertension end -->

		<!-- Obesity start -->
		@if($chronic_disease['Obesity'] == "true")
		<table style="width:100%;" class="table table-border">
			<tbody>
				<tr>
					<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
						OBESITY
					</th>
				</tr>
				<tr style="border-right:1px solid black">
					<th  width="10%">
						Prognosis
					</th>
					<td  width="90%" style="border-right:1px solid black">
						{{@$obesity_outcomes['prognosis'] ?? ""}}
					</td>
				</tr>
				<tr style="border-right:1px solid black">
					<th  width="10%">
						Assessment
					</th>
					<td  width="90%" style="border-right:1px solid black;">
						{{@$obesity_outcomes['assessment'] ?? ""}}
					</td>
				</tr>
			</tbody>
		</table>

		<table style="width:100%;" class="table table-border">
			<tbody>
				<tr>
					<th colspan="9" class=" table-primary" style="width:65%; text-align:center;">
						Goals
					</th>
					<th colspan="1" class="table-primary">
						Start Date
					</th>
					<th colspan="1" class=" table-primary">
						End Date
					</th>
					<th colspan="1" class="table-primary">
						Status
					</th>
				</tr>

				{{-- GOAL 1 STARTS --}}
				@if(isset($obesity_outcomes['goal1_status']) && $obesity_outcomes['goal1_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Assessment of patient knowledge on Obesity, BMI and its effect on overall health.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$obesity_outcomes['goal1_status'] ?? ""}}</td>
					</tr>
				
					@if(isset($obesity_outcomes['bmi_awareness_status']) && $obesity_outcomes['bmi_awareness_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To gain education and awareness about BMI and current BMI range.</b>
								<p>
									Doctors use a special measure called "body mass index," or "BMI," to help understand a person's weight. Your weight and height are used to calculate your BMI. Based on this number, you fall into 1 of the following categories:
									<ul>
										<li>Underweight – BMI under 18.5</li>
										<li>Healthy weight – BMI between 18.5 and 24.9</li>
										<li>Overweight – BMI between 25 and 29.9</li>
										<li>Having obesity – BMI 30 or greater</li>
									</ul>
									<p>Your doctor or nurse will often want to calculate your BMI at your medical appointments. But it's important to remember that your weight and BMI are just 1 piece of your overall health. Someone with a lower BMI might not be healthy overall, and someone with a higher BMI can still be healthy.</p>
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['bmi_awareness_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['bmi_awareness_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['bmi_awareness_status'] ?? ""}}</td>
						</tr>
					@endif
						
					@if(isset($obesity_outcomes['weight_effect_status']) && $obesity_outcomes['weight_effect_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To understand how your weight affects your health.</b>
								<p>
									Having obesity increases the risks of many different health problems. It can also make it harder for you to move, breathe, and do other things that people who are at a healthy weight can do easily.
								</p>
								<p>
									People with obesity are more likely to get diabetes, heart disease, cancer, and lots of other health problems. People with obesity also live less time than people of normal weight. That's why it's important to try to keep your weight in a healthy range.
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['weight_effect_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weight_effect_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weight_effect_status'] ?? ""}}</td>
						</tr>
					@endif
						
					@if(isset($obesity_outcomes['maintain_healthy_weight_status']) && $obesity_outcomes['maintain_healthy_weight_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To understand the importance of maintaining a healthy weight.</b>
								<p>
									If you would like to lose weight, you can start by talking to your doctor or nurse. They can help you make a plan to lose weight in a healthy way. It can also help to work with a dietitian (food expert).
								</p>
								<p>
									In general, to lose weight, you have to eat fewer calories and move your body more.
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['maintain_healthy_weight_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['maintain_healthy_weight_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['maintain_healthy_weight_status'] ?? ""}}</td>
						</tr>
					@endif
						
					@if(isset($obesity_outcomes['advertised_diets_status']) && $obesity_outcomes['advertised_diets_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Understanding the effectiveness of different advertised diets.</b>
								<p>
									Studies have compared different diets such as the Atkins diet, the Zone diet, and the Weight Watchers diet. No specific diet is better than any other. Any diet that reduces the number of calories you eat can help you lose weight, as long as you stick with it. You should try to find an eating pattern that works for you. A dietitian can help you make healthy changes to your diet while making sure that you get the nutrients your body needs.
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['advertised_diets_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['advertised_diets_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['advertised_diets_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($obesity_outcomes['healthy_habits_status']) && $obesity_outcomes['healthy_habits_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Understanding the effectiveness of exercise and healthy habits.</b>
								<p>
									Physical activity works the same way. You can walk, dance, garden, or even just move your arms while sitting. Even gentle forms of exercise are good for your health. For weight loss, the important thing is to increase the number of calories you burn by moving more. And you have to keep doing the extra activity.
								</p>
								<p>
									If you go on a diet for a short time, or increase your activity for a while, you might lose weight. But you will regain the weight if you go back to your old habits. Weight loss is about changing your habits for the long term.
								</p>
								<p>
									The best way to start is to make small changes and stick with them. Then, little by little, you can add new changes that you also stick with.
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['healthy_habits_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['healthy_habits_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['healthy_habits_status'] ?? ""}}</td>
						</tr>	
					@endif
				@endif
				{{-- GOAL 1 ENDS --}}
				
				{{-- GOAL 2 STARTS --}}
				@if(isset($obesity_outcomes['goal2_status']) && $obesity_outcomes['goal2_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Assess knowledge on weight loss techniques and make a plan on working on weight loss with lifestyle changes and other measures.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$obesity_outcomes['goal2_status'] ?? ""}}</td>
					</tr>

					@if(isset($obesity_outcomes['weight_loss_program_status']) && $obesity_outcomes['weight_loss_program_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To educate patient on starting a weight loss program.</b>
								<p>
									It can really help to find a health care professional who has experience in helping people lose weight and make the lifestyle changes needed to keep the weight off. This could be a doctor, nurse, or other provider like a nurse practitioner or physician assistant. Developing a relationship with this person will help improve your chances of long-term success, as they can help you figure out the best plan for you, monitor your process, and provide advice and support along the way.
								</p>
								<p>
									Different approaches and plans work for different people, so it's important to try not to get discouraged and to keep trying until you find something that works for you.
								</p>
								<p>
									Be careful about misinformation online and weight loss clinics with questionable ethics. Nothing out there is magic. Losing weight takes hard work, and keeping it off requires a plan that is sustainable long-term.
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['weight_loss_program_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weight_loss_program_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weight_loss_program_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($obesity_outcomes['bmi_importance_status']) && $obesity_outcomes['bmi_importance_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Importance of BMI in Weight Loss Programs.</b>
								<p>
									The first step is to determine your starting point, which includes weighing yourself and measuring your waist circumference. The body mass index (BMI) is calculated from your height and weight.
									<ul>
										<li>A person with a BMI between 25 and 29.9 is considered overweight</li>
										<li>A person with a BMI of 30 or greater is considered to have obesity</li>
									</ul>
									The BMI measurement provides an estimate of a person's total body fat, which is why experts find it more useful for assessing cardiovascular risk than a person's weight alone. However, it's not a perfect measure because it does not factor in variability in body composition. While most professional medical societies continue to recommend using a person's BMI when assessing risk, an experienced health care provider will also consider other factors (including a person's overall health) when making recommendations for how to achieve and maintain a healthy weight.
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['bmi_importance_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['bmi_importance_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['bmi_importance_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($obesity_outcomes['waist_circumference_status']) && $obesity_outcomes['waist_circumference_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Importance of waist circumference in weight loss.</b>
								<p>
									In general, a waist circumference greater than 35 inches (88 cm) in females and 40 inches (102 cm) in males increases the risk of obesity-related complications, such as heart disease and diabetes. People with obesity and who have a larger waist size may need more aggressive weight loss treatment than others. Your health care provider can talk to you about your situation, how to set short- and long-term goals, and how to start working toward those goals.
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['waist_circumference_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['waist_circumference_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['waist_circumference_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($obesity_outcomes['treatment_type_status']) && $obesity_outcomes['treatment_type_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Different type of treatments to lose weight.</b>
								<p>
									Types of treatment — Based on your situation and medical history, your health care provider can help you determine what combination of weight loss treatments would work best for you. Treatments must include changes in lifestyle, physical activity, approach to eating, and, in some cases, weight loss medicines or a surgical procedure. Weight loss surgery, also called bariatric surgery, is reserved for people with obesity who have not had success with other approaches.
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['treatment_type_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['treatment_type_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['treatment_type_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($obesity_outcomes['weight_loss_status']) && $obesity_outcomes['weight_loss_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To understand the importance of setting weight loss goals.</b>
								<p>
									It is important to set a weight loss goal. Your first goal should be to avoid gaining more weight. Once you know your starting point, it is helpful to create milestones and health-related goals in order to start tracking your success.
								</p>
								<p>
									If you are overweight or have obesity, losing 5 percent of your body weight is a reasonable initial weight loss goal. In the longer term, losing more than 15 percent of your body weight and staying at this weight is an extremely good result. However, keep in mind that even losing 5 percent of your body weight leads to important health benefits, so although your ultimate weight loss goal may be greater, try not to get discouraged if you're not able to lose more than this initially.
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['weight_loss_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weight_loss_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weight_loss_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($obesity_outcomes['eating_triggers_status']) && $obesity_outcomes['eating_triggers_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To understand the importance of “triggers” for eating.</b>
								<p>
									You can change your eating habits by breaking the chain of events between the trigger for eating and the act of eating. There are many ways to do this. For instance, you can:
									<ul>
										<li>Use a smaller plate for meals</li>
										<li>Make a conscious effort to eat more slowly</li>
										<li>Add more colorful (non-white) foods to your meals</li>
										<li>Keep healthy snacks (like chopped raw vegetables, fruits, and nuts) around in case you get hungry between meals</li>
									</ul>
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['eating_triggers_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['eating_triggers_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['eating_triggers_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($obesity_outcomes['healthy_unhealthy_status']) && $obesity_outcomes['healthy_unhealthy_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Understand healthy and un-healthy food.</b>
								<p>
									The types of foods we eat on a regular basis are related to whether we gain or lose weight over time. Whole grains, fruits, vegetables, nuts, and yogurt are associated with maintaining a lower weight, while foods like French fries or chips, sugar-sweetened beverages, and red or processed meats are associated with weight gain. High fructose-containing beverages, trans fats, and highly processed foods are particularly harmful for health and maintaining a healthy weight.
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['healthy_unhealthy_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['healthy_unhealthy_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['healthy_unhealthy_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($obesity_outcomes['weightloss_factors_status']) && $obesity_outcomes['weightloss_factors_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Understand different factors when losing weight.</b>
								<p>
									A calorie is a unit of energy found in food. Your body needs calories to function. If you are trying to lose weight, the goal of any eating plan is to burn up more calories than you eat.
								</p>
								<p>
									How quickly you lose weight on a given calorie intake depends upon several factors, such as your age, sex, and starting weight. In general:
									<ul>
										<li>Older people have a slower metabolism than young people, so it takes longer for them to lose weight.</li>
										<li>Males lose more weight than females of similar height and weight when dieting. This is because they have more muscle mass, which uses more energy.</li>
										<li>People who are extremely overweight lose weight more quickly than those who are only mildly overweight.</li>
									</ul>
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['weightloss_factors_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weightloss_factors_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weightloss_factors_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($obesity_outcomes['calories_needed_status']) && $obesity_outcomes['calories_needed_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>How many calories do I need?</b>
								<p>
									The number of calories you need per day depends on your current (or target) weight, sex, and activity level. Your health care provider can help you figure out this number and how to modify your diet accordingly.
								</p>
								<p>
									In general, it is best to choose foods that contain enough protein, carbohydrates, essential fatty acids, and vitamins. Try to avoid or at least limit alcohol, sugar-sweetened beverages (sodas and fruit drinks), and sweets (candy, cakes, cookies), since they have calories but generally lack important nutrients.
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['calories_needed_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['calories_needed_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['calories_needed_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($obesity_outcomes['calories_count_status']) && $obesity_outcomes['calories_count_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Are meal replacement plans good to count calories?</b>
								<p>
									Portion-controlled diets — One simple way to diet is to buy pre-packaged foods, like frozen low-calorie meals or meal-replacement canned drinks or bars. A typical meal plan for one day may include:
									<ul>
										<li>A meal-replacement drink or breakfast bar for breakfast</li>
										<li>A meal-replacement drink or a frozen low-calorie (250 to 350 calories) meal for lunch</li>
										<li>A frozen low-calorie meal or other prepackaged, calorie-controlled meal, along with extra vegetables for dinner</li>
									</ul>
									This would give you 1000 to 1500 calories per day.
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['calories_count_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['calories_count_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['calories_count_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($obesity_outcomes['reduce_fat_status']) && $obesity_outcomes['reduce_fat_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>How to reduce fat in your diet?</b>
								<p>
									To reduce the amount of fat in your diet, you can:
									<ul>
										<li>Eat low-fat foods. You can look at the nutrition label to see how much fat is each serving of a food.</li>
										<li>Count fat grams. For a 1500-calorie diet, this would mean about 45 g or fewer of fat per day.</li>
									</ul>
									If you try a low-fat diet, you should increase the amount of healthy carbohydrates in your diet (e.g., whole grains, fruits, and vegetables).
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['reduce_fat_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['reduce_fat_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['reduce_fat_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($obesity_outcomes['reduce_carbs_status']) && $obesity_outcomes['reduce_carbs_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>How to reduce Carbohydrate in your diet?</b>
								<p>
									Low- and very-low-carbohydrate diets (e.g., Atkins diet, South Beach diet, or "ketogenic" diet) are effective for weight loss and have become popular ways to lose weight quickly.
									<ul>
										<li>With a low-carbohydrate diet, you eat between 60 and 130 grams of carbohydrates per day.</li>
										<li>With a very-low-carbohydrate diet, you eat between 0 and 60 grams of carbohydrates per day (a standard diet contains 200 to 300 grams of carbohydrates).</li>
									</ul>
									Carbohydrates are found in fruits, vegetables, grains (including breads, rice, pasta, and cereal), alcoholic beverages, and dairy products. Meat and fish contain very few carbohydrates. If you try a low carbohydrate diet, it's important to make healthy choices for fat and protein (e.g., fish, nuts, beans); eating a lot of saturated fats (found in butter and red meat) can increase your cholesterol level and raise your risk of heart disease.
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['reduce_carbs_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['reduce_carbs_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['reduce_carbs_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($obesity_outcomes['mediterranean_diet_status']) && $obesity_outcomes['mediterranean_diet_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To understand the importance of maintaining a healthy weight.</b>
								<p>
									The term "Mediterranean diet" refers to a way of eating that is common in olive-growing regions around the Mediterranean Sea. Although there is some variation in Mediterranean diets, there are some similarities. Most Mediterranean diets include:
									<ul>
										<li>A high level of monounsaturated fats (from olive or canola oil, walnuts, pecans, almonds) and a low level of saturated fats (from butter).</li>
										<li>A high number of vegetables, fruits, legumes, and grains (7 to 10 servings of fruits and vegetables per day).</li>
										<li>A moderate amount of milk and dairy products, mostly in the form of cheese. Use low-fat dairy products (skim milk, fat-free yogurt, low-fat cheese).</li>
										<li>A relatively low amount of red meat and meat products. Substitute fish or poultry for red meat.</li>
										<li>For those who drink alcohol, a modest amount (mainly as red wine) may help to protect against cardiovascular disease. A modest amount is up to one (4 ounce) glass per day for females and up to two glasses per day for males.</li>
									</ul>
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['mediterranean_diet_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['mediterranean_diet_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['mediterranean_diet_status'] ?? ""}}</td>
						</tr>
					@endif
				@endif
				{{-- GOAL 2 ENDS --}}
				
				{{-- GOAL 3 STARTS --}}
				@if(isset($obesity_outcomes['goal3_status']) && $obesity_outcomes['goal3_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Assess Knowledge on Weight loss medications and supplements.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$obesity_outcomes['goal3_status'] ?? ""}}</td>
					</tr>
				
					@if(isset($obesity_outcomes['weightloss_medication_status']) && $obesity_outcomes['weightloss_medication_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To educate on weight loss medications.</b>
								<p>Medication may be helpful for weight loss when used in combination with diet, exercise, and lifestyle changes. However, it is important to understand the risks, benefits, and limitations of these medicines. They can cause side effects that may be bothersome, and in many cases the long-term safety data are limited. In addition, these medicines may not be covered by insurance and can be expensive. Although weight loss medicines may not help you reach your "dream" weight, they can contribute to reducing your risk of diabetes or heart disease.</p>
								<p>
									Weight loss medicines may be recommended for people who have not been able to lose weight with diet and exercise who have a:
									<ul>
										<li>Body mass index (BMI) of 30 or more.</li>
										<li>BMI between 27 and 29.9 and have other medical problems, such as diabetes, high cholesterol, or high blood pressure</li>
									</ul>
								</p>
							</td>
							<td align="center">{{@$obesity_outcomes['weightloss_medication_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weightloss_medication_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weightloss_medication_status'] ?? ""}}</td>
						</tr>
					@endif	
					
					@if(isset($obesity_outcomes['dietary_supplements_status']) && $obesity_outcomes['dietary_supplements_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To educate patient on Dietary supplements.</b>
								<h5>DIETARY SUPPLEMENTS NOT RECOMMENDED</h5>
								<p>Dietary supplements are widely used by people who are trying to lose weight. However, doctors DO NOT recommend their use because some are unsafe, and other supplements have not been studied carefully and there is no proof that they are safe or effective.</p>
								<p>Many herbal weight loss medicines are unsafe or do not work. Check with your doctor or pharmacist before you take any herbal weight loss medicines. There is also an over-the-counter (non-prescription) version of a prescription medicine called orlistat (brand name: Alli). It is probably safe to try, but it can cause unwanted side effects, such as cramps, burping, and gas.</p>
								<p>Some weight loss medicines are sold over the internet. However, they can contain harmful ingredients and be unsafe.</p>
							</td>
							<td align="center">{{@$obesity_outcomes['dietary_supplements_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['dietary_supplements_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['dietary_supplements_status'] ?? ""}}</td>
						</tr>
					@endif	
				
					@if(isset($obesity_outcomes['weightloss_method_status']) && $obesity_outcomes['weightloss_method_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To educate on other weight loss methods.</b>
							<p>There are medicines and surgery to help with weight loss. But these treatments are only for people who have not been able to lose weight through diet and exercise.</p>
							<p>Weight loss treatments DO NOT take the place of diet and exercise. People who have those treatments must also change how they eat and how active they are.</p>
							</td>
							<td align="center">{{@$obesity_outcomes['weightloss_method_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weightloss_method_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weightloss_method_status'] ?? ""}}</td>
						</tr>
					@endif	
				
					@if(isset($obesity_outcomes['seeing_dietitian_status']) && $obesity_outcomes['seeing_dietitian_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To understand the importance of seeing a Dietitian..</b>
							<p>
								It can also help to work with a dietitian (food expert). They can help you make a diet plan that will be specifically designed for you and your requirements, a plan you can stick to and then you can follow up regularly with the dietitian to evaluate your progress.
							</p>
							</td>
							<td align="center">{{@$obesity_outcomes['seeing_dietitian_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['seeing_dietitian_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['seeing_dietitian_status'] ?? ""}}</td>
						</tr>
					@endif
				@endif
			</tbody>
		</table>
		@endif
		<!-- Obesity end -->

		<!-- CHF start -->
		@if($chronic_disease['CongestiveHeartFailure'] == "true")
		<table style="width:100%;" class="table table-border">
			<tbody>
				<tr>
					<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
						CHF
					</th>
				</tr>
				<tr>
					<th colspan="2" width="10%">
						Prognosis
					</th>
					<td colspan="10">
						{{@$chf_outcomes['prognosis'] ?? ""}}
					</td>
				</tr>
			</tbody>
		</table>

		<table style="width:100%;" class="table table-border">
			<tbody>
				<tr>
					<th colspan="9" class=" table-primary" style="width:65%; text-align:center;">
						Goals
					</th>
					<th colspan="1" class="table-primary">
						Start Date
					</th>
					<th colspan="1" class=" table-primary">
						End Date
					</th>
					<th colspan="1" class="table-primary">
						Status
					</th>
				</tr>

				@if(isset($chf_outcomes['goal1_status']) && $chf_outcomes['goal1_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>To acquire knowledge about congestive heart failure and how it can affect you</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$chf_outcomes['goal1_status'] ?? ""}}</td>
					</tr>

					@if(isset($chf_outcomes['understanding_regarding_disease_status']) && $chf_outcomes['understanding_regarding_disease_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							Assess the patient's current knowledge and understanding regarding disease
							</td>
							<td align="center">{{@$chf_outcomes['understanding_regarding_disease_start_date'] ?? ""}}</td>
							<td align="center">{{@$chf_outcomes['understanding_regarding_disease_end_date'] ?? ""}}</td>
							<td align="center">{{@$chf_outcomes['understanding_regarding_disease_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($chf_outcomes['monitor_blood_pressure_status']) && $chf_outcomes['monitor_blood_pressure_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							Monitor blood pressure levels of patients
							</td>
							<td align="center">{{@$chf_outcomes['monitor_blood_pressure_start_date'] ?? ""}}</td>
							<td align="center">{{@$chf_outcomes['monitor_blood_pressure_end_date'] ?? ""}}</td>
							<td align="center">{{@$chf_outcomes['monitor_blood_pressure_status'] ?? ""}}</td>
						</tr>
					@endif
					
					@if(isset($chf_outcomes['monitor_ECG_levels_status']) && $chf_outcomes['monitor_ECG_levels_status'] != "Not Started")
						<tr>
							<td colspan="9" style="width:65%;">
							Monitor ECG levels of patients
							</td>
							<td align="center">{{@$chf_outcomes['monitor_ECG_levels_start_date'] ?? ""}}</td>
							<td align="center">{{@$chf_outcomes['monitor_ECG_levels_end_date'] ?? ""}}</td>
							<td align="center">{{@$chf_outcomes['monitor_ECG_levels_status'] ?? ""}}</td>
						</tr>
					@endif
				@endif

				@if(isset($chf_outcomes['goal2_status']) && $chf_outcomes['goal2_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>To closely monitor the signs and symptoms to mitigate the chances or relapse.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$chf_outcomes['goal2_status'] ?? ""}}</td>
					</tr>

					@if(isset($chf_outcomes['adequate_cardiac_status']) && $chf_outcomes['adequate_cardiac_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						Patient will demonstrate adequate cardiac output as evidenced by vital signs within acceptable limits, dysrhythmias absent/controlled, and no symptoms of failure (e.g., hemodynamic parameters within acceptable limits, urinary output adequate)
						</td>
						<td align="center">{{@$chf_outcomes['adequate_cardiac_start_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['adequate_cardiac_end_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['adequate_cardiac_status'] ?? ""}}</td>
					</tr>
				@endif
					
				@if(isset($chf_outcomes['cerebral_hypoperfusion_status']) && $chf_outcomes['cerebral_hypoperfusion_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						MONITOR Symptoms like Cerebral hypoperfusion occurs because of hypoxia to the brain from the decreased cardiac output. The patient may report this as confusion, forgetfulness, restlessness. Through assessment is necessary to evaluate for possible related conditions, including psychologic disorders. Depression is common among patients with heart failure and can lead to poor adherence to treatment plans.
						</td>
						<td align="center">{{@$chf_outcomes['cerebral_hypoperfusion_start_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['cerebral_hypoperfusion_end_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['cerebral_hypoperfusion_status'] ?? ""}}</td>
					</tr>
				@endif
				@endif

				@if(isset($chf_outcomes['goal3_status']) && $chf_outcomes['goal3_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>To assess the signs of respiratory distress</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$chf_outcomes['goal3_status'] ?? ""}}</td>
					</tr>

					@if(isset($chf_outcomes['pulmonary_hygiene_status']) && $chf_outcomes['pulmonary_hygiene_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Give awareness to patient regarding pulmonary hygiene as needed</b>
						Pulmonary hygiene, refers to exercises and procedures that help to clear your airways of mucus and other secretions. This ensures that your lungs get enough oxygen, and your respiratory system works efficiently. There are several pulmonary hygiene methods and approaches. Some can be done on your own at home, while others require a visit to your healthcare provider like breathing exercise, relaxed breathing, Huffing=This exercise requires you to “huff” by breathing hard out of your mouth, as though you were creating fog on a mirror. Spirometry, This method of strengthening and controlling your breathing uses a device called an incentive spirometer.
						</td>
						<td align="center">{{@$chf_outcomes['pulmonary_hygiene_start_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['pulmonary_hygiene_end_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['pulmonary_hygiene_status'] ?? ""}}</td>
					</tr>
				@endif
					
				@if(isset($chf_outcomes['respiratory_distress_status']) && $chf_outcomes['respiratory_distress_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Keep the head of the bed elevated in case of respiratory distress</b>
						Head-end elevation is known to improve oxygenation and respiratory mechanics. In poor lung compliance limits positive pressure ventilation causing delivery of inadequate minute ventilation (MVe). We observed that, in moderate-to-severe cases, the respiratory system compliance reduces upon elevating the head-end of the bed, and vice-versa, which can be utilized to improve ventilation and avoid respiratory distress.
						</td>
						<td align="center">{{@$chf_outcomes['respiratory_distress_start_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['respiratory_distress_end_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['respiratory_distress_status'] ?? ""}}</td>
					</tr>
				@endif
				
				@if(isset($chf_outcomes['monitor_ABG_levels_status']) && $chf_outcomes['monitor_ABG_levels_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Monitor ABG levels of patients</b>
						Your lungs and your kidneys do much of the work to keep your acid-base balance normal. So, the acid-base measurement from an ABG test can help diagnose and monitor conditions that affect your lungs and kidneys as well as many other conditions that may upset your acid-base balance.
						</td>
						<td align="center">{{@$chf_outcomes['monitor_ABG_levels_start_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['monitor_ABG_levels_end_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['monitor_ABG_levels_status'] ?? ""}}</td>
					</tr>
				@endif
				@endif

				@if(isset($chf_outcomes['goal4_status']) && $chf_outcomes['goal4_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>To understand the importance of Monitoring signs of altered cardiac output, including</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$chf_outcomes['goal4_status'] ?? ""}}</td>
					</tr>

					@if(isset($chf_outcomes['pulmonary_edemas_status']) && $chf_outcomes['pulmonary_edemas_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Monitoring of Pulmonary edemas</b>
						Pulmonary edema is a condition caused by too much fluid in the lungs. This fluid collects in the many air sacs in the lungs, making it difficult to breathe. It needs to be monitor by feeling any difficulty in respiration.
						</td>
						<td align="center">{{@$chf_outcomes['pulmonary_edemas_start_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['pulmonary_edemas_end_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['pulmonary_edemas_status'] ?? ""}}</td>
					</tr>
				@endif
					
				@if(isset($chf_outcomes['conditions_of_Arrhythmias_status']) && $chf_outcomes['conditions_of_Arrhythmias_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Assess conditions of Arrhythmias, including extreme tachycardia and bradycardia</b>
						</td>
						<td align="center">{{@$chf_outcomes['conditions_of_Arrhythmias_start_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['conditions_of_Arrhythmias_end_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['conditions_of_Arrhythmias_status'] ?? ""}}</td>
					</tr>
				@endif
				
				@if(isset($chf_outcomes['cardiologist_visit_status']) && $chf_outcomes['cardiologist_visit_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Check ECG and heart sound changes in every cardiologist visit.</b>
						The electrocardiogram (ECG) at rest is a non-invasive investigation that is recommended in the initial evaluation of patients with heart failure (HF). This is because the ECG is crucial in the detection of many abnormalities that may either cause or worsen HF. Therefore it is important to evaluate any changes in your heart sound by ECG (ELECTRO CARDIO GRAM).
						</td>
						<td align="center">{{@$chf_outcomes['cardiologist_visit_start_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['cardiologist_visit_end_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['cardiologist_visit_status'] ?? ""}}</td>
					</tr>
				@endif
				@endif

				@if(isset($chf_outcomes['goal5_status']) && $chf_outcomes['goal5_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Demonstrate stabilized fluid volume with balanced intake and output, breath sounds clear/clearing, vital signs within acceptable range, stable weight, and absence of edema.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$chf_outcomes['goal5_status'] ?? ""}}</td>
					</tr>
			
					@if(isset($chf_outcomes['fluid_status_status']) && $chf_outcomes['fluid_status_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						Evaluate fluid status by Monitor daily weights Assess for edema and severe diaphoresis Monitor electrolyte values and hematocrit level Verbalize understanding of individual dietary/fluid restrictions.
						</td>
						<td align="center">{{@$chf_outcomes['fluid_status_start_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['fluid_status_end_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['fluid_status_status'] ?? ""}}</td>
					</tr>
					@endif
				@endif

				@if(isset($chf_outcomes['goal6_status']) && $chf_outcomes['goal6_status'] != "")
					<tr>
						<td colspan="9" style="width:65%;">
						<b>Identify relationship of ongoing therapies (treatment program) to reduction of recurrent episodes and prevention of complications.</b>
						</td>
						<td></td>
						<td></td>
						<td align="center">{{@$chf_outcomes['goal6_status'] ?? ""}}</td>
					</tr>

					@if(isset($chf_outcomes['antiarrhythmias_status']) && $chf_outcomes['antiarrhythmias_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						Antiarrhythmias to increase cardiac performance Diuretics, to reduce venous and systemic congestion Iron and folic acid supplements to improve nutritional status Angiotensin-converting enzyme (ACE) inhibitors.These drugs relax blood vessels to lower blood pressure, improve blood flow and decrease the strain on the heart. Beta blockers.These drugs slow your heart rate and reduce blood pressure. Beta blockers may reduce signs and symptoms of heart failure, improve heart function. Digoxin (Lanoxin).This drug, also called digitalis, increases the strength of your heart muscle contractions
						</td>
						<td align="center">{{@$chf_outcomes['antiarrhythmias_start_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['antiarrhythmias_end_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['antiarrhythmias_status'] ?? ""}}</td>
					</tr>
					@endif
				@endif



				@if(isset($chf_outcomes['followup_pcp_status']) && $chf_outcomes['followup_pcp_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						To understand the importance of regular follow-up with PCP and cardiologist.
						</td>
						<td align="center">{{@$chf_outcomes['followup_pcp_start_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['followup_pcp_end_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['followup_pcp_status'] ?? ""}}</td>
					</tr>
				@endif
				
				@if(isset($chf_outcomes['importance_medication_status']) && $chf_outcomes['importance_medication_status'] != "Not Started")
					<tr>
						<td colspan="9" style="width:65%;">
						To recognize the importance of discipline in taking all medications as prescribed.
						</td>
						<td align="center">{{@$chf_outcomes['importance_medication_start_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['importance_medication_end_date'] ?? ""}}</td>
						<td align="center">{{@$chf_outcomes['importance_medication_status'] ?? ""}}</td>
					</tr>
				@endif


			</tbody>
		</table>
		@endif
		<!-- CHF end -->
	</div>

	<script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
	<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>