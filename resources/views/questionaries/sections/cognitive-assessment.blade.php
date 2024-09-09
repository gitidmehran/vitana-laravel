<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="cognitive_assessment">
    <h3> Cognitive Assessment</h3>
    <div class="form-group mb-3">
        <label class="control-label">
            What year is it? 
        </label>
        @foreach(Config('constants.error_options_a') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="cognitive_assessment[year_recalled]" type="radio" value="{{$val}}"
            @if(!empty($row['year_recalled']) && $row['year_recalled']==$val) checked @endif
            />
            <label class="form-check-label"> {{ucwords($val)}} </label>
        </div>
        @endforeach
    </div>

    <div class="form-group mb-3">
        <label class="control-label">
            What month is it?
        </label>
        @foreach(Config('constants.error_options_a') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="cognitive_assessment[month_recalled]" type="radio" value="{{$val}}"
            @if(!empty($row['month_recalled']) && $row['month_recalled']==$val) checked @endif
            />
            <label class="form-check-label"> {{ucwords($val)}} </label>
        </div>
        @endforeach
    </div>

    <div class="form-group mb-3">
        <label class="control-label">
            Give the patient an address phrase to remember with 5 components, <b>eg John, Smith, 42, High St, Bedford</b> 
        </label>
    </div>

    <div class="form-group mb-3">
        <label class="control-label">
            About what time is it (within 1 hour) ?
        </label>
        @foreach(Config('constants.error_options_a') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="cognitive_assessment[hour_recalled]" type="radio" value="{{$val}}"
            @if(!empty($row['hour_recalled']) && $row['hour_recalled']==$val) checked @endif
            />
            <label class="form-check-label"> {{ucwords($val)}} </label>
        </div>
        @endforeach
    </div>

    <div class="form-group mb-3">
        <label class="control-label">
            Count backwards from 20-1.
        </label>
        @foreach(Config('constants.error_options_b') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="cognitive_assessment[reverse_count]" type="radio" value="{{$val}}"
            @if(!empty($row['reverse_count']) && $row['reverse_count']==$val) checked @endif
            />
            <label class="form-check-label"> {{ucwords($val)}} </label>
        </div>
        @endforeach
    </div>

    <div class="form-group mb-3">
        <label class="control-label">
            Say the months of the year in reverse.
        </label>
        @foreach(Config('constants.error_options_b') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input" name="cognitive_assessment[reverse_month]" type="radio" value="{{$val}}"
            @if(!empty($row['reverse_month']) && $row['reverse_month']==$val) checked @endif
            />
            <label class="form-check-label"> {{ucwords($val)}} </label>
        </div>
        @endforeach
    </div>

    <div class="form-group mb-3">
        <label class="control-label">
            Repeat address phrase <b>John, Smith, 42, High St, Bedford</b>
        </label>
        @foreach(Config('constants.error_options_c') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input " name="cognitive_assessment[address_recalled]" type="radio" value="{{$val}}"
            @if(!empty($row['address_recalled']) && $row['address_recalled']==$val) checked @endif
            />
            <label class="form-check-label"> {{ucwords($val)}} </label>
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