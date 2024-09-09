 <div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="general_health">
    <h3> Genereal Health</h3>
    <div class="form-group mb-3">
        <label class="control-label">
            In general, would you say your health is?
        </label>
        @foreach(Config('constants.general_health') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="general_health[health_level]" 
            type="radio" 
            value="{{$val}}"
            @if(!empty($row['health_level']) && $row['health_level']==$val) checked @endif
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    <div class="form-group mb-3">
        <label class="control-label">
            How would you describe the condition of your mouth and teeth—including false teeth or dentures?
        </label>
        @foreach(Config('constants.general_health') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="general_health[mouth_and_teeth]" 
            type="radio" 
            value="{{$val}}"
            @if(!empty($row['mouth_and_teeth']) && $row['mouth_and_teeth']==$val) checked @endif
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    <div class="form-group mb-3">
        <label class="control-label">
            Have your feelings caused you distress or interfered with your ability to get along socially with family or friends?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="general_health[feeling_caused_distress]" 
            type="radio" 
            value="{{$val}}"
            @if(!empty($row['feeling_caused_distress']) && $row['feeling_caused_distress']==$val) checked @endif
            />
            <label class="form-check-label"> {{$val}} </label>
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