<!DOCTYPE html>

<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">

	<link rel="stylesheet" href="{{ asset('css/fontawesome.css') }}">
	<link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">

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
			border: 1px solid black;
		}

		tr>td {
			padding: 5px;
			justify-content: left;
		}

		@font-face {
			font-family: 'fontawesome3';
			src: url('https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/fonts/fontawesome-webfont.ttf?v=4.6.1') format('truetype');
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
	</style>
	@php
	$next_assessment = \Carbon\Carbon::create($row['created_at'])->addYear(1)->format('m/Y');
	$dateofBirth = \Carbon\Carbon::parse($row['patient']['dob'])->format('m/d/Y');
	$row['date_of_service'] = \Carbon\Carbon::parse($row['date_of_service'])->format('m/d/Y');
	@endphp

<body>


	<div style="margin-left: 220px; margin-top:250px;margin-bottom:460px">
		<div style="font-size:25px; font-weight:bold;margin-bottom: 10px">AWV 2022</div>
		<div style="font-size: 18px; font-weight:500;margin-bottom: 8px">This is your Preventive Care Plan</div>
		<div style="font-size: 18px; font-weight:500;margin-bottom: 8px">Attached Please see the following documents:</div>
		<div style="font-size: 18px; font-weight:500;margin-bottom: 8px">1. CDC recommendations for physical activity</div>
		<div style="font-size: 18px; font-weight:500;margin-bottom: 8px">2. CDC guidelines for alcohol use</div>
		<div style="font-size: 18px; font-weight:500;margin-bottom: 8px">3. CDC Information on Tobacco</div>
		<div style="font-size: 18px; font-weight:500;margin-bottom: 8px">4. USDA dietary guidelines</div>
		<div style="font-size: 18px; font-weight:500;margin-bottom: 8px"><b> {{$row['date_of_service']}}</b></div>
	</div>
	<div class="row">
		<div class="col-12">
			<p class="header">Patient Preventive Care Plan</p>
			<div>
				<h6 class="d-inline">Name:</h6>
				<p class="d-inline"> {{@$row['patient']['first_name'].' '.@$row['patient']['last_name']}} </p>

				<h6 class="d-inline ms-2">Date of Birth:</h6>
				<p class="d-inline"> {{$dateofBirth}} </p>

				<h6 class="d-inline ms-2">Age:</h6>
				<p class="d-inline"> {{$row['patient']['age']}} </p>

				<h6 class="d-inline ms-2">Gender:</h6>
				<p class="d-inline"> {{$row['patient']['gender']}} </p>

				<h6 class="d-inline ms-2">Height:</h6>
				<p class="d-inline"> {{$miscellaneous['height'] ?? ''}} </p>

				<h6 class="d-inline ms-2">Weight:</h6>
				<p class="d-inline"> {{$miscellaneous['weight'] ?? ''}} lbs </p>
			</div>

			<div style="position:relative; margin-left: 0px !important;">
				<h6 class="d-inline">Program:</h6>
				<p class="d-inline"> {{$row['program']['name']}} ({{$row['program']['short_name']}}) </p>

				<h6 class="d-inline ms-2">Primary care Physician:</h6>
				<p class="d-inline"> {{@$row['doctor'] ?? ''}} </p>
			</div>

			<div style="position:relative; margin-left: 0px !important;">
				<h6 class="d-inline">Date of Service:</h6>
				<p class="d-inline"> {{$row['date_of_service']}} </p>

				<h6 class="d-inline ms-2">Next Due:</h6>
				<p class="d-inline"> {{\Carbon\Carbon::create($row['date_of_service'])->addYear(1)->format('m/d/Y')}} </p>
			</div>
		</div>
	</div>

	<div class="col-12" style="margin-top:50px;">
		<table class="table" style="table-layout: fixed">
			<tbody>




				{{-- PHYSICAL HEALTH STARTS --}}
				{{-- <th class="text-center" colspan="12" style="text-align: center; background: black; color: white; column-span: all"> Physical health </th> --}}

				<th colspan="12" style="text-align: center; background: black; color: white;">
					{{-- <span style="margin-left:10%">Physical Activity </span>
							<span style="margin-left:80%; text-align: right"> Next due </span> --}}

					<span style="display:inline; margin-left:15%">Physical Health</span>
					<span style="display:flex; float:right; margin-right:4%;">Next Due</span>

				</th>

				<tr>
					<th scope="row" colspan="3">Physical Health - Fall Screening</th>
					<td colspan="6">
						@foreach ($fall_screening as $key => $val)
						@if ($val != "") {{$val}} <br /> @endif
						@endforeach
					</td>
					<td colspan="1">
						@if (empty($fall_screening))
						<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
						@endif
					</td>
					<td colspan="2">
						{{$next_assessment}}
					</td>
				</tr>
				{{-- PHYSICAL HEALTH ENDS --}}

				{{-- MENTAL HEALTH STARTS --}}
				<th class="text-center" colspan="12" style="text-align: center; background: black; color: white; column-span: all"> Mental health </th>
				<tr>
					<th scope="row" colspan="3">Depression PHQ-9</th>
					<td colspan="6">
						@foreach ($depression_out_comes as $key => $val)
						@if ($key != 'flag')
						{{$val}} <br />
						@endif
						@endforeach
					</td>
					<td colspan="1">
						@if (@$depression_out_comes['flag'])
						<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
						@endif
					</td>
					<td colspan="2">
						@if (@$depression_out_comes['severity'])
						{{$next_assessment}}
						@endif
					</td>
				</tr>
				{{-- MENTAL HEALTH ENDS --}}

				{{-- GENERAL HEALTH STARTS --}}
				<th class="text-center" colspan="12" style="text-align: center; background: black; color: white; column-span: all"> General health </th>
				<tr>
					<th scope="row" colspan="3">High Stress</th>
					<td colspan="6">{{@$high_stress['outcome'] ?? ''}}</td>
					<td colspan="1"></td>
					<td colspan="2">
						@if (@$high_stress['outcome'])
						{{$next_assessment}}
						@endif
					</td>
				</tr>

				<tr>
					<th scope="row" colspan="3">General Health</th>
					<td colspan="6">
						@foreach ($general_health as $key => $val)
						@if ($val != "" && $key != 'flag')
						{{ ucfirst(str_replace('_', ' ', $key)) }}: {{$val}} <br />
						@endif
						@endforeach
					</td>
					<td colspan="1"></td>
					<td colspan="2">
						@if (@$general_health['health_level'] || @$general_health['mouth_and_teeth'] || @$general_health['feelings_cause_distress'] )
						{{$next_assessment}}
						@endif
					</td>
				</tr>

				<tr>
					<th scope="row" colspan="3">Social/Emotional Support</th>
					<td colspan="6">{{@$social_emotional_support['outcome'] ?? ''}}</td>
					<td colspan="1"></td>
					<td colspan="2">
						@if (@$social_emotional_support['outcome'])
						{{$next_assessment}}
						@endif
					</td>
				</tr>

				<tr>
					<th scope="row" colspan="3">Pain</th>
					<td colspan="6">{{@$pain['outcome'] ?? ''}}</td>
					<td colspan="1"></td>
					<td colspan="2">
						@if (@$pain['outcome'])
						{{$next_assessment}}
						@endif
					</td>
				</tr>
				{{-- GENERAL HEALTH ENDS --}}

				{{-- COGNITIVE ASSESSMENT STARTS --}}
				<th class="text-center" colspan="12" style="text-align: center; background: black; color: white; column-span: all"> Cognitive Assessment </th>
				<tr>
					<th scope="row" colspan="3">Cognitive Assessment</th>
					<td colspan="6">{{@$cognitive_assessment['outcome'] ?? ''}}</td>
					<td colspan="1">
						@if (@$cognitive_assessment['flag'])
						<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
						@endif
					</td>
					<td colspan="2">
						@if (@$cognitive_assessment['outcome'])
						{{$next_assessment}}
						@endif
					</td>
				</tr>
				{{-- COGNITIVE ASSESSMENT ENDS --}}

				{{-- HABITS START --}}
				<th class="text-center" colspan="12" style="text-align: center; background: black; color: white; column-span: all"> Habits </th>
				<tr>
					<th scope="row" colspan="3">Physical Activity</th>
					<td colspan="6">{{@$physical_out_comes['outcome'] ?? ''}}</td>
					<td colspan="1">
						{{-- @if (@$physical_out_comes['flag'])
								<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
								@endif --}}
					</td>
					<td colspan="2">
						{{$next_assessment}}
					</td>
				</tr>

				<tr>
					<th scope="row" colspan="3">Alcohol Use</th>
					<td colspan="6">{{@$alcohol_out_comes['outcome'] ?? ''}}</td>

					<td colspan="1">
						{{-- @if (@$alcohol_out_comes['flag'])
								<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
								@endif --}}
					</td>
					<td colspan="2">
						{{$next_assessment}}
					</td>
				</tr>

				<tr>
					<th scope="row" colspan="3">Tobacco Use</th>
					<td colspan="6">
						{{@$tobacco_out_comes['quit_tobacoo'] ?? ''}}
						{{@$tobacco_out_comes['ldct_counseling'] ?? ''}} <br />
					</td>
					<td colspan="1">
						@if (@$tobacco_out_comes['flag'])
						<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
						@endif
					</td>
					<td colspan="2">
						{{$next_assessment}}
					</td>
				</tr>

				<tr>
					<th scope="row" colspan="3">Nutrition</th>
					<td colspan="6">
						CDC guidelines given and patient advised: <br />
						&bull; Vegetables 2 Cups every week <br />
						&bull; Fruit 1 ½ Cup Equivalent per day <br />
						&bull; Grain – 6 ounces eq each day <br />
					</td>

					<td colspan="1"></td>

					<td colspan="2">
						{{$next_assessment}}
					</td>
				</tr>

				<tr>
					<th scope="row" colspan="3">Seat Belt Use</th>
					<td colspan="6">{{@$seatbelt_use['outcome'] ?? ''}}</td>
					<td colspan="1"></td>
					<td colspan="2"></td>
				</tr>
				{{-- HABITS ENDS --}}

				{{-- IMMUNIZATION STARTS --}}
				<th class="text-center" colspan="12" style="text-align: center; background: black; color: white; column-span: all"> Immunization </th>
				<tr>
					<th scope="row" colspan="3">Immunization</th>
					<td colspan="6">
						{!! !empty($immunization['flu_vaccine']) ? $immunization['flu_vaccine'].'<br>' : "" !!}
						{!! !empty($immunization['flu_vaccine_script']) ? $immunization['flu_vaccine_script'].'<br>' : "" !!}
						{!! !empty($immunization['pneumococcal_vaccine']) ? $immunization['pneumococcal_vaccine'].'<br>' : "" !!}
						{!! !empty($immunization['pneumococcal_vaccine_script']) ? $immunization['pneumococcal_vaccine_script'].'<br>' : "" !!}
					</td>
					<td colspan="1">
						@if (@$immunization['flag'])
						<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
						@endif
					</td>
					<td colspan="2">
						@if (@$immunization['flu_vaccine'])
						Next season
						@endif
					</td>
				</tr>
				{{-- IMMUNIZATION ENDS --}}

				{{-- SCREENING STARTS --}}
				<th class="text-center" colspan="12" style="text-align: center; background: black; color: white; column-span: all"> Screening </th>
				<tr>
					<th scope="row" colspan="3">Mammogram</th>
					<td colspan="6">
						{!! !empty($screening['mammogram']) ? $screening['mammogram'].'<br>' : "" !!}
						{!! !empty($screening['next_mammogram']) ? $screening['next_mammogram'].'<br>' : "" !!}
						{!! !empty($screening['mammogram_script']) ? $screening['mammogram_script'].'<br>' : "" !!}
					</td>
					<td colspan="1">
						@if (@$screening['mammogaram_flag'])
						<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
						@endif
					</td>
					<td colspan="2">
						@if (@$screening['next_mammogram_date'])
						<strong>Next Mammogaram due:</strong> {{@$screening['next_mammogram_date'] ?? ''}} <br>
						@endif
					</td>
				</tr>
				<tr>
					<th scope="row" colspan="3">Colon Cancer</th>
					<td colspan="6">
						{!! !empty($screening['colonoscopy']) ? $screening['colonoscopy'].'<br>' : "" !!}
						{!! !empty($screening['next_colonoscopy']) ? $screening['next_colonoscopy'].'<br>' : "" !!}
						{!! !empty($screening['colonoscopy_script']) ? $screening['colonoscopy_script'].'<br>' : "" !!}
					</td>
					<td colspan="1">
						@if (@$screening['colo_flag'])
						<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
						@endif
					</td>
					<td colspan="2">
						@if (@$screening['next_col_fit_guard'])
						<strong>Next {{@$screening['test_type'] ?? ''}} due:</strong> {{@$screening['next_col_fit_guard'] ?? ''}}
						@endif
					</td>
				</tr>
				{{-- SCREENING ENDS --}}

				{{-- METABOLIC SCREENING STARTS --}}
				@php
				$title = (!isset($diabetes['is_diabetic']) || $diabetes['is_diabetic'] == 'No' ? 'Fasting Blood Sugar' : 'Diabetes');
				$keytitle = (isset($diabetes['is_diabetic']) && $diabetes['is_diabetic'] == 'Yes' ? 'DM - ' : '');
				@endphp
				<th class="text-center" colspan="12" style="text-align: center; background: black; color: white; column-span: all"> Metabolic Screening </th>
				<tr>
					<th scope="row" colspan="3" colsp>{{$title}}</th>
					<td colspan="6">
						@php
						$keysNotReq = ['flag', 'diabetec_eye_exam', 'nepropathy', 'eye_exam_flag', 'nephropathy_flag', 'next_fbs_date', 'next_hba1c_date', 'is_diabetic'];
						@endphp

						@foreach ($diabetes as $key => $val)
						@if ($val != "" && !in_array($key, $keysNotReq))
						{{$val}} <br />
						@endif
						@endforeach
					</td>
					<td colspan="1">
						@if (@$diabetes['flag'])
						<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
						@endif
					</td>
					<td colspan="2">
						@if (@$diabetes['next_fbs_date'])
						<strong>FBS:</strong> {{@$diabetes['next_fbs_date'] ?? ''}} <br>
						@endif
						@if (@$diabetes['next_hba1c_date'])
						<strong>HBA1C:</strong> {{@$diabetes['next_hba1c_date'] ?? ''}} <br>
						@endif

					</td>
				</tr>

						@if (@$diabetes['diabetec_eye_exam'] != "")
						<tr>
							<th scope="row" colspan="3">{{$keytitle}}Eye Examination</th>
							<td colspan="6">
								{{@$diabetes['diabetec_eye_exam'] ?? ''}}
							</td>
							<td colspan="1">
								@if (@$diabetes['eye_exam_flag'])
								<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
								@endif
							</td>
							<td colspan="2"></td>
						</tr>
						@endif

				@if (@$diabetes['nepropathy'] != "")
				<tr>
					<th scope="row" colspan="3">{{$keytitle}}Nephropathy</th>
					<td colspan="6">
						{{@$diabetes['nepropathy'] ?? ''}}
					</td>
					<td colspan="1">
						@if (@$diabetes['nephropathy_flag'])
						<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
						@endif
					</td>
					<td colspan="2"></td>
				</tr>
				@endif

				<tr>
					<th scope="row" colspan="3">Cholesterol</th>
					<td colspan="6">{{@$cholesterol_assessment['outcome'] ?? ''}}</td>
					<td colspan="1">
						@if (@$cholesterol_assessment['flag'])
						<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
						@endif
					</td>
					<td colspan="2"> {{@$cholesterol_assessment['ldl_next_due'] ?? ''}} </td>
				</tr>

				<tr>
					<th scope="row" colspan="3">BP Assessment</th>
					<td colspan="6">{{@$bp_assessment['bp_result'] ?? ''}} {{@$bp_assessment['outcome'] ?? ''}}</td>
					
					<td colspan="1">
					@if (@$bp_assessment['flag'])
						<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
					@endif
					</td>
					
					<td colspan="2">
						@if (@$bp_assessment['outcome'])
						{{$next_assessment}}
						@endif
					</td>
				</tr>
				
				<tr>
					<th scope="row" colspan="3">Weight Assessment</th>
					<td colspan="6">{{@$weight_assessment['bmi_result'] ?? ''}} {{@$weight_assessment['outcome'] ?? ''}}</td>
					<td colspan="1">
						{{-- @if (@$weight_assessment['flag'])
						<i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i> 
					@endif --}}
					</td>
					<td colspan="2">
						@if (@$weight_assessment['outcome'])
						{{$next_assessment}}
						@endif
					</td>
				</tr>
				{{-- METABOLIC SCREENING ENDS --}}

				<tr>
					<th scope="row" colspan="3">Physical Exam</th>
					<td colspan="6">
						@if (!empty($physical_exam))	
							@foreach ($physical_exam as $key => $value)
								<b>{{$key}}: </b> {{$value}}  <br>
							@endforeach
						@endif
					</td>
					<td colspan="1">
					</td>
					<td colspan="2">
						
					</td>
				</tr>

			</tbody>
		</table>
	</div>

			</tbody>
		</table>
	</div>

	@if (@$row['signed_date'])
	<div class="card-body">
		<strong>
			<p class="d-inline"> Electronically signed by {{$row['doctor']}} on {{\Carbon\Carbon::parse($row['signed_date'])->toDateString()}} at {{\Carbon\Carbon::parse($row['signed_date'])->format('g:i A')}} </p>
		</strong>
	</div>
	@endif

	{{-- CDC guidelines for Physical activity --}}
	<img src="{{asset('guidelines/Guidelines for Physical Activity.jpg') }}" width="100%" height="100%">

	{{-- CDC guidelines for Alcohol --}}
	<img src="{{asset('guidelines/Dietary Guidelines for Alcohol.jpg') }}" width="100%" height="100%">

	{{-- CDC guidelines for Tobacco --}}
	<img src="{{asset('guidelines/The Harmful Effects of Tobacco Use.jpg') }}" width="100%" height="100%">

	{{-- CDC guidelines for Nutition --}}
	<img src="{{asset('guidelines/DGA_2020-2025_ExecutiveSummary-1.jpg') }}" width="100%" height="100%">
	<img src="{{asset('guidelines/DGA_2020-2025_ExecutiveSummary-2.jpg') }}" width="100%" height="100%">
	<img src="{{asset('guidelines/DGA_2020-2025_ExecutiveSummary-3.jpg') }}" width="100%" height="100%">
	<img src="{{asset('guidelines/DGA_2020-2025_ExecutiveSummary-4.jpg') }}" width="100%" height="100%">

			<script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    		<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
		</body>
	</head>
</html>