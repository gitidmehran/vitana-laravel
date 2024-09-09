<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="hypertension">

    <h3>Hypertension</h3>

    {{-- BLOOD PRESSURE RESULT SECTION --}}
    <div class="container mb-3 mt-3">
        <label class="control-label">
            If you have been monitoring your Blood pressure, please tell me the readings from the last three days.
        </label>
        
        @if (!empty($row['bp']))
            @foreach ($row['bp'] as $key => $item)
                <div class="row form-group mb-3">
                    <div class="row col-3 ms-2">
                        <input type="text" class="form-control start_date" name="hypertension[bp][{{$key}}][bp_day]" placeholder="Date" value="{{@$item['bp_day'] ?? ''}}" />
                    </div>

                    <div class="row col-3 ms-2">
                        <input type="number" min="0" class="form-control" name="hypertension[bp][{{$key}}][systolic_day]" placeholder="Systolic" value="{{@$item['systolic_day'] ?? ''}}" />
                    </div>

                    <div class="row col-3 ms-2">
                        <input type="number" min="0" class="form-control" name="hypertension[bp][{{$key}}][diastolic_day]" placeholder="Diastolic" value="{{@$item['diastolic_day'] ?? ''}}" />
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
                        <input type="text" class="form-control start_date" name="hypertension[bp][{{$i}}][bp_day]" placeholder="Date" value="{{@$row['bp_day'] ?? ""}}" />
                    </div>

                    <div class="row col-3 ms-2">
                        <input type="number" min="0" class="form-control" name="hypertension[bp][{{$i}}][systolic_day]" placeholder="Systolic" value="{{@$row['systolic_day'] ?? ''}}" />
                    </div>

                    <div class="row col-3 ms-2">
                        <input type="number" min="0" class="form-control" name="hypertension[bp][{{$i}}][diastolic_day]" placeholder="Diastolic" value="{{@$row['diastolic_day'] ?? ''}}" />
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

    {{-- REASON FOR NOT RECORDING BLOOD PRESSURE --}}
    <div class="container mb-3">
        <label>If patient has not been monitoring their blood pressure, please state the reason</label>
        <div class="row form-group mb-3 ms-2 col-8">
            <textarea class="form-control" name="hypertension[reason_for_no_bp]" rows="3">{{@$row['reason_for_no_bp'] ?? ''}}</textarea>
        </div>
    </div>

    {{-- TREATMENT GOALS --}}
    <div class="container">
        <div class="row mb-5">
            <div class="col-6">
                <h5> Treatment Goals</h5>
            </div>
            <div class="col-3">
                <h5>Start Date</h5>
            </div>
            <div class="col-3">
                <h5>End Date</h5>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To acquire knowledge about hypertension and how it can affect you
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="hypertension[effect_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['effect_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="hypertension[effect_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['effect_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To understand the importance of daily blood pressure monitoring, logging and maintaining a normal blood pressure.
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="hypertension[imp_bp_monitoring_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['imp_bp_monitoring_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="hypertension[imp_bp_monitoring_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['imp_bp_monitoring_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To understand the relationship between high blood pressure and cardiovascular and kidney disease risks
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="hypertension[relation_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['relation_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="hypertension[relation_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['relation_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To recognize the importance of discipline in taking all medications as prescribed
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="hypertension[imp_of_medication_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['imp_of_medication_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="hypertension[imp_of_medication_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['imp_of_medication_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To recognize the importance of adopting lifestyle modifications to mitigate risk of complications
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="hypertension[adopt_lifestyle_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['adopt_lifestyle_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="hypertension[adopt_lifestyle_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['adopt_lifestyle_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To understand the importance of quitting Smoking and/or reducing alcohol consumption to reduce the risk of complications
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="hypertension[quit_smoking_alcohol_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['quit_smoking_alcohol_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="hypertension[quit_smoking_alcohol_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['quit_smoking_alcohol_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To adopt dietary modifications suggested by the PCP/cardiologist and maintain a healthy diet for managing high blood pressure 
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="hypertension[adopt_dietary_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['adopt_dietary_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="hypertension[adopt_dietary_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['adopt_dietary_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To maintain a healthy Weight
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="hypertension[maintain_weight_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['maintain_weight_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="hypertension[maintain_weight_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['maintain_weight_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To engage in 150 minutes of moderate intensity physical activity per week
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="hypertension[moderate_exercise_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['moderate_exercise_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="hypertension[moderate_exercise_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['moderate_exercise_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To understand the importance of regular follow-up with PCP and cardiologist
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="hypertension[regular_pcp_folloup_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['regular_pcp_folloup_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="hypertension[regular_pcp_folloup_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['regular_pcp_folloup_end_date'] ?? ''}}"/>
            </div>
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