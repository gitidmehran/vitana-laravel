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
			border: 1px solid #D3D3D3;
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
		  background-image: url('guidelines/background.png');
		  background-size: cover;
		}
	</style>
	@php
		$next_assessment = \Carbon\Carbon::create(@$row['created_at'])->addYear(1)->format('m/Y');
		$dateofBirth = \Carbon\Carbon::parse(@$row['patient']['dob'])->format('m/d/Y');
		$row['date_of_service'] = \Carbon\Carbon::parse(@$row['date_of_service'])->format('m/d/Y');
		$currentYear = \Carbon\Carbon::now()->year;
	@endphp

<body>


	<!-- <div style="margin-left: 220px; margin-top:250px;margin-bottom:460px; background-image:url(data:image/jpeg;base64,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)"> -->
	<div size="A4" class="radial-repeating">
      <div class="center" >
				<p >AWV {{$currentYear}}
				This is your Preventive Care Plan
				Attached Please see the following documents:
				<ol>
					<li>CDC recommendations for physical activity</li>
					<li> CDC guidelines for alcohol use</li>
					<li> CDC Information on Tobacco</li>
					<li>USDA dietary guidelines</li>
				</ol>
				
				<b> Visit Date: {{@$row['date_of_service']}}</b>
			</p>
			</div>
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
				<p class="d-inline"> {{@$row['patient']['age']}} </p>

				<h6 class="d-inline ms-2">Gender:</h6>
				<p class="d-inline"> {{@$row['patient']['gender']}} </p>

				<h6 class="d-inline ms-2">Height:</h6>
				<p class="d-inline"> {{$miscellaneous['height'] ?? ''}} </p>

				<h6 class="d-inline ms-2">Weight:</h6>
				<p class="d-inline"> {{$miscellaneous['weight'] ?? ''}} lbs </p>
			</div>

			<div style="position:relative; margin-left: 0px !important;">
				<h6 class="d-inline">Program:</h6>
				<p class="d-inline"> {{@$row['program']['name']}} ({{@$row['program']['short_name']}}) </p>

				<h6 class="d-inline ms-2">Primary care Physician:</h6>
				<p class="d-inline"> {{@$row['doctor'] ?? ''}} </p>
			</div>

			<div style="position:relative; margin-left: 0px !important;">
				<h6 class="d-inline">Date of Service:</h6>
				<p class="d-inline"> {{@$row['date_of_service']}} </p>

				<h6 class="d-inline ms-2">Next Due:</h6>
				<p class="d-inline"> {{\Carbon\Carbon::create(@$row['date_of_service'])->addYear(1)->format('m/d/Y')}} </p>
			</div>
		</div>
	</div>

	<div class="col-12" style="margin-top:50px;">
		<table class="table" style="table-layout: fixed">
			<tbody>
				{{-- PHYSICAL HEALTH STARTS --}}
				{{-- <th class="text-center" colspan="12" style="text-align: center; background: #b8daff; color: #23468c; column-span: all"> Physical health </th> --}}

				<th colspan="12" style="text-align: center; background: #b8daff; color: #23468c;">
					{{-- <span style="margin-left:10%">Physical Activity </span>
							<span style="margin-left:80%; text-align: right"> Next due </span> --}}

					<span style="display:inline; margin-left:15%">Physical Health</span>
					<span style="display:flex; float:right; margin-right:7%;">Next Due</span>

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
				<th class="text-center" colspan="12" style="text-align: center; background: #b8daff; color: #23468c; column-span: all"> Mental health </th>
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
				<th class="text-center" colspan="12" style="text-align: center; background: #b8daff; color: #23468c; column-span: all"> General health </th>
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
				<th class="text-center" colspan="12" style="text-align: center; background: #b8daff; color: #23468c; column-span: all"> Cognitive Assessment </th>
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
				<th class="text-center" colspan="12" style="text-align: center; background: #b8daff; color: #23468c; column-span: all"> Habits </th>
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
				<th class="text-center" colspan="12" style="text-align: center; background: #b8daff; color: #23468c; column-span: all"> Immunization </th>
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
				<th class="text-center" colspan="12" style="text-align: center; background: #b8daff; color: #23468c; column-span: all"> Screening </th>
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
						<strong>Next Mammogram due:</strong> {{@$screening['next_mammogram_date'] ?? ''}} <br>
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
				<th class="text-center" colspan="12" style="text-align: center; background: #b8daff; color: #23468c; column-span: all"> Metabolic Screening </th>
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
			</tbody>
		</table>
	</div>

	@if (@$row['signed_date'])
	<div class="card-body">
		<strong>
			<p class="d-inline"> Electronically signed by {{@$row['doctor']}} on {{\Carbon\Carbon::parse(@$row['signed_date'])->toDateString()}} at {{\Carbon\Carbon::parse(@$row['signed_date'])->format('g:i A')}} </p>
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
