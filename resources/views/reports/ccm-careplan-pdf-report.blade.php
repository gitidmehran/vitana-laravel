<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="{{ asset('css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
	<!-- <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}"> -->
	<link rel="stylesheet" href="{{ asset('css/bootstrap3.min.css') }}">
	<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"> -->
    
	

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
</head>
	@php
		$next_assessment = \Carbon\Carbon::now()->addYear(1)->startOfYear()->format('m/d/Y');
		$dateofBirth = \Carbon\Carbon::parse($row['patient']['dob'])->format('m/d/Y');
		$row['date_of_service'] = \Carbon\Carbon::parse($row['date_of_service'])->format('m/d/Y');
		$currentYear = \Carbon\Carbon::now()->year;
	@endphp

	<body>

		<div size="A4" class="radial-repeating">
			<div class="center" >
				<p>
					CCM {{$currentYear}}
				</p>
				<p>
					{{@$row['patient']['name']}} This is your Annual CCM Care Plan
				</p>
				<p>
					<b> Encounter Date: {{@$row['date_of_service']}}</b>
				</p>
			</div>
		</div>
	<div style="padding: 20px; border: 1px solid #b8daff; ">
		<h4 class="main-heading ">CCM - Annual Care Plan</h4>
		<table style="border: none;">
			<tr style="border: none;">
				<th width="10%" style="font-size: 12px; border: none; padding-left:10px text-align: left; ">Name: <span style="font-size: 12px; font-weight: normal;">{{@$row['patient']['name']}}</span></th>
				<th width="16%" style="font-size: 12px; border: none; padding-left:10px text-align: left;">Date of Birth: <span style="font-size: 12px; font-weight: normal;">{{$dateofBirth}}</span></th>
				<th width="8%" style="font-size: 12px; border: none; padding-left:10px text-align: left;">Age: <span style="font-size: 12px; font-weight: normal;">{{$row['patient']['age']}}</span></th>
				<th width="10%" style="font-size: 12px; border: none; padding-left:10px text-align: left;">Gender: <span style="font-size: 12px; font-weight: normal;">{{$row['patient']['gender']}}</span></th>
			</tr>
		<tr style="border: none;">
				<th width="25%" style="font-size: 12px; border: none; padding-left:10px text-align: left;">Program: <span style="font-size: 12px; font-weight: normal;">{{$row['program']['name']}} ({{$row['program']['short_name']}})</span></th>
				<th width="10%" style="font-size: 12px; border: none; padding-left:10px text-align: left;">Primary care Physician: <span style="font-size: 12px; font-weight: normal;">{{@$row['primary_care_physician'] ?? ''}}</span></th>
			</tr>
			<tr style="border: none;">
				<th width="10%" style="font-size: 12px; border: none; padding-left:10px">Date of Service: <span style="font-size: 12px; font-weight: normal;">{{$row['date_of_service']}}</span></th>
				<th width="10%" style="font-size: 12px; border: none; padding-left:10px">Next Due: <span style="font-size: 12px; font-weight: normal;">{{$next_assessment}}</span></th>
			</tr>
		</table>
	</div>

		<div style="margin-top:50px;">

			<!-- Annual Assessment Sections Starts -->
			<table class="table" style="table-layout: fixed">
				<tbody>

					<!-- Physical Health Assessment -->
					<tr>
						<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
						Physical Health
						</th>
					</tr>
					<tr>
						<th scope="row" colspan="3">Physical Health - Fall Screening</th>
						<td colspan="9">
							@foreach ($fall_screening as $key => $val)
							@if ($val != "") {{$val}} <br /> @endif
							@endforeach
						</td>
					</tr>
					<!-- PHYSICAL HEALTH ENDS -->

					<!-- Cognitive Assessment -->
					<tr>
						<th class="text-center" colspan="12" style="text-align: center; background: #b8daff; color: #23468c; column-span: all"> Cognitive Assessment </th>
					</tr>
					<tr>
						<th scope="row" colspan="3">Cognitive Assessment</th>
						<td colspan="9">{{@$cognitive_assessment['outcome'] ?? ''}}</td>
						
					</tr>
					<!-- Congnitive Assessment ENDS -->

					<!-- Caregiver Assessment -->
					<tr>
                        <th class="text-center" colspan="12" style="text-align: center; background: #b8daff; color: #23468c; column-span: all"> Caregiver Assessment </th>
                    </tr>
					<tr>
						<th scope="row" colspan="3">Caregiver Assessment</th>
						<td colspan="9">{{@$caregiver_assesment_outcomes['every_day_activities'] ?? ''}}</td>
					</tr>
					<tr>
						<th scope="row" colspan="3"></th>
						<td colspan="9">{{@$caregiver_assesment_outcomes['medications'] ?? ''}}</td>
					</tr>
					<!-- Caregiven Assessment ENDS -->

					<!-- Immunization Section -->
					<tr>
                        <th class="text-center" colspan="12" style="text-align: center; background: #b8daff; color: #23468c; column-span: all"> Immunization </th>
                    </tr>
					<tr>
						<th scope="row" colspan="3">Immunization</th>
						<td colspan="9">
							{!! !empty($immunization['flu_vaccine']) ? $immunization['flu_vaccine'].'<br>' : "" !!}
							{!! !empty($immunization['flu_vaccine_script']) ? $immunization['flu_vaccine_script'].'<br>' : "" !!}
							{!! !empty($immunization['pneumococcal_vaccine']) ? $immunization['pneumococcal_vaccine'].'<br>' : "" !!}
							{!! !empty($immunization['pneumococcal_vaccine_script']) ? $immunization['pneumococcal_vaccine_script'].'<br>' : "" !!}
						</td>
					</tr>
					<!-- Immunizarion SECTIONS ENDS -->


					<!-- Screening Section -->
					<tr>
						<th class="text-center" colspan="12" style="text-align: center; background: #b8daff; color: #23468c; column-span: all"> Screening </th>
					</tr>
					<tr>
						<th scope="row" colspan="3">Mammogram</th>
						<td colspan="9">
							{!! !empty($screening['mammogram']) ? $screening['mammogram'].'<br>' : "" !!}
							{!! !empty($screening['next_mammogram']) ? $screening['next_mammogram'].'<br>' : "" !!}
							{!! !empty($screening['mammogram_script']) ? $screening['mammogram_script'].'<br>' : "" !!}
						</td>
					</tr>
					<tr>
						<th scope="row" colspan="3">Colon Cancer</th>
						<td colspan="9">
							{!! !empty($screening['colonoscopy']) ? $screening['colonoscopy'].'<br>' : "" !!}
							{!! !empty($screening['next_colonoscopy']) ? $screening['next_colonoscopy'].'<br>' : "" !!}
							{!! !empty($screening['colonoscopy_script']) ? $screening['colonoscopy_script'].'<br>' : "" !!}
						</td>
					</tr>
					<!-- Screening Section END -->

					<!--  -->
				</tbody>
			</table>
            <!-- Annual Assessment sections ends -->


			<!-- General Assessment Section -->
			<table style="width:100%;" class="table table-border">
				<tbody>
					<tr>
						<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
							GENERAL ASSESSMENT
						</th>
					</tr>

					<tr>
						<th colspan="2" width="10%">Medication Reconciliation</th>
						<td colspan="10">
							{{@$general_assessment_outcomes['is_taking_medication'] ?? ''}}
						</td>
					</tr>

					<tr>
						<th colspan="2" width="10%">Tobacco Usage</th>
						<td colspan="10">
							{{@$general_assessment_outcomes['is_consuming_tobacco'] ?? ''}}
						</td>
					</tr>

					<tr>
						<th colspan="2" width="10%">Physical Exercise</th>
						<td colspan="10">
						    {{@$general_assessment_outcomes['physical_exercise_level'] ?? ''}}
						</td>
					</tr>
				</tbody>
			</table>

			<!-- GOALS -->
			<table class="table" style="table-layout: fixed ; margin-top:0;padding-top:0;">
				<tbody>
							
					<th colspan="6" style="text-align: center; background: #b8daff; color: #23468c;" >
						<span style="display:inline;">General Assessment Goals</span>
					</th>
					<th colspan="2" style="text-align: center; background: #b8daff; color: #23468c;" >
						<span style="display:inline; ">Start Date</span>
					</th>
					<th colspan="2" style="text-align: center; background: #b8daff; color: #23468c;" >
						<span style="display:inline; ">End Date</span>
					</th>
					<th colspan="2" style="text-align: center; background: #b8daff; color: #23468c;" >
						<span style="display:inline; ">Status</span>
					</th>

					<tr>
						<td colspan="6">Instructed on Importance of Hand Washing</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['imp_handwash_start_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['imp_handwash_end_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['imp_handwash_status'] ?? ''}}</td>
                    </tr>

					<tr>
						<td colspan="6">Instructed on how washing with Soap remove germs</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['washwithsoap_start_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['washwithsoap_end_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['washwithsoap_status'] ?? ''}}</td>
                    </tr>
                    
                    <tr>
						<td colspan="6">Instructed on proper way to turn off the faucet</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['turnoff_faucet_start_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['turnoff_faucet_end_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['turnoff_faucet_status'] ?? ''}}</td>
                    </tr>

					<tr>
						<td colspan="6">Patient shows understanding on proper way to turn off the faucet</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['understand_faucet_start_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['understand_faucet_end_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['understand_faucet_status'] ?? ''}}</td>
                    </tr>

					<tr>
						<td colspan="6">Patient shows understanding of using plain Soap</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['plain_soap_usage_start_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['plain_soap_usage_end_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['plain_soap_usage_status'] ?? ''}}</td>
                    </tr>
                    
					<tr>
						<td colspan="6">Is Bar Soap or Liquid Soap better?</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['bar_or_liquid_start_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['bar_or_liquid_end_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['bar_or_liquid_status'] ?? ''}}</td>
                    </tr>

					<tr>
						<td colspan="6">Patient shows understanding about importance of plain soap in any form</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['uips_start_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['uips_end_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['uips_status'] ?? ''}}</td>
                    </tr>

					<tr>
						<td colspan="6">What if there is no Soap?</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['no_soap_condition_start_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['no_soap_condition_end_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['no_soap_condition_status'] ?? ''}}</td>
                    </tr>

					<tr>
						<td colspan="6">Patient shows understanding about Hand Sanitizer</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['understand_hand_sanitizer_start_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['understand_hand_sanitizer_end_date'] ?? ''}}</td>
						<td colspan="2" class='text-center'>{{@$general_assessment_outcomes['understand_hand_sanitizer_status'] ?? ''}}</td>
                    </tr>

                </tbody>
            </table>
			<!-- General Assessment Section ENDS -->

			<!-- Mental Health Assessment -->
			@if ($chronic_disease["ChronicObstructivePulmonaryDisease"])
				<table style="width:100%;" class="table table-border">
					<tbody>
						<tr>
							<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
								Depression
							</th>
						</tr>
					</tbody>
				</table>

				<!-- GOALS -->
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
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To acquire knowledge about depression and how it can affect you.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$depression_out_comes['goal1_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>
									Assess the patient’s current knowledge and understanding regarding disease
								</p>
							</td>
							<td align="center">{{@$depression_out_comes['understand_about_disease_start_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['understand_about_disease_end_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['understand_about_disease_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>
									Monitor PHQ-9 levels of patients
								</p>
							</td>
							<td align="center">{{@$depression_out_comes['monitor_phq9_start_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['monitor_phq9_end_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['monitor_phq9_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>
									ADVANTAGES OF THE PHQ-9
								</p>
							</td>
							<td align="center">{{@$depression_out_comes['advantages_of_phq9_start_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['advantages_of_phq9_end_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['advantages_of_phq9_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 1 ENDS --}}

						{{-- GOAL 2 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To understand the effect of depression on overall health.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$depression_out_comes['goal2_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>
									Understanding depression relationship with other medical problems
								</p>
							</td>
							<td align="center">{{@$depression_out_comes['understand_about_disease_start_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['understand_about_disease_end_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['understand_about_disease_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 2 ENDS --}}

						{{-- GOAL 3 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To understand the importance of different approaches that are used to treat depression.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$depression_out_comes['goal3_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>
									Understanding Counseling (with a psychiatrist, psychologist, nurse, or social worker) & medicines that relieve depression
								</p>
							</td>
							<td align="center">{{@$depression_out_comes['relieve_depression_start_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['relieve_depression_end_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['relieve_depression_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>
									Understanding CBT
								</p>
							</td>
							<td align="center">{{@$depression_out_comes['understand_cbt_start_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['understand_cbt_end_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['understand_cbt_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>
									Importance of Physical activity
								</p>
							</td>
							<td align="center">{{@$depression_out_comes['physical_activity_importance_start_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['physical_activity_importance_end_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['physical_activity_importance_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>
									Understanding treatments that pass magnetic waves or electricity into the brain
								</p>
							</td>
							<td align="center">{{@$depression_out_comes['waves_treatment_start_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['waves_treatment_end_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['waves_treatment_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 3 ENDS --}}

						{{-- GOAL 4 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To understand the importance of changes to your habits and lifestyle to treat depression.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$depression_out_comes['goal4_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>
									Exercise a specific number of days per week
								</p>
							</td>
							<td align="center">{{@$depression_out_comes['exercise_start_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['exercise_end_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['exercise_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 4 ENDS --}}

						{{-- GOAL 5 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To understand the importance of regular follow-ups</b>
							</td>
							<td align="center">{{@$depression_out_comes['regular_follow_ups_start_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['regular_follow_ups_end_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['goal5_status'] ?? ""}}</td>
						</tr>

						{{-- <tr>
							<td colspan="9" style="width:65%;">
								<p></p>
							</td>
							<td align="center"></td>
							<td align="center"></td>
							<td align="center">{{@$depression_out_comes['regular_follow_ups_status'] ?? ""}}</td>
						</tr> --}}
						{{-- GOAL 5 ENDS --}}

						{{-- GOAL 6 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To understand what to do if you are having thoughts of harming yourself.</b>
							</td>
							<td align="center">{{@$depression_out_comes['helping_guides_start_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['helping_guides_end_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['goal6_status'] ?? ""}}</td>
						</tr>

						{{-- <tr>
							<td colspan="9" style="width:65%;">
								<p></p>
							</td>
							<td align="center"></td>
							<td align="center"></td>
							<td align="center">{{@$depression_out_comes['helping_guides_status'] ?? ""}}</td>
						</tr> --}}
						{{-- GOAL 6 ENDS --}}

						{{-- GOAL 7 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To utilize counseling/group support</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$depression_out_comes['goal7_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>To improve your relationships with other people can help to lower your risk of being affected by depression.</p>
							</td>
							<td align="center">{{@$depression_out_comes['improve_relations_start_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['improve_relations_end_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['improve_relations_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>To take part in therapy on a regular basis not only lets you receive the mental health benefits of psychotherapy, but it can also help create a routine in your life.</p>
							</td>
							<td align="center">{{@$depression_out_comes['psychotherapy_start_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['psychotherapy_end_date'] ?? ""}}</td>
							<td align="center">{{@$depression_out_comes['psychotherapy_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 7 ENDS --}}
					</tbody>
				</table>
			@endif
			<!-- Mental Health ENDS -->

			<!-- Obesity Start -->
			@if ($chronic_disease["Obesity"])
				<table style="width:100%;" class="table table-border">
					<tbody>
						<tr>
							<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
								OBESITY
							</th>
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

						<tr>
							<td colspan="9" style="width:65%;">
							<b>Assessment of patient knowledge on Obesity, BMI and its effect on overall health.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$obesity_outcomes['goal1_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To gain education and awareness about BMI and current BMI range.</p>
							</td>
							<td align="center">{{@$obesity_outcomes['bmi_awareness_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['bmi_awareness_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['bmi_awareness_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>To understand how your weight affects your health.</p>
							</td>
							<td align="center">{{@$obesity_outcomes['weight_effect_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weight_effect_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weight_effect_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>To understand the importance of maintaining a healthy weight.</p>
							</td>
							<td align="center">{{@$obesity_outcomes['maintain_healthy_weight_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['maintain_healthy_weight_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['maintain_healthy_weight_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>Understanding the effectiveness of different advertised diets.</p>
							</td>
							<td align="center">{{@$obesity_outcomes['advertised_diets_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['advertised_diets_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['advertised_diets_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>Understanding the effectiveness of exercise and healthy habits.</p>
							</td>
							<td align="center">{{@$obesity_outcomes['healthy_habits_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['healthy_habits_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['healthy_habits_status'] ?? ""}}</td>
						</tr>

						{{-- Goal 2 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
							<b>Assess knowledge on weight loss techniques and make a plan on working on weight loss with lifestyle changes and other measures.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$obesity_outcomes['goal2_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>To educate patient on starting a weight loss program.</p>
							</td>
							<td align="center">{{@$obesity_outcomes['weight_loss_program_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weight_loss_program_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weight_loss_program_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<b>Importance of BMI in Weight Loss Programs.</b>
							</td>
							<td align="center">{{@$obesity_outcomes['bmi_importance_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['bmi_importance_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['bmi_importance_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<b>Importance of waist circumference in weight loss.</b>
							</td>
							<td align="center">{{@$obesity_outcomes['waist_circumference_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['waist_circumference_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['waist_circumference_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<b>Different type of treatments to lose weight.</b>
							</td>
							<td align="center">{{@$obesity_outcomes['treatment_type_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['treatment_type_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['treatment_type_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<b>To understand the importance of setting weight loss goals.</b>
							</td>
							<td align="center">{{@$obesity_outcomes['weight_loss_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weight_loss_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weight_loss_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<b>To understand the importance of “triggers” for eating.</b>
							</td>
							<td align="center">{{@$obesity_outcomes['eating_triggers_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['eating_triggers_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['eating_triggers_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<b>Understand healthy and un-healthy food.</b>
							</td>
							<td align="center">{{@$obesity_outcomes['healthy_unhealthy_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['healthy_unhealthy_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['healthy_unhealthy_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<b>Understand different factors when losing weight.</b>
							</td>
							<td align="center">{{@$obesity_outcomes['weightloss_factors_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weightloss_factors_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weightloss_factors_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<b>How many calories do I need?</b>
							</td>
							<td align="center">{{@$obesity_outcomes['calories_needed_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['calories_needed_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['calories_needed_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<b>Are meal replacement plans good to count calories?</b>
							</td>
							<td align="center">{{@$obesity_outcomes['calories_count_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['calories_count_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['calories_count_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<b>How to reduce fat in your diet?</b>
							</td>
							<td align="center">{{@$obesity_outcomes['reduce_fat_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['reduce_fat_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['reduce_fat_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<b>How to reduce Carbohydrate in your diet?</b>
							</td>
							<td align="center">{{@$obesity_outcomes['reduce_carbs_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['reduce_carbs_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['reduce_carbs_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<b>What is a Mediterranean diet?</b>
							</td>
							<td align="center">{{@$obesity_outcomes['mediterranean_diet_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['mediterranean_diet_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['mediterranean_diet_status'] ?? ""}}</td>
						</tr>
						{{-- Goal 2 ENDS --}}
						
						{{-- GOAL 3 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To understand the importance of healthy eating habits</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$obesity_outcomes['goal3_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To educate on weight loss medications.</p>
							</td>
							<td align="center">{{@$obesity_outcomes['weightloss_medication_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weightloss_medication_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weightloss_medication_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To educate patient on Dietary supplements.</p>
							</td>
							<td align="center">{{@$obesity_outcomes['dietary_supplements_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['dietary_supplements_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['dietary_supplements_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To educate on other weight loss methods.</p>
							</td>
							<td align="center">{{@$obesity_outcomes['weightloss_method_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weightloss_method_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['weightloss_method_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To understand the importance of seeing a Dietitian.</p>
							</td>
							<td align="center">{{@$obesity_outcomes['seeing_dietitian_start_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['seeing_dietitian_end_date'] ?? ""}}</td>
							<td align="center">{{@$obesity_outcomes['seeing_dietitian_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 3 ENDS --}}
					</tbody>
				</table>
			@endif
			<!-- Obesity ends -->


			<!-- COPD Start -->
			@if ($chronic_disease["ChronicObstructivePulmonaryDisease"])
				<table style="width:100%;" class="table table-border">
					<tbody>
						<tr>
							<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
								COPD
							</th>
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

						<tr>
							<td colspan="9" style="width:65%;">
								<p>
									To educate the patient of symptoms and complications of COPD.
								</p>
							<td align="center">{{@$copd_outcomes['educate_on_disease_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['educate_on_disease_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['educate_on_disease_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 1 ENDS --}}


						{{-- GOAL 2 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b> Smoking Cessation. </b>
							</td>
							<td align="center"></td>
							<td align="center"></td>
							<td align="center">{{@$copd_outcomes['goal2_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
								<p>To educate patient on the importance of smoking cessation (if applicable) for better COPD management.</p>
							<td align="center">{{@$copd_outcomes['smoking_cessation_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['smoking_cessation_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['smoking_cessation_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 2 ENDS --}}


						{{-- GOAL 3 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Lowering Risk of Infection.</b>
							</td>
							<td align="center"></td>
							<td align="center"></td>
							<td align="center">{{@$copd_outcomes['goal3_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
								<p>
									To educate the patient on lowering the risk of infections.
								</p>
							<td align="center">{{@$copd_outcomes['lowering_infection_risk_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['lowering_infection_risk_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['lowering_infection_risk_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 3 ENDS --}}


						{{-- GOAL 4 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b> Lifestyle changes that can help with COPD. </b>
							</td>
							<td align="center"></td>
							<td align="center"></td>
							<td align="center">{{@$copd_outcomes['goal4_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
								<p>To educate the patient on lifestyle changes.</p>
							<td align="center">{{@$copd_outcomes['educate_on_lifestyle_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['educate_on_lifestyle_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['educate_on_lifestyle_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 4 ENDS --}}


						{{-- GOAL 5 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									Lifestyle changes that can help with COPD.
								</b>
							</td>
							<td align="center"></td>
							<td align="center"></td>
							<td align="center">{{@$copd_outcomes['goal5_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
								<p> To educate the patient on lifestyle changes. </p>
							<td align="center">{{@$copd_outcomes['educate_on_emergency_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['educate_on_emergency_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['educate_on_emergency_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 5 ENDS --}}


						{{-- GOAL 6 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b> Know when you are having a COPD flare. </b>
							</td>
							<td align="center"></td>
							<td align="center"></td>
							<td align="center">{{@$copd_outcomes['goal6_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
								<p> To educate patients on COPD flare. </p>
							<td align="center">{{@$copd_outcomes['having_copd_flare_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['having_copd_flare_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['having_copd_flare_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 6 ENDS --}}


						{{-- GOAL 7 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b> Prevention of COPD flare. </b>
							</td>
							<td align="center"></td>
							<td align="center"></td>
							<td align="center">{{@$copd_outcomes['goal7_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
								<p>
									To educate the patient on the prevention of COPD flare.
								</p>
							<td align="center">{{@$copd_outcomes['prevention_copd_flare_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['prevention_copd_flare_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['prevention_copd_flare_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 7 ENDS --}}


						{{-- GOAL 8 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>
									Understand the importance of treatment adherence and regular follow-ups.
								</b>
							</td>
							<td align="center"></td>
							<td align="center"></td>
							<td align="center">{{@$copd_outcomes['goal8_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
								<p>
									To educate the patient on the importance of treatment adherence and regular follow-ups with PCP and Pulmonologist.
								</p>
							<td align="center">{{@$copd_outcomes['followup_imp_start_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['followup_imp_end_date'] ?? ""}}</td>
							<td align="center">{{@$copd_outcomes['followup_imp_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 8 ENDS --}}
					</tbody>
				</table>
			@endif
			<!-- COPD End -->

			<!-- CKD start -->
			@if ($chronic_disease["CKD"])
				<table style="width:100%;" class="table table-border">
					<tbody>
						<tr>
							<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
								CKD
							</th>
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
						<tr>
							<td colspan="9" style="width:65%;">
								<b> Assess patient knowledge on CKD and its complications and educate on steps to prevent worsening of renal function. </b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$ckd_outcomes['goal1_status'] ?? ""}}</td>
						</tr>
						{{-- TASKS --}}
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To educate patient on CKD.</p>
							</td>
							<td align="center">{{@$ckd_outcomes['chronic_kidney_failure_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['chronic_kidney_failure_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['chronic_kidney_failure_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To educate patient on symptoms of worsening CKD.</p>
							</td>
							<td align="center">{{@$ckd_outcomes['educate_on_ckd_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['educate_on_ckd_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['educate_on_ckd_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p>understand the importance of follow up with PCP and nephrologist if needed for management of CKD.</p>
							</td>
							<td align="center">{{@$ckd_outcomes['followup_importance_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['followup_importance_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['followup_importance_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To understand what the patient can do to prevent worsening of kidney function.</p>
							</td>
							<td align="center">{{@$ckd_outcomes['prevent_worsening_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['prevent_worsening_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['prevent_worsening_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To learn about the medication that you should avoid.</p>
							</td>
							<td align="center">{{@$ckd_outcomes['aviod_medications_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['aviod_medications_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['aviod_medications_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To understand how CKD is treated and importance of treatment compliance.</p>
							</td>
							<td align="center">{{@$ckd_outcomes['ckd_treatment_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['ckd_treatment_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['ckd_treatment_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 1 ENDS --}}

						{{-- GOAL 2 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Assess patient knowledge on risk factors of CKD and ways to prevent developing CKD.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$ckd_outcomes['goal2_status'] ?? ""}}</td>
						</tr>

						{{-- TASKS --}}
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To educate on factors that can increase risk of developing CKD.</p>
							</td>
							<td align="center">{{@$ckd_outcomes['educate_on_risk_factors_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['educate_on_risk_factors_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['educate_on_risk_factors_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To educate patient on lowering the risk of CKD development and rate of CKD progression.</p>
							</td>
							<td align="center">{{@$ckd_outcomes['educate_on_lowering_risk_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['educate_on_lowering_risk_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['educate_on_lowering_risk_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>Understanding effects of Hypertension on Kidneys.</p>
							</td>
							<td align="center">{{@$ckd_outcomes['hypertension_effects_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['hypertension_effects_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['hypertension_effects_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To understand healthy diet for Kidneys.</p>
							</td>
							<td align="center">{{@$ckd_outcomes['healthy_diet_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['healthy_diet_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['healthy_diet_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To understand effect of Protein on Kidneys.</p>
							</td>
							<td align="center">{{@$ckd_outcomes['protein_effects_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['protein_effects_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['protein_effects_food_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>To understand health effects of elevated Cholesterol and triglycerides with CKD.</p>
							</td>
							<td align="center">{{@$ckd_outcomes['elevated_cholesterol_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['elevated_cholesterol_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['elevated_cholesterol_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 2 ENDS --}}

						{{-- GOAL 3 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b> Assess patient knowledge on Diabetic Kidney Disease. </b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$ckd_outcomes['goal3_status'] ?? ""}}</td>
						</tr>
					
						{{-- TASKS --}}
						<tr>
							<td colspan="9" style="width:65%;">
							<p> To educate patient on DKD. </p>
							</td>
							<td align="center">{{@$ckd_outcomes['educate_on_dkd_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['educate_on_dkd_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['educate_on_dkd_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p> To educate patient on DKD symptoms. </p>
							</td>
							<td align="center">{{@$ckd_outcomes['dkd_symptoms_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['dkd_symptoms_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['dkd_symptoms_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p> To educate patient on risk factors of DKD. </p>
							</td>
							<td align="center">{{@$ckd_outcomes['dkd_risk_factors_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['dkd_risk_factors_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['dkd_risk_factors_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p> To educate patient on prevention of progression of DKD. </p>
							</td>
							<td align="center">{{@$ckd_outcomes['dkd_progression_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['dkd_progression_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['dkd_progression_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p> To educate the effect of healthy lifestyle on DKD. </p>
							</td>
							<td align="center">{{@$ckd_outcomes['healthy_lifestyle_effect_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['healthy_lifestyle_effect_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['healthy_lifestyle_effect_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p>
								To educate the effect of controlling blood sugar.
							</p>
							</td>
							<td align="center">{{@$ckd_outcomes['blood_sugar_control_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['blood_sugar_control_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['blood_sugar_control_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p> To educate importance of HBA1C. </p>
							</td>
							<td align="center">{{@$ckd_outcomes['hba1c_importance_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['hba1c_importance_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['hba1c_importance_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p> To educate how to bring blood sugars under control. </p>
							</td>
							<td align="center">{{@$ckd_outcomes['control_blood_sugar_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['control_blood_sugar_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['control_blood_sugar_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p> To educate the effect of Blood Pressure on DKD. </p>
							</td>
							<td align="center">{{@$ckd_outcomes['sugar_effect_on_dkd_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['sugar_effect_on_dkd_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['sugar_effect_on_dkd_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p> To educate about the treatment of Hypertension. </p>
							</td>
							<td align="center">{{@$ckd_outcomes['hypertension_treatment_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['hypertension_treatment_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['hypertension_treatment_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 3 ENDS --}}
						
						{{-- GOal 4 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>Assess knowledge of association between CKD and Cardiovascular disease.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$ckd_outcomes['goal4_status'] ?? ""}}</td>
						</tr>
					
						<tr>
							<td colspan="9" style="width:65%;">
							<p> To educate patient on association between CKD and heart disease. </p>
							</td>
							<td align="center">{{@$ckd_outcomes['ckd_heart_start_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['ckd_heart_end_date'] ?? ""}}</td>
							<td align="center">{{@$ckd_outcomes['ckd_heart_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 4 ENDS --}}
					</tbody>
				</table>
			@endif
			<!-- CKD end -->

			<!-- CHF start -->
			@if ($chronic_disease["CongestiveHeartFailure"])
				<table style="width:100%;" class="table table-border">
					<tbody>
						<tr>
							<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
								CHF
							</th>
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

							<tr>
								<td colspan="9" style="width:65%;">
								<b>To acquire knowledge about congestive heart failure and how it can affect you</b>
								</td>
								<td></td>
								<td></td>
								<td align="center">{{@$chf_outcomes['goal1_status'] ?? ""}}</td>
							</tr>

							<tr>
								<td colspan="9" style="width:65%;">
								Assess the patient's current knowledge and understanding regarding disease
								</td>
								<td align="center">{{@$chf_outcomes['understanding_regarding_disease_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['understanding_regarding_disease_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['understanding_regarding_disease_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
								Monitor blood pressure levels of patients
								</td>
								<td align="center">{{@$chf_outcomes['monitor_blood_pressure_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['monitor_blood_pressure_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['monitor_blood_pressure_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
								Monitor ECG levels of patients
								</td>
								<td align="center">{{@$chf_outcomes['monitor_ECG_levels_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['monitor_ECG_levels_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['monitor_ECG_levels_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To closely monitor the signs and symptoms to mitigate the chances or relapse.</b>
								</td>
								<td></td>
								<td></td>
								<td align="center">{{@$chf_outcomes['goal2_status'] ?? ""}}</td>
							</tr>

							<tr>
								<td colspan="9" style="width:65%;">
								Patient will demonstrate adequate cardiac output as evidenced by vital signs within acceptable limits, dysrhythmias absent/controlled, and no symptoms of failure (e.g., hemodynamic parameters within acceptable limits, urinary output adequate)
								</td>
								<td align="center">{{@$chf_outcomes['adequate_cardiac_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['adequate_cardiac_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['adequate_cardiac_status'] ?? ""}}</td>
							</tr>
							
							<tr>
								<td colspan="9" style="width:65%;">
								MONITOR Symptoms like Cerebral hypoperfusion occurs because of hypoxia to the brain from the decreased cardiac output. The patient may report this as confusion, forgetfulness, restlessness. Through assessment is necessary to evaluate for possible related conditions, including psychologic disorders. Depression is common among patients with heart failure and can lead to poor adherence to treatment plans.
								</td>
								<td align="center">{{@$chf_outcomes['cerebral_hypoperfusion_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['cerebral_hypoperfusion_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['cerebral_hypoperfusion_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To assess the signs of respiratory distress</b>
								</td>
								<td></td>
								<td></td>
								<td align="center">{{@$chf_outcomes['goal3_status'] ?? ""}}</td>
							</tr>

							<tr>
								<td colspan="9" style="width:65%;">
								<b>Give awareness to patient regarding pulmonary hygiene as needed</b>
								Pulmonary hygiene, refers to exercises and procedures that help to clear your airways of mucus and other secretions. This ensures that your lungs get enough oxygen, and your respiratory system works efficiently. There are several pulmonary hygiene methods and approaches. Some can be done on your own at home, while others require a visit to your healthcare provider like breathing exercise, relaxed breathing, Huffing=This exercise requires you to “huff” by breathing hard out of your mouth, as though you were creating fog on a mirror. Spirometry, This method of strengthening and controlling your breathing uses a device called an incentive spirometer.
								</td>
								<td align="center">{{@$chf_outcomes['pulmonary_hygiene_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['pulmonary_hygiene_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['pulmonary_hygiene_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
								<b>Keep the head of the bed elevated in case of respiratory distress</b>
								Head-end elevation is known to improve oxygenation and respiratory mechanics. In poor lung compliance limits positive pressure ventilation causing delivery of inadequate minute ventilation (MVe). We observed that, in moderate-to-severe cases, the respiratory system compliance reduces upon elevating the head-end of the bed, and vice-versa, which can be utilized to improve ventilation and avoid respiratory distress.
								</td>
								<td align="center">{{@$chf_outcomes['respiratory_distress_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['respiratory_distress_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['respiratory_distress_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
								<b>Monitor ABG levels of patients</b>
								Your lungs and your kidneys do much of the work to keep your acid-base balance normal. So, the acid-base measurement from an ABG test can help diagnose and monitor conditions that affect your lungs and kidneys as well as many other conditions that may upset your acid-base balance.
								</td>
								<td align="center">{{@$chf_outcomes['monitor_ABG_levels_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['monitor_ABG_levels_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['monitor_ABG_levels_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To understand the importance of Monitoring signs of altered cardiac output, including</b>
								</td>
								<td></td>
								<td></td>
								<td align="center">{{@$chf_outcomes['goal4_status'] ?? ""}}</td>
							</tr>

							<tr>
								<td colspan="9" style="width:65%;">
								<b>Monitoring of Pulmonary edemas</b>
								Pulmonary edema is a condition caused by too much fluid in the lungs. This fluid collects in the many air sacs in the lungs, making it difficult to breathe. It needs to be monitor by feeling any difficulty in respiration.
								</td>
								<td align="center">{{@$chf_outcomes['pulmonary_edemas_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['pulmonary_edemas_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['pulmonary_edemas_status'] ?? ""}}</td>
							</tr>
							
							<tr>
								<td colspan="9" style="width:65%;">
								<b>Assess conditions of Arrhythmias, including extreme tachycardia and bradycardia</b>
								</td>
								<td align="center">{{@$chf_outcomes['conditions_of_Arrhythmias_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['conditions_of_Arrhythmias_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['conditions_of_Arrhythmias_status'] ?? ""}}</td>
							</tr>
							
							<tr>
								<td colspan="9" style="width:65%;">
								<b>Check ECG and heart sound changes in every cardiologist visit.</b>
								The electrocardiogram (ECG) at rest is a non-invasive investigation that is recommended in the initial evaluation of patients with heart failure (HF). This is because the ECG is crucial in the detection of many abnormalities that may either cause or worsen HF. Therefore it is important to evaluate any changes in your heart sound by ECG (ELECTRO CARDIO GRAM).
								</td>
								<td align="center">{{@$chf_outcomes['cardiologist_visit_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['cardiologist_visit_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['cardiologist_visit_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
								<b>Demonstrate stabilized fluid volume with balanced intake and output, breath sounds clear/clearing, vital signs within acceptable range, stable weight, and absence of edema.</b>
								</td>
								<td></td>
								<td></td>
								<td align="center">{{@$chf_outcomes['goal5_status'] ?? ""}}</td>
							</tr>
					
							<tr>
								<td colspan="9" style="width:65%;">
								Evaluate fluid status by Monitor daily weights Assess for edema and severe diaphoresis Monitor electrolyte values and hematocrit level Verbalize understanding of individual dietary/fluid restrictions.
								</td>
								<td align="center">{{@$chf_outcomes['fluid_status_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['fluid_status_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['fluid_status_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
								<b>Identify relationship of ongoing therapies (treatment program) to reduction of recurrent episodes and prevention of complications.</b>
								</td>
								<td></td>
								<td></td>
								<td align="center">{{@$chf_outcomes['goal6_status'] ?? ""}}</td>
							</tr>

							<tr>
								<td colspan="9" style="width:65%;">
								Antiarrhythmias to increase cardiac performance Diuretics, to reduce venous and systemic congestion Iron and folic acid supplements to improve nutritional status Angiotensin-converting enzyme (ACE) inhibitors.These drugs relax blood vessels to lower blood pressure, improve blood flow and decrease the strain on the heart. Beta blockers.These drugs slow your heart rate and reduce blood pressure. Beta blockers may reduce signs and symptoms of heart failure, improve heart function. Digoxin (Lanoxin).This drug, also called digitalis, increases the strength of your heart muscle contractions
								</td>
								<td align="center">{{@$chf_outcomes['antiarrhythmias_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['antiarrhythmias_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['antiarrhythmias_status'] ?? ""}}</td>
							</tr>
						


							<tr>
								<td colspan="9" style="width:65%;">
								To understand the importance of regular follow-up with PCP and cardiologist.
								</td>
								<td align="center">{{@$chf_outcomes['followup_pcp_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['followup_pcp_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['followup_pcp_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
								To recognize the importance of discipline in taking all medications as prescribed.
								</td>
								<td align="center">{{@$chf_outcomes['importance_medication_start_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['importance_medication_end_date'] ?? ""}}</td>
								<td align="center">{{@$chf_outcomes['importance_medication_status'] ?? ""}}</td>
							</tr>
						


					</tbody>
				</table>
			@endif
			<!-- CHF end -->

			<!-- Hypercholesterolemia start -->
			@if ($chronic_disease["Hypercholesterolemia"])
				<table style="width:100%;" class="table table-border">
					<tbody>
						<tr>
							<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
								Hypercholesterolemia
							</th>
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
							<!-- GOAL ! -->
							<tr>
								<td colspan="9" style="width:65%;">
									<p>Patient will learn various causes of hyperlipidemia. </p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['causes_of_hyperlipidemia_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['causes_of_hyperlipidemia_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['causes_of_hyperlipidemia_status'] ?? ""}}</td>
							</tr>
							
							<tr>
								<td colspan="9" style="width:65%;">
									<p>Patient will learn to avoid saturated & trans-fat. </p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['saturated_trans_fat_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['saturated_trans_fat_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['saturated_trans_fat_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
									<p>Patient will learn importance of checking yearly Lipids & LDL goal.</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['lab_mandatory_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['lab_mandatory_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['lab_mandatory_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
									<p>Patient will learn other conditions that can co-exist and managing Lipid can help them</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['monitor_comorbid_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['monitor_comorbid_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['monitor_comorbid_status'] ?? ""}}</td>
							</tr>
							<!-- GOAL 1 ENDS -->
						
							<!-- GOAL 2 STARTS -->
							<tr>
								<td colspan="9" style="width:65%;">
									<b> To understand the effect of Lipids on Cardiovascular System.</b>
								</td>
								<td></td>
								<td></td>
								<td align="center">{{@$hypercholestrolemia_outcomes['goal2_status'] ?? ""}}</td>
							</tr>

							<!-- TASKS -->
							<tr>
								<td colspan="9" style="width:65%;">
									<p>Understanding how high LDL leads to heart attack.</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['understand_etiology_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['understand_etiology_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['understand_etiology_status'] ?? ""}}</td>
							</tr>
							
							<tr>
								<td colspan="9" style="width:65%;">
									<p>Cholesterol is a factor in ASCVD score.</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['calculate_ASCVD_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['calculate_ASCVD_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['calculate_ASCVD_status'] ?? ""}}</td>
							</tr>
							<!-- GOAL2 ENDS -->
							

							<!-- GOAL 3 STARTS -->
							<tr>
								<td colspan="9" style="width:65%;">
									<b> To understand the importance of healthy diet in controlling Lipids.</b>
								</td>
								<td></td>
								<td></td>
								<td align="center">{{@$hypercholestrolemia_outcomes['goal3_status'] ?? ""}}</td>
							</tr>

							<!-- Tasks -->
							<tr>
								<td colspan="9" style="width:65%;">
								<p>Teaching about healthy diet.</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['dietary_factors_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['dietary_factors_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['dietary_factors_status'] ?? ""}}</td>
							</tr>
							
							<tr>
								<td colspan="9" style="width:65%;">
									<p>Visiting to nutritionist for proper diet plan</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['visiting_nutritionist_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['visiting_nutritionist_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['visiting_nutritionist_status'] ?? ""}}</td>
							</tr>
							<!-- Goal 3 ENDS -->			
						
							<!-- Goal 4 -->
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

							<!-- Tasks -->
							<tr>
								<td colspan="9" style="width:65%;">
								<p>How much exercise is better?</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['amount_of_exercise_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['amount_of_exercise_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['amount_of_exercise_status'] ?? ""}}</td>
							</tr>
							
							<tr>
								<td colspan="9" style="width:65%;">
								<p>What is the effect of exercise on Lipids?</p>
								</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['effect_of_exercise_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['effect_of_exercise_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypercholestrolemia_outcomes['effect_of_exercise_status'] ?? ""}}</td>
							</tr>
					</tbody>
				</table>
			@endif
			<!-- Hypercholesterolemia end -->

			<!-- Hypertension start -->
			@if ($chronic_disease["Hypertensions"])
				<table style="width:100%;" class="table table-border">
					<tbody>
						<tr>
							<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
								Hypertension
							</th>
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

							<tr>
								<td colspan="9" style="width:65%;">
								<b>To acquire Knowledge about Hypertension and its effect on the multiple body organs.</b>
								</td>
								<td></td>
								<td></td>
								<td align="center">{{@$hypertension_outcomes['goal1_status'] ?? ""}}</td>
							</tr>


							<tr>
								<td colspan="9" style="width:65%;">
								<p>Educate patient on HTN and its long-term effects on the body.</p>
								</td>
								<td align="center">{{@$hypertension_outcomes['understanding_regarding_disease_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['understanding_regarding_disease_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['understanding_regarding_disease_status'] ?? ""}}</td>
							</tr>
						

							<!-- GOAL 2 STARTS -->
							<!-- GOAL 2 -->
							<tr>
								<td colspan="9" style="width:65%;">
								<b>To educate patient on Lifestyle modifications to help with better BP control.</b>
								</td>
								<td></td>
								<td></td>
								<td align="center">{{@$hypertension_outcomes['goal2_status'] ?? ""}}</td>
							</tr>
						
							<!-- GOAL 2 TASKS -->
							<tr>
								<td colspan="9" style="width:65%;">
									Educate the patient about DASH diet
								</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_dash_diet_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_dash_diet_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_dash_diet_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
									Educate patient about low sodium diet
								</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_sodium_diet_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_sodium_diet_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_sodium_diet_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
									Educate patient about importance of exercise
								</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_excercise_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_excercise_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_excercise_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
									Educate patient on effects of alcohol on BP
								</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_alcoholeffects_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_alcoholeffects_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_alcoholeffects_status'] ?? ""}}</td>
							</tr>
						
							<tr>
								<td colspan="9" style="width:65%;">
									Educate patients about the effect of smoking on BP
								</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_smokingeffects_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_smokingeffects_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['educate_about_smokingeffects_status'] ?? ""}}</td>
							</tr>
							<!-- GOAL 2 Tasks END -->
							<!-- GOLA 2 ENDS -->
						
							<!-- GOAL 3 STARTS -->
							<tr>
								<td colspan="9" style="width:65%;">
									<b>Patient will understand the importance of Treatment Adherence and Regular BP monitoring.</b>
								</td>
								<td></td>
								<td></td>
								<td align="center">{{@$hypertension_outcomes['goal3_status'] ?? ""}}</td>
							</tr>
							<!-- GOAL3 TASKS -->
							<tr>
								<td colspan="9" style="width:65%;">
									<p>Explain to the patient the role of regular BP monitoring and treatment adherence in BP control.</p>
								</td>
								<td align="center">{{@$hypertension_outcomes['regular_bp_monitoring_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['regular_bp_monitoring_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['regular_bp_monitoring_status'] ?? ""}}</td>
							</tr>
							<!-- GOAL 3 Tasks END -->
							<!-- GOLA 3 ENDS -->

							<tr>
								<td colspan="9" style="width:65%;">
									<b>Regular Follow up with PCP.</b>
								</td>
								<td></td>
								<td></td>
								<td align="center">{{@$hypertension_outcomes['goal4_status'] ?? ""}}</td>
							</tr>
							<tr>
								<td colspan="9" style="width:65%;">
								<p>Patient will understand the importance of regular follow ups with PCP for BP monitoring as well as overall health assessment periodically. </p>
								</td>
								<td align="center">{{@$hypertension_outcomes['regular_pcp_folloup_start_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['regular_pcp_folloup_end_date'] ?? ""}}</td>
								<td align="center">{{@$hypertension_outcomes['regular_pcp_folloup_status'] ?? ""}}</td>
							</tr>
					</tbody>
				</table> 	
			@endif
			<!-- Hypertension end -->

			<!-- Diabetes start -->
			@if ($chronic_disease["DiabetesMellitus"])
				<table style="width:100%;" class="table table-border">
					<tbody>
						<tr>
							<th colspan="12" class="text-center table-primary" style="background: #b8daff; color: #23468c;">
								Diabetes
							</th>
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
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To understand the importance of Blood Glucose Monitoring and control.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$diabetes_outcome['goal1_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>Assess the patients current knowledge and understanding regarding disease</p>
							</td>
							<td align="center">{{@$diabetes_outcome['monitoring_blood_sugar_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['monitoring_blood_sugar_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['monitoring_blood_sugar_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>Weight daily, Explain the importance of weight loss to obese patients with diabetes</p>
							</td>
							<td align="center">{{@$diabetes_outcome['importance_of_weight_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['importance_of_weight_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['importance_of_weight_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>Assess the pattern of physical activity</p>
							</td>
							<td align="center">{{@$diabetes_outcome['assess_the_pattern_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['assess_the_pattern_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['assess_the_pattern_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>Monitor blood glucose levels before meals and at bedtime to control</p>
							</td>
							<td align="center">{{@$diabetes_outcome['monitor_blood_glucose_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['monitor_blood_glucose_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['monitor_blood_glucose_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 1 ENDS --}}
						
						{{-- GOAL 2 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
								<b>To Understand the importance of Diabetic Diet.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$diabetes_outcome['goal2_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>Understanding A, B & C of Diabetes</p>
							</td>
							<td align="center">{{@$diabetes_outcome['abc_of_diabetes_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['abc_of_diabetes_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['abc_of_diabetes_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>Keeping your Weight under control</p>
							</td>
							<td align="center">{{@$diabetes_outcome['undercontrol_weight_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['undercontrol_weight_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['undercontrol_weight_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
								<p>Seeing a Dietician</p>
							</td>
							<td align="center">{{@$diabetes_outcome['seeing_dietician_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['seeing_dietician_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['seeing_dietician_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 2 ENDS --}}

						{{-- GOAL 3 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To Understand Hypoglycemia, hyperglycemia and how to prevent them.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$diabetes_outcome['goal3_status'] ?? ""}}</td>
						</tr>

						
						<tr>
							<td colspan="9" style="width:65%;">
								<p>Assess for signs of hyperglycemia/hypoglycemia</p>
							</td>
							<td align="center">{{@$diabetes_outcome['signs_of_hyperglycemia_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['signs_of_hyperglycemia_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['signs_of_hyperglycemia_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>prevention of hyperglycemia by exercise to help lower blood sugar, follow your meal plan maintain a healthy weight, don't smoke and limit alcohol</p>
							</td>
							<td align="center">{{@$diabetes_outcome['prevention_of_hyperglycemia_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['prevention_of_hyperglycemia_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['prevention_of_hyperglycemia_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>prevention of hypoglycemia to help lower blood sugar, follow your meal plan maintain a healthy weight, don't smoke and limit alcohol</p>
							</td>
							<td align="center">{{@$diabetes_outcome['lower_blood_sugar_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['lower_blood_sugar_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['lower_blood_sugar_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 3 ENDS --}}

						{{-- GOAL 4 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To Understand the importance of Diabetic Eye exam.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$diabetes_outcome['goal4_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>Understanding how high blood sugar effects Eyes</p>
							</td>
							<td align="center">{{@$diabetes_outcome['sugar_effect_on_eye_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['sugar_effect_on_eye_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['sugar_effect_on_eye_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>Understanding different ways Diabetes can affect the Eyes</p>
							</td>
							<td align="center">{{@$diabetes_outcome['sugar_ways_to_effect_on_eye_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['sugar_ways_to_effect_on_eye_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['sugar_ways_to_effect_on_eye_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 4 ENDS --}}

						{{-- GOAL 5 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To Understand the importance of Diabetic Foot Care.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$diabetes_outcome['goal5_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>Understanding how Diabetic damage the nerves in the Foot.</p>
							</td>
							<td align="center">{{@$diabetes_outcome['foot_nerves_damage_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['foot_nerves_damage_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['foot_nerves_damage_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>How to protect your feet in Diabetes?</p>
							</td>
							<td align="center">{{@$diabetes_outcome['protect_feet_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['protect_feet_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['protect_feet_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>How to do your foot examination?</p>
							</td>
							<td align="center">{{@$diabetes_outcome['foot_examination_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['foot_examination_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['foot_examination_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 5 ENDS --}}

						{{-- GOAL 6 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To understand Cardiovascular Complications secondary to Diabetes.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$diabetes_outcome['goal6_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>Learning the leading cause of death in Diabetics.</p>
							</td>
							<td align="center">{{@$diabetes_outcome['death_cause_in_diabetes_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['death_cause_in_diabetes_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['death_cause_in_diabetes_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>Learning three ways to decrease the risk of Cardio-vascular disease?</p>
							</td>
							<td align="center">{{@$diabetes_outcome['risk_of_cardio_disease_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['risk_of_cardio_disease_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['risk_of_cardio_disease_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>Learning to keep your cholesterol and triglyceride levels in a healthy range.</p>
							</td>
							<td align="center">{{@$diabetes_outcome['cholesterol_healthy_range_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['cholesterol_healthy_range_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['cholesterol_healthy_range_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>Consider daily low-dose aspirin, depending on your other conditions.</p>
							</td>
							<td align="center">{{@$diabetes_outcome['low_dose_aspirin_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['low_dose_aspirin_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['low_dose_aspirin_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 6 ENDS --}}

						{{-- GOAL 7 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To understand Kidney complications secondary to diabetes.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$diabetes_outcome['goal7_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>Understanding the effect of diabetes on Kidneys.</p>
							</td>
							<td align="center">{{@$diabetes_outcome['diabetes_effect_on_kidneys_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['diabetes_effect_on_kidneys_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['diabetes_effect_on_kidneys_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>How to know if your kidneys are being effected by diabetes?</p>
							</td>
							<td align="center">{{@$diabetes_outcome['know_how_kidneys_effected_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['know_how_kidneys_effected_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['know_how_kidneys_effected_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>How to protect your kidneys if diabetes has started to damage it?</p>
							</td>
							<td align="center">{{@$diabetes_outcome['protect_kidneys_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['protect_kidneys_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['protect_kidneys_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 7 ENDS --}}

						{{-- GOAL 8 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To understand Kidney complications secondary to diabetes.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$diabetes_outcome['goal8_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>What should be your BP if you are diabetic?</p>
							</td>
							<td align="center">{{@$diabetes_outcome['bp_recommendation_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['bp_recommendation_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['bp_recommendation_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>How to lower your BP?</p>
							</td>
							<td align="center">{{@$diabetes_outcome['how_to_lower_bp_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['how_to_lower_bp_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['how_to_lower_bp_status'] ?? ""}}</td>
						</tr>
						{{-- GOAL 8 ENDS --}}

						{{-- GOAL 9 STARTS --}}
						<tr>
							<td colspan="9" style="width:65%;">
							<b>To recognize the signs and symptoms of exacerbation that must be reported to the doctor/nurse.</b>
							</td>
							<td></td>
							<td></td>
							<td align="center">{{@$diabetes_outcome['goal9_status'] ?? ""}}</td>
						</tr>

						<tr>
							<td colspan="9" style="width:65%;">
							<p>Monitor hunger and fatigue that would be exacerbate later</p>
							</td>
							<td align="center">{{@$diabetes_outcome['monitor_hunger_and_fatigue_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['monitor_hunger_and_fatigue_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['monitor_hunger_and_fatigue_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>Assess Frequent urination, dry mouth, or blurred vision</p>
							</td>
							<td align="center">{{@$diabetes_outcome['assess_frequent_urination_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['assess_frequent_urination_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['assess_frequent_urination_status'] ?? ""}}</td>
						</tr>
						
						<tr>
							<td colspan="9" style="width:65%;">
							<p>Assess slow healing of wound</p>
							</td>
							<td align="center">{{@$diabetes_outcome['assess_slow_healing_start_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['assess_slow_healing_end_date'] ?? ""}}</td>
							<td align="center">{{@$diabetes_outcome['assess_slow_healing_status'] ?? ""}}</td>
						</tr>
					</tbody>
				</table>
			@endif
			<!-- Diabetes end -->
		</div>

		<script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
		<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
	</body>
</html>