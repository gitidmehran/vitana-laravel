<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="diabetes_mellitus">
    <h3> Diabetes</h3>
    <div class="row mt-3 mb-3">
        <label class="mb-3">When was your last HbA1c test performed and what was the result?</label>
        <div class="form-group col-6">
            <input type="number" step="0.01" placeholder="HbA1c result" class="form-control" oninput="lastHbaResult(this)" name="diabetes_mellitus[hb_result]" value="{{@$row['hb_result']}}">
        </div>
        <div class="form-group col-6">
            <input type="text" class="form-control datepicker" onchange="lastHbaDate(this)" name="diabetes_mellitus[result_month]" value="{{@$row['result_month']}}" placeholder="Result month" autocomplete="off" />
        </div>
    </div>


    <div class="form-group mb-3 eye_examination d-none">
        <label class="control-label mb-3">
            Have you had a Diabetic Eye Examination in last 12 months?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check-inline">
            <input class="form-check-input tab6" name="diabetes_mellitus[eye_examination]" @if(!empty($row['eye_examination']) && $row['eye_examination']==$val) checked @endif onclick="eyeExamination(this)" type="radio" value="{{$val}}" />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach

        <div class="form-group mb-3">
            <label class="control-label">
                Have you had a Diabetic Nephropathy screening in the last 6 months?
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input tab6" name="diabetes_mellitus[diabetic_nephropathy]" @if(!empty($row['diabetic_nephropathy']) && $row['diabetic_nephropathy']==$val) checked @endif onclick="diabeticNephropathy(this)" type="radio" value="{{$val}}" />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>

        <div class="row eye_examination_detail d-none mb-3">
            <h6>Eye Examination Detail</h6>
            <div class="form-group col-4">
                <input type="text" placeholder="Name of Doctor" class="form-control" name="diabetes_mellitus[name_of_doctor]" value="{{@$row['name_of_doctor']}}">
            </div>
            <div class="form-group col-4">
                <input type="text" placeholder="Facility" class="form-control" name="diabetes_mellitus[name_of_facility]" value="{{@$row['name_of_facility']}}">
            </div>
            <div class="form-group col-4">
                <input type="text" class="form-control datepicker" class="form-control" name="diabetes_mellitus[checkup_date]" placeholder="Date" autocomplete="off" value="{{@$row['checkup_date']}}" />
            </div>


            <div class="mt-3">
                <label class="control-label">
                    Report Available
                </label>
                @foreach(Config('constants.agree_options') as $key => $val)
                <div class="form-group form-check-inline">
                    <input class="form-check-input tab6" name="diabetes_mellitus[report_available]" @if(!empty($row['report_available']) && $row['report_available']==$val) checked @endif onclick="diabeticReport(this)" type="radio" value="{{$val}}" />
                    <label class="form-check-label"> {{$val}} </label>
                </div>
                @endforeach
            </div>
        </div>


        <div class="diabetic_report_requested d-none mt-3">
            <label class="control-label">
                Report Requested
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input tab6" name="diabetes_mellitus[report_requested]" @if(!empty($row['report_requested']) && $row['report_requested']==$val) checked @endif type="radio" value="{{$val}}" />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>




    <div class="row no_eye_examination d-none mb-3">
        <div class="col-6">
            <label class="control-label">
                Retinavue ordered
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input tab6" name="diabetes_mellitus[retinavue_ordered]" @if(!empty($row['retinavue_ordered']) && $row['retinavue_ordered']==$val) checked @endif type="radio" value="{{$val}}" />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>

        <div class="mt-3 col-6">
            <label class="control-label">
                Script given for eye examination
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input tab6" name="diabetes_mellitus[eye_examination_script]" @if(!empty($row['eye_examination_script']) && $row['eye_examination_script']==$val) checked @endif type="radio" value="{{$val}}" />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>


    <div class="diabetic_nephropathy_date d-none  mb-3 row">
        <h6>Nephropathy Screening</h6>
        <div class="form-group col-6">
            <input type="text" class="form-control datepicker" name="diabetes_mellitus[diabetic_nephropathy_date]" value="{{@$row['diabetic_nephropathy_date']}}" placeholder="Nephropathy Test Date">
        </div>
        <div class="form-group mb-3 mt-3 col-6">
            <label class="control-label">
                Test Result:
            </label>
            @foreach(Config('constants.urine_microalbumin_report') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input tab6" name="diabetes_mellitus[diabetic_nephropathy_result]" @if(!empty($row['diabetic_nephropathy_result']) && $row['diabetic_nephropathy_result']==$val) checked @endif type="radio" value="{{$val}}" />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>


    <div class="no_diabetic_nephropathy d-none mb-3">
        <div class="form-group mb-3 mt-3">
            <div class="form-group form-check-inline">
                <input class="form-check-input tab6" name="diabetes_mellitus[diabetic_nephropathy_not_conducted]" @if(!empty($row['diabetic_nephropathy_not_conducted']) && $row['diabetic_nephropathy_not_conducted']==$val) checked @endif onclick="nephropathyNotConducted(this)" type="radio" value="script_generated_for_urine_for_microalbumin" />
                <label>Script generated for Urine for Micro-albumin</label>
                <input class="form-check-input tab6" name="diabetes_mellitus[diabetic_nephropathy_not_conducted]" @if(!empty($row['diabetic_nephropathy_not_conducted']) && $row['diabetic_nephropathy_not_conducted']==$val) checked @endif onclick="nephropathyNotConducted(this)" type="radio" value="patient_refused_urine_for_microalbuminemia_testing" />
                <label>Patient refused urine for Microalbuminemia testing</label>
                @foreach(Config('constants.inhibitor') as $key => $val)
                <input class="form-check-input tab6" name="diabetes_mellitus[diabetic_nephropathy_not_conducted]" @if(!empty($row['diabetic_nephropathy_not_conducted']) && $row['diabetic_nephropathy_not_conducted']==$val) checked @endif  onclick="nephropathyNotConducted(this)" type="radio" value="{{$val}}" />
                <label class="form-check-label"> {{$val}} </label>
                @endforeach
            </div>
        </div>
    </div>








    <div class="nephropathy_options_none d-none mb-3">
        <div class="form-group mb-3 mt-3 col-6">
            <label class="control-label">
                Does patient has:
            </label>
            @foreach(Config('constants.ckd_stage_4_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input tab6" name="diabetes_mellitus[nephropathy_patient_has]" @if(!empty($row['nephropathy_patient_has']) && $row['nephropathy_patient_has']==$val) checked @endif type="radio" value="{{$val}}" />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>


    <!-- Goals Section -->
    <div class="row mb-3">
        <div class="col-8">
            <h6>Goals</h6>
        </div>
        <div class="col-2">
            <h6>Start Date</h6>
        </div>
        <div class="col-2">
            <h6>End Date</h6>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>
                To understand the importance of Blood Glucose Monitoring and control
            </p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="diabetes_mellitus[imp_blood_glucose_start_date]" value="{{@$row['imp_blood_glucose_start_date']}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control end_date" name="diabetes_mellitus[imp_blood_glucose_end_date]" value="{{@$row['imp_blood_glucose_end_date']}}" placeholder="End Date" autocomplete="off" />
        </div>

    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>
                To Understand Hypoglycemia, hyperglycemia and how to prevent them
            </p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="diabetes_mellitus[und_hypoglycemia_hyperglycemia_start_date]" value="{{@$row['und_hypoglycemia_hyperglycemia_start_date']}}" placeholder="Start Date" autocomplete="off" />
        </div> 
        <div class="form-group col-2">
            <input type="text" class="form-control end_date" name="diabetes_mellitus[und_hypoglycemia_hyperglycemia_end_date]" value="{{@$row['und_hypoglycemia_hyperglycemia_end_date']}}" placeholder="End Date" autocomplete="off" />
        </div>

    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>
                To recognize the signs and symptoms of exacerbation that must be reported to the
                doctor/nurse
            </p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="diabetes_mellitus[recognize_signs_symptoms_start_date]" value="{{@$row['recognize_signs_symptoms_start_date']}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control end_date" name="diabetes_mellitus[recognize_signs_symptoms_end_date]" value="{{@$row['recognize_signs_symptoms_end_date']}}" placeholder="End Date" autocomplete="off" />
        </div>

    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>
                To reduce the risk of complications and prevent future health problems
            </p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="diabetes_mellitus[reduce_complications_start_date]" value="{{@$row['reduce_complications_start_date']}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control end_date" name="diabetes_mellitus[reduce_complications_end_date]" value="{{@$row['reduce_complications_end_date']}}" placeholder="End Date" autocomplete="off" />
        </div>

    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>
                To understand the importance of quitting Smoking to reduce the risk of complications
            </p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="diabetes_mellitus[und_imp_of_quit_smoking_start_date]" value="{{@$row['und_imp_of_quit_smoking_start_date']}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control end_date" name="diabetes_mellitus[und_imp_of_quit_smoking_end_date]" value="{{@$row['und_imp_of_quit_smoking_end_date']}}" placeholder="End Date" autocomplete="off" />
        </div>

    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>
                To maintain a healthy Weight
            </p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="diabetes_mellitus[maintain_healthy_weight_start_date]" value="{{@$row['maintain_healthy_weight_start_date']}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control end_date" name="diabetes_mellitus[maintain_healthy_weight_end_date]" value="{{@$row['maintain_healthy_weight_end_date']}}" placeholder="End Date" autocomplete="off" />
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>
                To engage in 150 minutes of moderate intensity physical activity per week
            </p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="diabetes_mellitus[engage_physical_activity_start_date]" value="{{@$row['engage_physical_activity_start_date']}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control end_date" name="diabetes_mellitus[engage_physical_activity_end_date]" value="{{@$row['engage_physical_activity_end_date']}}" placeholder="End Date" autocomplete="off" />
        </div>

    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>
                To maintain a healthy diet for managing diabetes
            </p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="diabetes_mellitus[maintain_a_healthy_diet_start_date]" value="{{@$row['maintain_a_healthy_diet_start_date']}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control end_date" name="diabetes_mellitus[maintain_a_healthy_diet_end_date]" value="{{@$row['maintain_a_healthy_diet_end_date']}}" placeholder="End Date" autocomplete="off" />
        </div>

    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>
                To develop an understanding of Diabetic Foot care
            </p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="diabetes_mellitus[und_foot_care_start_date]" value="{{@$row['und_foot_care_start_date']}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control end_date" name="diabetes_mellitus[und_foot_care_end_date]" value="{{@$row['und_foot_care_end_date']}}" placeholder="End Date" autocomplete="off" />
        </div>
    </div>

    <div class="pull-right align-items-end">
        <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
            @if(isset($end) && $end==true)
            <button class="btn btn-primary mx-2" type="submit">Finish!</button>
            <button class="btn btn-primary prevBtn" type="button">Previous</button>
            @else
            <button class="btn btn-primary nextBtn mx-2" type="button">Next</button>
            <button class="btn btn-primary prevBtn" type="button">Previous</button>
            @endif
        </div>
    </div>
</div>