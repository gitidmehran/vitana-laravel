<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="cholesterol_assessment">
    <h3> Cholesterol Assessment</h3>

    <div class="form-group mb-3">
        <label class="control-label"> LDL Done in last 12 months? </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check-inline">
            <input class="form-check-input " name="cholesterol_assessment[ldl_in_last_12months]" type="radio" value="{{$val}}"
            @if(!empty($row['ldl_in_last_12months']) && $row['ldl_in_last_12months']==$val) checked @endif 
            onclick="showLdlandASVDsection(this)"
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    @php
    $showLDLValuesection = @$row['ldl_in_last_12months'] == 'Yes' ? '' : 'd-none';
    @endphp

    {{-- If above ldl in last 12 months is Yes --}}
    <div class="form-group mb-3 row ldlvalues_section {{$showLDLValuesection}}">
        <label class="control-label">
            LDL is  ?
        </label>
        <div class="col">
            <input type="number" min="0" step="0.01" class="form-control" name="cholesterol_assessment[ldl_value]" placeholder="LDL Result"
            value="{{$row['ldl_value'] ?? ''}}"/>
        </div>

        <div class="col">
            <input type="text" class="form-control datepicker" name="cholesterol_assessment[ldl_date]" placeholder="LDL Date"
            value="{{$row['ldl_date'] ?? ''}}"/>
        </div>
    </div>

    {{-- ASCVD Question section --}}
    <div class="ascvd_section">
        <div class="form-group mb-3">
            <label class="control-label"> Does patient have ASCVD?  </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input " name="cholesterol_assessment[patient_has_ascvd]" type="radio" value="{{$val}}"
                @if(!empty($row['patient_has_ascvd']) && $row['patient_has_ascvd']==$val) checked @endif 
                onclick="showStatinOrHypercholSection(this)"
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>


    @php
    $showHyperColesterolemia = @$row['patient_has_ascvd'] == 'No' ? '' : 'd-none';
    @endphp
    {{-- Hypercholesterolemia Section (show if ascvd is no) --}}
    <div class="hypercholesterolemia_section {{$showHyperColesterolemia}}">
        <div class="form-group mb-3">
            <label class="control-label"> Fasting or direct LDL-C ≥ 190 mg/dL? Check from result above  </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input" name="cholesterol_assessment[ldlvalue_190ormore]" type="radio" value="{{$val}}"
                @if(!empty($row['ldlvalue_190ormore']) && $row['ldlvalue_190ormore']==$val) checked @endif 
                onclick="showDibetesOrStatin()"
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>

        <div class="form-group mb-3">
            <label class="control-label"> History or active diagnosis of familial or pure hypercholesterolemia  </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input" name="cholesterol_assessment[pure_hypercholesterolemia]" type="radio" value="{{$val}}"
                @if(!empty($row['pure_hypercholesterolemia']) && $row['pure_hypercholesterolemia']==$val) checked @endif 
                onclick="showDibetesOrStatin()"
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>


    @php
    $showDiabetesSection = 'd-none';
    $ldlValue190ormore = (!empty($row['ldlvalue_190ormore']) ? $row['ldlvalue_190ormore'] : '');
    $hyperchol = (!empty($row['pure_hypercholesterolemia']) ? $row['pure_hypercholesterolemia'] : '');

    if (($ldlValue190ormore != '' && $ldlValue190ormore == 'No') || ($hyperchol != '' && $hyperchol == 'No') ) {
        $showDiabetesSection = '';
    }
    @endphp
    {{-- Cholesterol Diabetes Section (show if hypercholesterolemia is no) --}}
    <div class="cholesterol_diabetes_section {{$showDiabetesSection}}">
        <div class="form-group mb-3">
            <label class="control-label"> Does Patient have active diagnosis of diabetes?  </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input" name="cholesterol_assessment[active_diabetes]" type="radio" value="{{$val}}"
                @if(!empty($row['active_diabetes']) && $row['active_diabetes']==$val) checked @endif 
                onclick="askPatientage(this)"
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>


        @php
        $showPatientAge = 'd-none';
        if (!empty($row['active_diabetes']) && $row['active_diabetes'] == 'Yes') {
            $showPatientAge = '';
        }
        @endphp
        {{-- If above diabetes question is yes --}}
        <div class="form-group mb-3 patient_agesection {{$showPatientAge}}">
            <label class="control-label"> Patient age between 40-75 years? </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input" name="cholesterol_assessment[diabetes_patient_age]" type="radio" value="{{$val}}"
                @if(!empty($row['diabetes_patient_age']) && $row['diabetes_patient_age']==$val) checked @endif 
                onclick="lastTwoyearsStatin(this)"
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>



        @php
        $showLDLinTwoYears = 'd-none';
        if (!empty($row['diabetes_patient_age']) && $row['diabetes_patient_age'] == 'Yes') {
            $showLDLinTwoYears = '';
        }
        @endphp
        {{-- If above patient age question is yes --}}
        <div class="form-group mb-3 last_two_yearsLDL {{$showLDLinTwoYears}}">
            <label class="control-label"> Fasting or Direct LDL-C 70-189 mg/dL any time in past two years (2020-2022)? </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input" name="cholesterol_assessment[ldl_range_in_past_two_years]" type="radio" value="{{$val}}"
                @if(!empty($row['ldl_range_in_past_two_years']) && $row['ldl_range_in_past_two_years']==$val) checked @endif 
                onclick="prescribedStatin($(this).val())"
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>


    @php
    $showStatinQuestion = 'd-none';

    $patienthasAscvd = (!empty($row['patient_has_ascvd']) && $row['patient_has_ascvd'] == 'Yes' ? true : false);
    $ldl190_ormore = (!empty($row['ldlvalue_190ormore']) && $row['ldlvalue_190ormore'] == 'Yes' ? true : false);
    $pureHypercholeterolemia = (!empty($row['pure_hypercholesterolemia']) && $row['pure_hypercholesterolemia'] == 'Yes' ? true : false);
    $ldlinPasttwoYears = (!empty($row['ldl_range_in_past_two_years']) && $row['ldl_range_in_past_two_years'] == 'Yes' ? true : false);

    if ($patienthasAscvd || $ldl190_ormore || $pureHypercholeterolemia || $ldlinPasttwoYears) {
        $showStatinQuestion = '';
    }
    @endphp

    {{-- Statin question section --}}
    <div class="statin_question_section {{$showStatinQuestion}}">
        <div class="form-group mb-3">
            <label class="control-label"> Was the patient prescribed any high or moderate intensity statin in the current calendar year?  </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input " name="cholesterol_assessment[statin_prescribed]" type="radio" value="{{$val}}"
                @if(!empty($row['statin_prescribed']) && $row['statin_prescribed']==$val) checked @endif 
                onclick="showStatinDosage(this)"
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>


    @php
    $showStatinDosage = 'd-none';
    if (!empty($row['statin_prescribed']) && $row['statin_prescribed'] == 'Yes') {
        $showStatinDosage = '';
    }
    @endphp
    {{-- Show if above question about statin usage is YES --}}
    <div class="statin_dosage_section {{$showStatinDosage}}">
        <div class="mb-3">
            <label class="control-label fs-5"> Statin Type and dosage </label>
        </div>

        <div class="row">
            <div class="col-md-2">
                <label class=""> <b> Statin </b> </label> <br>
                @foreach (Config('constants.statin_name') as $key => $statin_name)
                <label class="form-check-label"> {{$statin_name}} </label> <br>
                @endforeach
            </div>
            <div class="col-md-4">
                <label>
                    <b>
                        Moderate-intensity
                        (LDL-C reduction 30% to < 50%)
                    </b>    
                </label>

                <div class="form-group mb-3">
                    @foreach (Config('constants.moderate_intensity_statin') as $key => $moderate_dosage)
                    <input class="form-check-input " name="cholesterol_assessment[statintype_dosage]" type="radio" value="{{$key}} {{$moderate_dosage}}"
                    @if(!empty($row['statintype_dosage']) && $row['statintype_dosage']==$key.' '.$moderate_dosage ) checked @endif 
                    />
                    <label class="form-check-label"> {{$moderate_dosage}} </label> <br>
                    @endforeach 
                </div>
            </div>
            <div class="col-md-4">
                <label>
                    <b>
                        High-intensity
                        (LDL-C reduction >50%)
                    </b>    
                </label>
                <div class="form-group mb-3">
                    @foreach (Config('constants.high_intensity_statin') as $key => $high_dosage)
                    @if ($high_dosage != "")
                    <input class="form-check-input " name="cholesterol_assessment[statintype_dosage]" type="radio" value="{{$key}} {{$high_dosage}}">
                    <label class="form-check-label"> {{$high_dosage}} </label> <br>
                    @else
                    <label class="form-check-label text-muted"> NA </label>
                    <br>
                    @endif
                    @endforeach 
                </div>
            </div>
        </div>
    </div>


    @php
    $showReasonfornoStatin = 'd-none';
    if (!empty($row['statin_prescribed']) && $row['statin_prescribed'] == 'No') {
        $showReasonfornoStatin = '';
    }

    @endphp
    {{-- Medical reason for no statin --}}
    <div class="medical_reasonforstatin_section {{$showReasonfornoStatin}}">
        <label class="control-label"> Documented medical reason for not being on statin therapy is: </label>
        <div class="form-group mb-3">
            @foreach(Config('constants.statin_medical_reason') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input " name="cholesterol_assessment[medical_reason_for_nostatin{{$key}}]" type="checkbox" value="{{$val}}"
                @if(!empty($row['medical_reason_for_nostatin'.$key]) && $row['medical_reason_for_nostatin'.$key] == $val) checked @endif                                            
                >
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>

    <div class="pull-right align-items-end">
        <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
            <button class="btn btn-primary nextBtn mx-2" type="button" >Next</button>
            <button class="btn btn-primary prevBtn" type="button">Previous</button>
        </div>
    </div>
</div>