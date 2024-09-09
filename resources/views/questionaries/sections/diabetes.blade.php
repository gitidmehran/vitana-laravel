<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="diabetes">
    <h3> Diabetes</h3>

    <div class="form-group mb-3">
        <label class="control-label">
           Does the patient have an active diagnosis of diabetes?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="diabetes[diabetec_patient]" type="radio" value="{{$val}}"
            @if(!empty($row['diabetec_patient']) && $row['diabetec_patient']==$val) checked @endif
            onclick="diabeticpatientCheck(this)" />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    <div class="fbs_last12_moths {!! empty($row['diabetec_patient']) || $row['diabetec_patient'] == 'Yes' ? 'd-none' : '' !!}">
        <div class="form-group mb-3">
            <label class="control-label">
                FBS done in last 12 months ?
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check">
                <input class="form-check-input tab6" name="diabetes[fbs_in_year]" type="radio" value="{{$val}}" 
                @if(!empty($row['fbs_in_year']) && $row['fbs_in_year']==$val) checked @endif
                onclick="diabeteseFbsdata(this)">
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>


        <div class="diabetes_fbs_data {!! empty($row['fbs_in_year']) || $row['fbs_in_year'] == 'No' ? 'd-none' : '' !!}">
            <div class="form-group mb-3">
                <label class="control-label">
                    Fasting Blood Sugar (FBS)
                </label>
                <input type="number" min="0" class="form-control fbsvalue" name="diabetes[fbs_value]" 
                placeholder="Fasting Blood Sugar"
                value="{{$row['fbs_value'] ?? ''}}"
                onkeyup="diabetesHba1cdata(this)"/>
            </div>

            <div class="form-group mb-3">
                <label class="control-label">
                    Fasting Blood Sugar (FBS) date
                </label>
                <input type="text" class="form-control datepicker" name="diabetes[fbs_date]" placeholder="FBS Date"
                value="{{$row['fbs_date'] ?? ''}}"/>
            </div>
        </div>
    </div>

    @php
    $hba1c_dnone = 'd-none';
    $fbs_Value = !empty($row['fbs_value']) ? (int)$row['fbs_value'] : '';
    $active_diagnoses = !empty($row['diabetec_patient']) ? $row['diabetec_patient'] : '';

    if (($fbs_Value != '' && $fbs_Value > 100 && $active_diagnoses == 'No') || $active_diagnoses == 'Yes') {
        $hba1c_dnone = '';
    }
    @endphp

    <div class="diabeted_hba1c_data {{$hba1c_dnone}}">
        <div class="form-group mb-3">
            <label class="control-label">
                HBA1C Result
            </label>
            <input type="text" class="form-control hba1cvalue" name="diabetes[hba1c_value]" placeholder="HBA1C Result"
            value="{{$row['hba1c_value'] ?? ''}}"
            onkeyup="diabetesHba1cdata(this)"/>
        </div>

        <div class="form-group mb-3">
            <label class="control-label">
                HBA1C Date
            </label>
            <input type="text" class="form-control datepicker" name="diabetes[hba1c_date]" placeholder="HBA1C Date"
            value="{{$row['hba1c_date'] ?? ''}}"/>
        </div>
    </div>


    @php
    $eyeExam_dnone = $nephropathy_dnone = 'd-none';
    $hba1cValue = !empty($row['hba1c_value']) ? (float)$row['hba1c_value'] : '';

    if ($hba1cValue != "" && $hba1cValue >= 6.5) {
        $eyeExam_dnone = $nephropathy_dnone = '';
    }

    @endphp

    <div class="form-group mb-3 mt-2 eye_examintaion {{$eyeExam_dnone}}">
        <h5> Eye Examination </h5>
        <label class="control-label">
            Diabetic Eye Examination in last 12 months ?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="diabetes[diabetec_eye_exam]" type="radio" 
            value="{{$val}}" 
            @if(!empty($row['diabetec_eye_exam']) && $row['diabetec_eye_exam']==$val) checked @endif
            onclick="eyeExamReport(this)" />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    @php
    $eye_exam_ordered = 'd-none';
    $eyeExam_done = !empty($row['diabetec_eye_exam']) ? $row['diabetec_eye_exam'] : '';
    if ($eyeExam_done == 'No') {
        $eye_exam_ordered = '';
    }
    @endphp

    <div class="eye_exam_order_section {{$eye_exam_ordered}}">
        <div class="form-group mb-3">
            <div class="form-group form-check-inline">
                <input class="form-check-input" name="diabetes[ratinavue_ordered]" 
                type="radio" value="Yes"
                @if(!empty($row['ratinavue_ordered']) && $row['ratinavue_ordered']== 'Yes') checked @endif
                />
                <label class="form-check-label"> Ratinavue Ordered </label>
            </div>

            <div class="form-group form-check-inline">
                <input class="form-check-input" name="diabetes[ratinavue_ordered]" 
                type="radio" value="No"
                @if(!empty($row['ratinavue_ordered']) && $row['ratinavue_ordered']== 'No') checked @endif
                />
                <label class="form-check-label"> Script given for Eye Examination </label>
            </div>
        </div>
    </div>

    @php
    $eyeExamReport = 'd-none';
    if ($eyeExam_done == 'Yes') {
        $eyeExamReport = '';
    }
    @endphp

    <div class="form-group mb-3 eye_exam_repot {{$eyeExamReport}}">
        @foreach(Config('constants.diabetec_eye_exam_report') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="diabetes[diabetec_eye_exam_report]" type="radio" 
            value="{{$val}}" 
            @if(!empty($row['diabetec_eye_exam_report']) && $row['diabetec_eye_exam_report']==$val) checked @endif
            onclick="eyeExamReportData(this)">
            <label class="form-check-label"> {{$key}} </label>
        </div>
        @endforeach
    </div>

    <div class="eye_exam_report_data {!! empty($row['diabetec_eye_exam_report']) || $row['diabetec_eye_exam_report'] != 'report_available' ? 'd-none' : '' !!}">
        <div class="form-group mb-3">
            <input type="text" class="form-control mt-2" name="diabetes[eye_exam_doctor]" placeholder="Name of Doctor"
            value="{{$row['eye_exam_doctor'] ?? ''}}"/>

            <input type="text" class="form-control mt-2" name="diabetes[eye_exam_facility]" placeholder="Facility"
            value="{{$row['eye_exam_facility'] ?? ''}}"/>


            <input type="text" class="form-control mt-2 datepicker" name="diabetes[eye_exam_date]" placeholder="Eye exam date"
            value="{{$row['eye_exam_date'] ?? ''}}"/>
        </div>

        <div class="form-group mb-3">
            <input class="form-check-input" type="checkbox" value="1" 
            name="diabetes[eye_exam_report_reviewed]" id="flexCheckDefault" 
            @if(!empty(@$row['eye_exam_report_reviewed'])) checked @endif
            onchange="checkDiabetecRatipathy(this)">
            <label class="form-check-label"> Report reviewed</label>
        </div>

        <div class="form-group mb-3 diabetec_retinopath {!! empty($row['eye_exam_report_reviewed']) ? 'd-none' : '' !!}">
            <label class="form-check-label"> Report Shows Diabetic Retinopathy</label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input tab6" name="diabetes[diabetec_ratinopathy]" 
                @if(!empty($row['diabetec_ratinopathy']) && $row['diabetec_ratinopathy']==$val) checked @endif
                type="radio" value="{{$val}}" />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>


    <div class="form-group mb-3 nephropathy {{$nephropathy_dnone}}">
        <h5> Nephropathy </h5>
        <label class="control-label">
            Urine for microalbumin in last 6 months
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="diabetes[urine_microalbumin]" 
            type="radio" value="{{$val}}"
            @if(!empty($row['urine_microalbumin']) && $row['urine_microalbumin']==$val) checked @endif 
            onclick="urineMicroalbumin(this)" />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    @php
        $urineMicroalbumin_showhide = (@$row['urine_microalbumin'] == 'Yes' ? '' : 'd-none');
    @endphp

    <div class="form-group mb-3 urine_microalbumin_section {{$urineMicroalbumin_showhide}}">
        <input type="text" class="form-control mt-2 datepicker" name="diabetes[urine_microalbumin_date]" placeholder="Urine for Microalbumin date"
        value="{{$row['urine_microalbumin_date'] ?? ''}}"/>
    </div>

    <div class="form-group mb-3 urine_microalbumin_section {{$urineMicroalbumin_showhide}}">
        @foreach(Config('constants.urine_microalbumin_report') as $key => $val)
        <div class="form-group form-check-inline">
            <input class="form-check-input" name="diabetes[urine_microalbumin_report]" type="radio" value="{{$val}}"
            @if(!empty($row['urine_microalbumin_report']) && $row['urine_microalbumin_report']==$val) checked @endif 
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    <div class="declined_urine_microalbumin {!! empty($row['urine_microalbumin']) || $row['urine_microalbumin'] == 'Yes' ? 'd-none' : '' !!}">

        <div class="form-group mb-3">
            <label class="control-label">
                Urine for Micro-albumin ordered
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input tab6" name="diabetes[urine_microalbumin_ordered]" type="radio" 
                value="{{$val}}"
                @if(!empty($row['urine_microalbumin_ordered']) && $row['urine_microalbumin_ordered']==$val) checked @endif 
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>

        <div class="form-group mb-3">
            <label class="control-label">
                Does patient use
            </label>
            @foreach(Config('constants.inhibitor') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input tab6" name="diabetes[urine_microalbumin_inhibitor]" type="radio" value="{{$val}}" 
                @if(!empty($row['urine_microalbumin_inhibitor']) && $row['urine_microalbumin_inhibitor']==$val) checked @endif 
                onclick="inhibitorsData(this)"/>
                <label class="form-check-label"> {{$key}} </label>
            </div>
            @endforeach
        </div>
    </div>

    <div class="form-group mb-3 ckd_stage4_data {!! empty($row['urine_microalbumin_inhibitor']) || $row['urine_microalbumin_inhibitor'] != 'none' ? 'd-none' : '' !!}">
        <label class="control-label">
            Does patient has
        </label>
        @foreach(Config('constants.ckd_stage_4_options') as $key => $val)
        <div class="form-group form-check-inline">
            <input class="form-check-input tab6" name="diabetes[ckd_stage_4]" type="radio" value="{{$val}}"
            @if(!empty($row['ckd_stage_4']) && $row['ckd_stage_4']==$val) checked @endif 
            />
            <label class="form-check-label"> {{$key}} </label>
        </div>
        @endforeach
    </div>

    <div class="pull-right align-items-end">
        <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
            <button class="btn btn-primary nextBtn mx-2" type="button" >Next</button>
            <button class="btn btn-primary prevBtn" type="button">Previous</button>
        </div>
    </div>
</div>