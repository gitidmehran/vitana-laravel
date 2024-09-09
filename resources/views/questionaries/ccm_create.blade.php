@extends('layout.layout')

@section('content')
<!-- MultiStep Form -->
<div class="container-fluid mt-3">
    <div class="card">
        <form action="{{url($action)}}" method="post" class="make_ajax">
            <div class="card-header bg-success"> Add New {{$singular}}</div>
            <div class="card-body">
                <div class="row my-2">
                  <div class="col-md-6">
                    <label class="control-label">Select Patient</label>
                    <select class="form-select" name="patient_id" id="patient_id" onchange="autoFillage(this, {{!! !empty($patients) ? json_encode($patients) : '' }})">
                        <option value="">Select Patient</option>
                        @if(!empty($patients))
                        @foreach($patients as $key => $val)
                            <option value="{{$val['id']}}">{{$val['name']}}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="control-label">Select Program</label>
                    <select class="form-select" name="program_id" id="program_id">
                        <option value="">Select Programs</option>
                        @if(!empty($programs))
                        @foreach($programs as $key => $val)
                            <option value="{{$val['id']}}">{{$val['name']}}</option>
                        @endforeach
                        @endif
                    </select>
                </div>  
                </div>
                
                <div class="row my-2">
                    <div class="stepwizard" row='1'>
                        <div class="stepwizard-row setup-panel">

                            <div class="stepwizard-step">
                                <a href="#step-1" type="button" class="btn btn-primary btn-circle" disabled="disabled">1</a>
                                <p>Caregiver Assessment</p>
                            </div>

                            <div class="stepwizard-step">
                                <a href="#step-2" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled">2</a>
                                <p>Environment Assessment</p>
                            </div>

                            <div class="stepwizard-step">
                                <a href="#step-3" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled">3</a>
                                <p>Other Providers</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row my-2">
                    <div class="col-12">

                        {{-- CAREGIVER ASSESSMENT START --}}
                        <div class="row setup-content" id="step-1">
                            <h3> Caregiver Assessment </h3>       

                            <div class="form-group mb-3">
                                <label class="control-label">
                                    In the past 7 days, did you need help from others to perform every day activities such as eating, getting dressed, grooming, bathing, walking or using the toilet?
                                </label>
                                @foreach(Config('constants.agree_options') as $key => $val)
                                    <div class="form-group form-check">
                                        <input class="form-check-input tab6" name="caregiver_assessment[every_day_activities]" type="radio" value="{{$val}}" onclick="show_Adls(this)">
                                        <label class="form-check-label"> {{$val}} </label>
                                    </div>
                                @endforeach
                            </div> 

                            <div class="form-group mb-3">
                                <label class="control-label">
                                    In the past 7 days, did you need help from others to take care of things such as laundry, house-keeping, banking, shopping, using the telephone, food preparation, transportation or taking your own medications?  
                                </label>
                                @foreach(Config('constants.agree_options') as $key => $val)
                                    <div class="form-group form-check">
                                        <input class="form-check-input tab6" name="caregiver_assessment[medications]" type="radio" value="{{$val}}" onclick="show_Adls(this)">
                                        <label class="form-check-label"> {{$val}} </label>
                                    </div>
                                @endforeach
                            </div>
                                                    
                            {{-- adls Section (show if(no) on both) --}}
                        
                                <div class="form-group mb-3 d-none adls_section">
                                    <label class="control-label"> Do You have a Care giver to help take care of ADLs? </label>
                                    @foreach(Config('constants.agree_options') as $key => $val)
                                    <div class="form-group form-check-inline">
                                        <input class="form-check-input" name="caregiver_assessment[adls]" type="radio" value="{{$val}}" onclick="adls_section_Yes(this)">
                                        <label class="form-check-label"> {{$val}} </label>
                                    </div>
                                    @endforeach
                                </div>

                                {{-- Live_the_patient Section (show if yes adls)  start--}}
                        
                                <div class="form-group mb-3 d-none Live_the_patient_section">                                   
                                    <div class="form-group mb-3">

                                        <label class="control-label"> Who is your help?</label>
                                        <input class="form-check-input your_help_wife" name="caregiver_assessment[your_help_wife]" type="text" value="" >
                                    </div>
                                    
                                    <div class="form-group mb-3">

                                       <label class="control-label">Live with the patient?</label>
                                        <input class="  Live_patient" name="caregiver_assessment[Live_patient]" type="text" value="">
                                    </div>
                                    
                                </div>
                               {{-- Live_the_patient Section (show if yes adls)  end--}}

                               {{-- Live_the_patient Section (show if no adls)  start--}} 
                                     <div class="form-group mb-3 d-none adls_section_No">
                                         <label class="control-label"> Referred to Home Health? </label>
                                        @foreach(Config('constants.agree_options') as $key => $val)
                                        <div class="form-group form-check-inline">
                                            <input class="form-check-input" name="caregiver_assessment[adls_No]" type="radio" value="{{$val}}" >
                                            <label class="form-check-label"> {{$val}} </label>
                                        </div>
                                        @endforeach
                                    </div>
                                
                                {{-- Live_the_patient Section (show if no adls) End--}}
                            
                            <div class="pull-right align-items-end">
                                <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
                                    <button class="btn btn-primary nextBtn mx-2 pull-right" type="button" >Next</button>
                                </div>
                            </div>
                        </div>
                        {{-- CAREGIVER ASSESSMENT  END --}}

                       
                    </div>
                </div>
            </div>
        </form>
    </div>       
</div>
@endsection
