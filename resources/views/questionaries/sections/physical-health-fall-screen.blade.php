<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="fall_screening">
    <h3> Physical Health - Fall Screening</h3>
    <div class="form-group mb-3">
        <label class="control-label">
            Have you fallen in the past 1 year?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="fall_screening[fall_in_one_year]" 
            type="radio" 
            value="{{$val}}"
            @if(!empty($row['fall_in_one_year']) && $row['fall_in_one_year']==$val) checked @endif
            onclick="fallScreening(this)">
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    @php
        $showFall_questions = @$row['fall_in_one_year'] == 'Yes' ? '' : 'd-none';
    @endphp
    <div class="agree_to_fall {{$showFall_questions}}">
        <div class="form-group mb-3">
            <label class="control-label">
                Number of times you fell in last 1 year
            </label>
            @foreach(Config('constants.fall_screening') as $key => $val)
            <div class="form-group form-check">
                <input class="form-check-input tab6" 
                name="fall_screening[number_of_falls]" 
                type="radio" value="{{$val}}" 
                @if(!empty($row['number_of_falls']) && $row['number_of_falls']==$val) checked @endif
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    
        <div class="form-group mb-3">
            <label class="control-label">
                Was their any injury?
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check">
                <input class="form-check-input tab6" 
                name="fall_screening[injury]" 
                type="radio" 
                value="{{$val}}"
                @if(!empty($row['injury']) && $row['injury']==$val) checked @endif
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    
        <div class="form-group mb-3">
            <label class="control-label">
                Physical Therapy
            </label>
            @foreach(Config('constants.physical_therapy') as $key => $val)
            <div class="form-group form-check">
                <input class="form-check-input tab6" 
                name="fall_screening[physical_therapy]" 
                type="radio" value="{{$val}}"
                @if(!empty($row['physical_therapy']) && $row['physical_therapy']==$val) checked @endif
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>
    
    <div class="form-group mb-3">
        <label class="control-label">
            Do you feel unsteady or do thing move when standing or walking?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="fall_screening[unsteady_todo_things]" 
            type="radio" 
            value="{{$val}}"
            @if(!empty($row['unsteady_todo_things']) && $row['unsteady_todo_things']==$val) checked @endif
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    <div class="form-group mb-3">
        <label class="control-label">
            Do you feel like “blacking out” when getting up from bed or chair?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="fall_screening[blackingout_from_bed]" 
            type="radio" 
            value="{{$val}}"
            @if(!empty($row['blackingout_from_bed']) && $row['blackingout_from_bed']==$val) checked @endif
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    <div class="form-group mb-3">
        <label class="control-label">
            Do you use any assistance device?
        </label>
        @foreach(Config('constants.assistance_device') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="fall_screening[assistance_device]" 
            type="radio" 
            value="{{$val}}"
            @if(!empty($row['assistance_device']) && $row['assistance_device']==$val) checked @endif
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>



    <div class="pull-right align-items-end">
        <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
            <button class="btn btn-primary nextBtn mx-2 pull-right" type="button" >Next</button>
        </div>
    </div>
</div>