<div class="row setup-content ckd_assesment" id="step-{{$stepNo ?? '1'}}" data-type="ckd_assesment">
    <h3> Chronic Kidney Disease</h3>
    {{-- BLOOD PRESSURE RESULT SECTION --}}
    <div class="container mb-3 mt-3">
        <label class="control-label">
            If you have been monitoring your Blood pressure, please tell me the readings from the last three days.
        </label>
        
        @if (!empty($row['bp']))
            @foreach ($row['bp'] as $key => $item)
                <div class="row form-group mb-3">
                    <div class="row col-3 ms-2">
                        <input type="text" class="form-control start_date" name="ckd_assesment[bp][{{$key}}][bp_day]" placeholder="Date" value="{{@$item['bp_day'] ?? ""}}" />
                    </div>

                    <div class="row col-3 ms-2">
                        <input type="number" min="0" class="form-control" name="ckd_assesment[bp][{{$key}}][systolic_day]" placeholder="Systolic" value="{{@$item['systolic_day'] ?? ''}}" />
                    </div>

                    <div class="row col-3 ms-2">
                        <input type="number" min="0" class="form-control" name="ckd_assesment[bp][{{$key}}][diastolic_day]" placeholder="Diastolic" value="{{@$item['diastolic_day'] ?? ''}}" />
                    </div>

                    <div class="row col-2 ms-2">
                        <div class="pull-left align-items-end col-3">
                            <button class="btn btn-danger mx-2" type="button" onclick="removethisRow(this)">X</button>
                        </div>
                    </div>
                </div>
            @endforeach
        @else    
            @for ($i = 1; $i <= 3; $i++)
                <div class="row form-group mb-3">
                    <div class="row col-3 ms-2">
                        <input type="text" class="form-control start_date" name="ckd_assesment[bp][{{$i}}][bp_day]" placeholder="Date" value="{{@$row['bp_day'] ?? ""}}" />
                    </div>

                    <div class="row col-3 ms-2">
                        <input type="number" min="0" class="form-control" name="ckd_assesment[bp][{{$i}}][systolic_day]" placeholder="Systolic" value="{{@$row['systolic_day'] ?? ''}}" />
                    </div>

                    <div class="row col-3 ms-2">
                        <input type="number" min="0" class="form-control" name="ckd_assesment[bp][{{$i}}][diastolic_day]" placeholder="Diastolic" value="{{@$row['diastolic_day'] ?? ''}}" />
                    </div>

                    <div class="row col-2 ms-2">
                        <div class="pull-left align-items-end col-3">
                            <button class="btn btn-danger mx-2" type="button" onclick="removethisRow(this)">X</button>
                        </div>
                    </div>
                </div>
            @endfor
        @endif
        
        <div class="row mb-3">
            <div class="pull-right align-items-end col-3">
                <button class="btn btn-success mx-2" type="button" onclick="addnewRow(this)">Add more</button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-6">
            <input type="number" readonly step="0.01" class="form-control" placeholder="HbA1c" name="ckd_assesment[hba1c]" value="{{@$row['hba1c'] ?? ""}}" >
        </div>
    </div>


    <label class="mb-3 mt-3">
        Mention the results of eGFR tests performed on two occasions within the last 12 months
    </label>

    <h6>First Test</h6>
    <div class="row mb-3">
        <div class="form-group col-6">
            <input type="text" class="form-control datepicker_simple" name="ckd_assesment[egfr_result_one_start_date]" value="{{@$row['egfr_result_one_start_date'] ?? ""}}" placeholder="Date" autocomplete="off" />
        </div>
        <div class="form-group col-6">
            <input type="number" class="form-control" name="ckd_assesment[egfr_result_one_report]" value="{{@$row['egfr_result_one_report'] ?? ""}}" placeholder="Result">
        </div>
    </div>

    <h6>Second Test</h6>
    <div class="row mb-3">
        <div class="form-group col-6">
            <input type="text" class="form-control datepicker_simple" name="ckd_assesment[egfr_result_two_start_date]" value="{{@$row['egfr_result_two_start_date'] ?? ""}}" placeholder="Date" autocomplete="off" />
        </div>
        <div class="form-group col-6">
            <input type="number" class="form-control" name="ckd_assesment[egfr_result_two_report]" value="{{@$row['egfr_result_two_report'] ?? ""}}" placeholder="Result">
        </div>
    </div>

    <div class=" row mb-3">
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
            <p>To acquire knowledge about CKD and how it can affect you</p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="ckd_assesment[ak_ckd_start_date]" value="{{@$row['ak_ckd_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="col-2">
            <input type="text" class="form-control end_date" name="ckd_assesment[ak_ckd_end_date]" value="{{@$row['ak_ckd_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off" />
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>To understand the relationship between chronic kidney disease and cardiovascular risks</p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="ckd_assesment[ur_kidney_disease_start_date]" value="{{@$row['ur_kidney_disease_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="col-2">
            <input type="text" class="form-control end_date" name="ckd_assesment[ur_kidney_disease_end_date]" value="{{@$row['ur_kidney_disease_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off" />
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>To understand the impact of high blood pressure on progression of CKD</p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="ckd_assesment[uih_bp_start_date]" value="{{@$row['uih_bp_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="col-2">
            <input type="text" class="form-control end_date" name="ckd_assesment[uih_bp_end_date]" value="{{@$row['uih_bp_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off" />
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>To recognize the importance of adopting lifestyle modifications to mitigate risk of complications</p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="ckd_assesment[ria_mitigate_risk_start_date]" value="{{@$row['ria_mitigate_risk_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="col-2">
            <input type="text" class="form-control end_date" name="ckd_assesment[ria_mitigate_risk_end_date]" value="{{@$row['ria_mitigate_risk_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off" />
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>To understand the importance of smoking cessation</p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="ckd_assesment[ui_smoking_cessation_start_date]" value="{{@$row['ui_smoking_cessation_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="col-2">
            <input type="text" class="form-control end_date" name="ckd_assesment[ui_smoking_cessation_end_date]" value="{{@$row['ui_smoking_cessation_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off" />
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>To understand the importance of daily blood pressure monitoring and maintaining a normal blood pressure.</p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="ckd_assesment[uidbp_normalbp_start_date]" value="{{@$row['uidbp_normalbp_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="col-2">
            <input type="text" class="form-control end_date" name="ckd_assesment[uidbp_normalbp_end_date]" value="{{@$row['uidbp_normalbp_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off" />
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>To recognize the importance of discipline in taking all medications as prescribed</p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="ckd_assesment[rid_medication_start_date]" value="{{@$row['rid_medication_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="col-2">
            <input type="text" class="form-control end_date" name="ckd_assesment[rid_medication_end_date]" value="{{@$row['rid_medication_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off" />
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>To adopt dietary modifications suggested by the PCP/nephrologist</p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="ckd_assesment[adm_dietary_start_date]" value="{{@$row['adm_dietary_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="col-2">
            <input type="text" class="form-control end_date" name="ckd_assesment[adm_dietary_end_date]" value="{{@$row['adm_dietary_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off" />
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>To maintain blood sugars in a healthy range, if you have diabetes</p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="ckd_assesment[mbshr_diabetes_start_date]" value="{{@$row['mbshr_diabetes_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="col-2">
            <input type="text" class="form-control end_date" name="ckd_assesment[mbshr_diabetes_end_date]" value="{{@$row['mbshr_diabetes_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off" />
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>To maintain a healthy Weight</p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="ckd_assesment[tmh_weight_start_date]" value="{{@$row['tmh_weight_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="col-2">
            <input type="text" class="form-control end_date" name="ckd_assesment[tmh_weight_end_date]" value="{{@$row['tmh_weight_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off" />
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>To help the patient design a personalized strategy to manage and influence their own condition</p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="ckd_assesment[thp_strategy_start_date]" value="{{@$row['thp_strategy_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="col-2">
            <input type="text" class="form-control end_date" name="ckd_assesment[thp_strategy_end_date]" value="{{@$row['thp_strategy_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off" />
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-8">
            <p>To help the patient people cope with and adjust to CKD and find sources of psychological support</p>
        </div>
        <div class="form-group col-2">
            <input type="text" class="form-control start_date" name="ckd_assesment[thp_adjust_ckd_start_date]" value="{{@$row['thp_adjust_ckd_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off" />
        </div>
        <div class="col-2">
            <input type="text" class="form-control end_date" name="ckd_assesment[thp_adjust_ckd_end_date]" value="{{@$row['thp_adjust_ckd_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off" />
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
</div>

<script type="text/javascript">
    setTimeout(function() {
        $(document).ready(function() {
            var hba1cResult = $('input[name="diabetes[hb_result]"]').val();
            console.log(hba1cResult)
            $('input[name="ckd_assesment[hba1c]" ]').val(hba1cResult);
        })
    }, 100);
</script>