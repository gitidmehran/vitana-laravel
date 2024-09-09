<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="pain">
    <h3> Pain</h3>
    <div class="form-group mb-3">
        <label class="control-label">
            In the past 7 days, how much pain have you felt?
        </label>
        @foreach(Config('constants.pain') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="pain[pain_felt]" 
            type="radio" 
            value="{{$val}}"
            @if(!empty($row['pain_felt']) && $row['pain_felt']==$val) checked @endif
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