<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="seatbelt_use">
    <h3> Seat Belt Use</h3>
    <div class="form-group mb-3">
        <label class="control-label">
            Do you always fasten your seat belt when you are in a car?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check-inline">
            <input 
            class="form-check-input tab6" 
            name="seatbelt_use[wear_seal_belt]" 
            type="radio" 
            value="{{$val}}"
            @if(@$row['wear_seal_belt']==$val) checked @endif
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