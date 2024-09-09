<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="cong_heart_failure">

    <h3>Congestive Heart Failure</h3>
    <div class="container mt-3">
        
        <div class="form-group mb-3">
            <label class="control-label">
                Do you follow up with a Cardiologist for your CHF?
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input 
                class="form-check-input tab6" 
                name="cong_heart_failure[follow_up_cardio]" 
                onclick="followUpWithCardiologist(this)"
                type="radio"
                @if(!empty($row['follow_up_cardio']) && $row['follow_up_cardio']==$val) checked @endif 
                value="{{$val}}"
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>

        <div class="follow_up_with_cardiologist d-none">
            <div class="form-group mb-3">
                <label class="control-label">
                    How frequently were you recommended to follow up with cardiology?
                </label>
                @foreach(Config('constants.follow_up_cardio') as $key => $val)
                <div class="form-group form-check-inline">
                    <input 
                    class="form-check-input tab6" 
                    name="cong_heart_failure[freq_recom_cardio]"
                    
                    type="radio"
                    @if(!empty($row['freq_recom_cardio']) && $row['freq_recom_cardio']==$key) checked @endif 
                    value="{{$key}}"
                    />
                    <label class="form-check-label"> {{$val}} </label>
                </div>
                @endforeach
            </div>
        </div>


        <div class="not_follow_up_with_cardiologist d-none">
            <div class="form-group mb-3">
                <label class="control-label">
                    Why are you not seeing a Cardiologist?
                </label>
                @foreach(Config('constants.not_follow_up_cardio') as $key => $val)
                <div class="form-group form-check-inline">
                    <input 
                    class="form-check-input tab6" 
                    name="cong_heart_failure[not_following_cardio]"
                    type="radio"
                    
                    @if(!empty($row['not_following_cardio']) && $row['not_following_cardio']==$key) checked @endif 
                    value="{{$key}}"
                    />
                    <label class="form-check-label"> {{$val}} </label>
                </div>
                @endforeach
            </div>
        </div>
    </div>


    <div class="container mt-3">        
        
        <div class="form-group mb-3">
            <label class="control-label">
                Did you have an echocardiogram within the last 1-2 years?
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input 
                class="form-check-input tab6" 
                name="cong_heart_failure[echocardiogram]" 
                onclick="haveEchodiogram(this)"
                type="radio"
                @if(!empty($row['echocardiogram']) && $row['echocardiogram']==$val) checked @endif 
                value="{{$val}}"
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>

        <div class="no_echodiogram d-none">
            <div class="form-group mb-3">
                @foreach(Config('constants.no_echodiogram') as $key => $val)
                <div class="form-group form-check-inline">
                    <input 
                    class="form-check-input tab6" 
                    name="cong_heart_failure[no_echocardiogram]" 
                    type="radio"
                    
                    @if(!empty($row['no_echocardiogram']) && $row['no_echocardiogram']==$key) checked @endif 
                    value="{{$key}}"
                    />
                    <label class="form-check-label"> {{$val}} </label>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row mb-5">
            <div class="col-8">
                <h5>Goals</h5>
            </div>
            <div class="col-2">
                <h5>Start Date</h5>
            </div>
            <div class="col-2">
                <h5>End Date</h5>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-8">
                <p>
                    To gain education about CHF and self-management of the condition
                </p>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control start_date" name="cong_heart_failure[ge_chf_start_date]" value="{{@$row['ge_chf_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control end_date" name="cong_heart_failure[ge_chf_end_date]" value="{{@$row['ge_chf_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-8">
                <p>
                    To understand the importance of smoking cessation and restricting alcohol intake
                </p>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control start_date" name="cong_heart_failure[ui_smoke_cessation_start_date]" value="{{@$row['ui_smoke_cessation_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control end_date" name="cong_heart_failure[ui_smoke_cessation_end_date]" value="{{@$row['ui_smoke_cessation_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-8">
                <p>
                    To understand the importance of a low sodium diet
                </p>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control start_date" name="cong_heart_failure[ui_sodium_diet_start_date]" value="{{@$row['ui_sodium_diet_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control end_date" name="cong_heart_failure[ui_sodium_diet_end_date]" value="{{@$row['ui_sodium_diet_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-8">
                <p>
                    To understand the importance of fluid restriction for self-management of CHF
                </p>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control start_date" name="cong_heart_failure[ui_fluid_restriction_start_date]" value="{{@$row['ui_fluid_restriction_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control end_date" name="cong_heart_failure[ui_fluid_restriction_end_date]" value="{{@$row['ui_fluid_restriction_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-8">
                <p>
                    To understand importance of daily weight monitoring and recording in a daily log
                </p>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control start_date" name="cong_heart_failure[uid_weight_monitoring_start_date]" value="{{@$row['uid_weight_monitoring_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control end_date" name="cong_heart_failure[uid_weight_monitoring_end_date]" value="{{@$row['uid_weight_monitoring_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-8">
                <p>
                    To recognize the signs of exacerbation which must be reported to the doctor/nurse
                </p>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control start_date" name="cong_heart_failure[rs_excerbation_start_date]" value="{{@$row['rs_excerbation_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control end_date" name="cong_heart_failure[rs_excerbation_end_date]" value="{{@$row['rs_excerbation_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-8">
                <p>
                    To recognize the importance of adherence to treatment
                </p>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control start_date" name="cong_heart_failure[ri_adherence_start_date]" value="{{@$row['ri_adherence_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control end_date" name="cong_heart_failure[ri_adherence_end_date]" value="{{@$row['ri_adherence_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off" />
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-8">
                <p>
                    To seek help with activities of daily living
                </p>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control start_date" name="cong_heart_failure[seek_help_start_date]" value="{{@$row['seek_help_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-2">
                <input type="text" class="form-control end_date" name="cong_heart_failure[seek_help_end_date]" value="{{@$row['seek_help_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off"/>
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