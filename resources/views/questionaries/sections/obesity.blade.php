<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="obesity">

    <h3>Obesity</h3>

    {{-- BLOOD PRESSURE RESULT SECTION --}}
    <div class="container mb-3 mt-3">
        <div class="col mb-3">
            <label class="control-label">
                Have you gained weight since last visit?
            </label>
            
            @foreach(Config('constants.agree_options') as $key => $val)
                <div class="form-group form-check-inline">
                    <input class="form-check-input"  name="obesity[gained_weight]" type="radio"  value="{{$val}}" @if(!empty($row['gained_weight']) && $row['gained_weight']==$val) checked @endif onclick="show_newBmi(this)">
                    <label class="form-check-label"> {{$val}} </label>
                </div>
            @endforeach
        </div>
        
        <div class="lost_weight col mb-3 d-none">
            <label class="control-label">
                Have you lost weight since last visit ?
            </label>
            
            @foreach(Config('constants.agree_options') as $key => $val)
                <div class="form-group form-check-inline">
                    <input class="form-check-input"  name="obesity[lost_weight]" type="radio"  value="{{$val}}" @if(!empty($row['lost_weight']) && $row['lost_weight']==$val) checked @endif onclick="show_newBmi(this)">
                    <label class="form-check-label"> {{$val}} </label>
                </div>
            @endforeach
        </div>


        <div class="form-group mb-3 col-6 obesity_newbmi d-none">
            <label class="control-label">
                How much
            </label>
            <input type="number" step="0.01" class="form-control" name="obesity[bmi]"  placeholder="BMI" value="{{$row['bmi'] ?? ''}}"/>
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
                    To gain education and awareness about BMI and current BMI range.
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="obesity[awareness_about_bmi_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['awareness_about_bmi_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="obesity[awareness_about_bmi_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['awareness_about_bmi_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To understand the need for weight loss.
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="obesity[need_of_weight_loss_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['need_of_weight_loss_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="obesity[need_of_weight_loss_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['need_of_weight_loss_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To understand the importance of maintaining a healthy weight.
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="obesity[imp_of_healthy_weight_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['imp_of_healthy_weight_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="obesity[imp_of_healthy_weight_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['imp_of_healthy_weight_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To understand the importance of healthy eating habits.
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="obesity[imp_of_healthy_eating_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['imp_of_healthy_eating_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="obesity[imp_of_healthy_eating_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['imp_of_healthy_eating_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To receive education regarding required changes in diet that would assist with weight loss.
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="obesity[diet_assist_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['diet_assist_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="obesity[diet_assist_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['diet_assist_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To engage in 150 minutes of moderate intensity physical activity per week.
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="obesity[moderate_activity_inaweek_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['moderate_activity_inaweek_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="obesity[moderate_activity_inaweek_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['moderate_activity_inaweek_end_date'] ?? ''}}"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To be referred to a dietician.
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="obesity[referred_dietician_start_date]" placeholder="Start Date" autocomplete="off" value="{{@$row['referred_dietician_start_date'] ?? ''}}"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="obesity[referred_dietician_end_date]" placeholder="End Date" autocomplete="off" value="{{@$row['referred_dietician_end_date'] ?? ''}}"/>
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