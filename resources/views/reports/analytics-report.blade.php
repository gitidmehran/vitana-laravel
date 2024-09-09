@extends('./layout/layout')

@section('content')
@php
    $next_assessment = \Carbon\Carbon::create($row['created_at'])->addYear(1)->format('m/Y');
    $action = 'dashboard/reports/analytics-report/savesignature';
    $row['date_of_service'] = \Carbon\Carbon::parse($row['date_of_service'])->format('m/d/Y');
    $dateofBirth = \Carbon\Carbon::parse($row['patient']['dob'])->format('m/d/Y');
@endphp

<div class="container-fluid mt-3">
    <div class="card">
		<div class="card-header">
            <div class="card-body">
                <div>
                    <h6 class="d-inline">Patient Name:</h6>
                    <p class="d-inline"> {{@$row['patient']['first_name'].' '.@$row['patient']['last_name']}}  </p>

                    <h6 class="d-inline ms-4">Date of Birth:</h6>
                    <p class="d-inline"> {{$dateofBirth}} </p>

                    <h6 class="d-inline ms-4">Age:</h6>
                    <p class="d-inline"> {{$row['patient']['age']}} </p>

                    <h6 class="d-inline ms-4">Gender:</h6>
                    <p class="d-inline"> {{$row['patient']['gender']}} </p>
                    
                    <h6 class="d-inline ms-4">Height:</h6>
                    <p class="d-inline"> {{$miscellaneous['height'] ?? ''}} </p>
                    
                    <h6 class="d-inline ms-4">Weight:</h6>
                    <p class="d-inline"> {{$miscellaneous['weight'] ?? ''}} lbs </p>
                </div>

                <div>
                    <h6 class="d-inline">Program:</h6>
                    <p class="d-inline"> {{$row['program']['name']}} </p>
                    
                    <h6 class="d-inline ms-5">Primary care Physician:</h6>
                    <p class="d-inline"> {{@$row['doctor'] ?? ''}} </p>
                </div>

                <div>
                    <h6 class="d-inline">Date of Service:</h6>
                    <p class="d-inline"> {{$row['date_of_service']}} </p>
                    
                    <h6 class="d-inline ms-5">Next Due:</h6>
                    <p class="d-inline"> {{\Carbon\Carbon::create($row['date_of_service'])->addYear(1)->format('m/d/Y')}} </p>
                </div>
            </div>
            
			<div class="card-body">
                <table class="table table-bordered table-light">
                    <tbody>

                        {{-- PHYSICAL HEALTH STARTS --}}
                        
                        <th colspan="12" class="text-center table-dark">
                            <div class="row">
                                <div class="col-10">
                                    <span style="margin-left: 21%;">Physical Activity </span>
                                </div>
                                <div class="col-2">
                                    Next due
                                </div>
                            </div>
                        </th>
                        <tr>
                            <th scope="row">Physical Health - Fall Screening</th>
                            <td colspan="2">
                                @foreach ($fall_screening as $key => $val)
                                    @if ($val != "") {{$val}} <br/> @endif
                                @endforeach
                            </td>
                            <td colspan="2">
                                @if (empty($fall_screening))
                                <i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
                                @endif
                            </td>
                            <td colspan="2">
                                @if (!empty($fall_screening))
                                {{$next_assessment}}
                                @endif
                            </td>
                        </tr>
                        {{-- PHYSICAL HEALTH ENDS --}}


                        {{-- MENTAL HEALTH STARTS --}}
                        <th colspan="12" class="text-center table-dark"> Mental health </th>
                        <tr>
                            <th scope="row">Depression PHQ-9</th>
                            <td colspan="2">
                                @foreach ($depression_out_comes as $key => $val)
                                    @if ($key != 'flag')
                                    {{$val}} <br/>
                                    @endif
                                @endforeach
                            </td>
                            <td colspan="2">
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
                        <th colspan="12" class="text-center table-dark"> General Health </th>
                        
                        <tr>
                            <th scope="row">High Stress</th>
                            <td colspan="2">{{@$high_stress['outcome'] ?? ''}}</td>
                            <td colspan="2">
                            </td>
                            <td colspan="2">
                                @if (@$high_stress['outcome'])
                                {{$next_assessment}}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">General Health</th>
                            <td colspan="2">
                                @foreach ($general_health as $key => $val)
                                    @if ($val != "" && $key != 'flag') 
                                        {{ ucfirst(str_replace('_', ' ', $key)) }}: {{$val}} <br/>
                                    @endif
                                @endforeach
                            </td>
                            <td colspan="2">
                            </td>
                            <td colspan="2">
                                @if (@$general_health['health_level'] || @$general_health['mouth_and_teeth'] || @$general_health['feelings_cause_distress'] )
                                {{$next_assessment}}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">Social/Emotional Support</th>
                            <td colspan="2">{{@$social_emotional_support['outcome'] ?? ''}}</td>
                            <td colspan="2">
                            </td>
                            <td colspan="2">
                                @if (@$social_emotional_support['outcome'])
                                {{$next_assessment}}
                                @endif
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Pain</th>
                            <td colspan="2">{{@$pain['outcome'] ?? ''}}</td>
                            <td colspan="2">
                            </td>
                            <td colspan="2">
                                @if (@$pain['outcome'])
                                {{$next_assessment}}
                                @endif
                            </td>
                        </tr>
                        {{-- GENERAL HEALTH ENDS --}}
                        
                        {{-- COGNITIVE ASSESSMENT STARTS --}}
                        <th colspan="12" class="text-center table-dark"> Cognitive Assessment </th>
                        <tr>
                            <th scope="row">Cognitive Assessment</th>
                            <td colspan="2">{{@$cognitive_assessment['outcome'] ?? ''}}</td>
                            <td colspan="2">
                                {{-- @if (@$cognitive_assessment['flag'])
                                <i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
                                @endif --}}
                            </td>
                            <td colspan="2">
                                @if (@$cognitive_assessment['outcome'])
                                {{$next_assessment}}
                                @endif
                            </td>
                        </tr>
                        {{-- COGNITIVE ASSESSMENT ENDS --}}


                        {{-- HABITS START --}}
                        <th colspan="12" class="text-center table-dark"> Habits </th>

                        <tr>
                            <th scope="row">Physical Activity</th>
                            <td>{{@$physical_out_comes['outcome'] ?? ''}}</td>
                            <td colspan="2">
                                {{-- @if (@$physical_out_comes['flag'])
                                <i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
                                @endif --}}
                            </td>
                            <td colspan="2">
                                {{$next_assessment}}
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">Alcohol Use</th>
                            <td>{{@$alcohol_out_comes['outcome'] ?? ''}}</td>
                        
                            <td colspan="2">
                                {{-- @if (@$alcohol_out_comes['flag'])
                                <i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
                                @endif --}}
                            </td>
                            <td colspan="2">
                                {{$next_assessment}}
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">Tobacco Use</th>
                            <td>
                                {{@$tobacco_out_comes['quit_tobacoo'] ?? ''}}
                                {{@$tobacco_out_comes['ldct_counseling'] ?? ''}} <br/> 
                            </td>
                            <td colspan="2">
                                @if (@$tobacco_out_comes['flag'])
                                <i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
                                @endif
                            </td>
                            <td colspan="2">
                                {{$next_assessment}}
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">Nutrition</th>
                            <td colspan="2">
                                CDC guidelines given and patient advised: <br/>
                                &bull; Vegetables 2 cups every week. <br/>
                                &bull; Fruit 1 ½ cup Equivalent per day. <br/>
                                &bull; Grain – 6 ounces eq each day. <br/>
                            </td>

                            <td colspan="2">  </td>
                            
                            <td colspan="2">
                                {{$next_assessment}}
                            </td>
                        </tr>
                        

                        <tr>
                            <th scope="row">Seat Belt Use</th>
                            <td colspan="2">{{@$seatbelt_use['outcome'] ?? ''}}</td>
                            <td colspan="2">
                                {{-- @if (@$seatbelt_use['flag'])
                                <i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
                                @endif --}}
                            </td>
                            <td colspan="2">
                                
                            </td>
                        </tr>
                        {{-- HABITS ENDS --}}
                        
                        {{-- IMMUNIZATION STARTS --}}
                        <th colspan="12" class="text-center table-dark"> Immunization </th>
                        <tr>
                            <th scope="row">Immunization</th>
                            <td colspan="2">
                                {!! !empty($immunization['flu_vaccine']) ? $immunization['flu_vaccine'].'<br>' : "" !!}
                                {!! !empty($immunization['flu_vaccine_script']) ? $immunization['flu_vaccine_script'].'<br>' : "" !!}
                                {!! !empty($immunization['pneumococcal_vaccine']) ? $immunization['pneumococcal_vaccine'].'<br>' : "" !!}
                                {!! !empty($immunization['pneumococcal_vaccine_script']) ? $immunization['pneumococcal_vaccine_script'].'<br>' : "" !!}
                            </td>
                            <td colspan="2">
                                @if (@$immunization['flag'])
                                <i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
                                @endif
                            </td>
                            <td class="col-sm-2">
                                @if (@$immunization['flu_vaccine'])
                                Next season
                                @endif
                            </td>
                        </tr>
                        {{-- IMMUNIZATION ENDS --}}
                        

                        {{-- SCREENING STARTS --}}
                        <th colspan="12" class="text-center table-dark"> Screening </th>
                        <tr>
                            <th scope="row">Mammogaram</th>
                            <td colspan="2">
                                {!! !empty($screening['mammogram']) ? $screening['mammogram'].'<br>' : "" !!}
                                {!! !empty($screening['next_mammogram']) ? $screening['next_mammogram'].'<br>' : "" !!}
                                {!! !empty($screening['mammogram_script']) ? $screening['mammogram_script'].'<br>' : "" !!}
                            </td>
                            <td colspan="2">
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
                            <th scope="row">Colon Cancer</th>
                            <td colspan="2">
                                {!! !empty($screening['colonoscopy']) ? $screening['colonoscopy'].'<br>' : "" !!}
                                {!! !empty($screening['next_colonoscopy']) ? $screening['next_colonoscopy'].'<br>' : "" !!}
                                {!! !empty($screening['colonoscopy_script']) ? $screening['colonoscopy_script'].'<br>' : "" !!}
                            </td>
                            <td colspan="2">
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
                        <th colspan="12" class="text-center table-dark"> Metabolic Screening </th>
                        <tr>
                            <th scope="row">{{$title}}</th>
                            <td colspan="2">
                                @php
                                    $keysNotReq = ['flag', 'diabetec_eye_exam', 'nepropathy', 'eye_exam_flag', 'nephropathy_flag', 'next_fbs_date', 'next_hba1c_date', 'is_diabetic'];
                                @endphp

                                @foreach ($diabetes as $key => $val)
                                    @if ($val != "" && !in_array($key, $keysNotReq)) 
                                        {{$val}} <br/> 
                                    @endif
                                @endforeach
                            </td>
                            <td colspan="2">
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
                                <th scope="row">{{$keytitle}}Eye Examination</th>
                                <td colspan="2">
                                    {{@$diabetes['diabetec_eye_exam'] ?? ''}}
                                </td>
                                <td colspan="2">
                                    @if (@$diabetes['eye_exam_flag'])
                                    <i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
                                    @endif
                                </td>
                            </tr>
                        @endif

                        @if (@$diabetes['nepropathy'] != "")
                            <tr>
                                <th scope="row">{{$keytitle}}Nephropathy</th>
                                <td colspan="2">
                                    {{@$diabetes['nepropathy'] ?? ''}}
                                </td>
                                <td colspan="2">
                                    @if (@$diabetes['nephropathy_flag'])
                                    <i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        
                        <tr>
                            <th scope="row">Cholesterol</th>
                            <td colspan="2">{{@$cholesterol_assessment['ldl_result'] ?? ''}} {{@$cholesterol_assessment['outcome'] ?? ''}}</td>
                            <td colspan="2">
                                @if (@$cholesterol_assessment['flag'])
                                <i class="fa fa-flag" aria-hidden="true" style="color: darkred"></i>
                                @endif
                            </td>
                            
                            <td colspan="2"> {{@$cholesterol_assessment['ldl_next_due'] ?? ''}} </td>
                        </tr>

                        <tr>
                            <th scope="row">BP Assessment</th>
                            <td colspan="2">{{@$bp_assessment['bp_result'] ?? ''}} {{@$bp_assessment['outcome'] ?? ''}}</td>
                            
                            <td colspan="2">
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
                            <th scope="row">Weight Assessment</th>
                            <td colspan="2">{{@$weight_assessment['bmi_result'] ?? ''}} {{@$weight_assessment['outcome'] ?? ''}}</td>
                            <td colspan="2">
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
                            <th scope="row">Physical Exam</th>
                            <td colspan="2">
                                @if (!empty($physical_exam))    
                                    @foreach ($physical_exam as $key => $value)
                                        <b>{{$key}}: </b> {{$value}}  <br>
                                    @endforeach
                                @endif
                            </td>
                            <td colspan="2">
                            </td>
                            <td colspan="2">
                            </td>
                        </tr>

                    </tbody>
                </table>
			</div>

            @if (@$row['signed_date'])
            <div class="card-body">
                <strong>
                    <p class="d-inline"> Electronically signed by {{$row['doctor']}} on {{\Carbon\Carbon::parse($row['signed_date'])->toDateString()}} at {{\Carbon\Carbon::parse($row['signed_date'])->format('g:i A')}} </p>
                </strong>
            </div>
            @else
                @if (Auth::user()->role == 2)
                <div class="card-body">
                    <form method="POST" action="{{url($action.'/'.$row['serial_no'])}}" class="make_ajax">
                            <div class="flex-row-reverse" role="group" aria-label="Basic example">
                                <button class="btn btn-success btn-md" type="submit">Sign</button>
                            </div>
                            <input type="hidden" name="questionaire_id" value="{{$row['id']}}">
                            <input type="hidden" name="questionaire_serialno" value="{{$row['serial_no']}}">
                            <input type="hidden" name="doctor_id" value="{{ Auth::check() ? Auth::user()->id :'' }}">
                        </div>
                    </form>
                </div>
                @endif
            @endif
		</div>
	</div>
</div>
@endsection