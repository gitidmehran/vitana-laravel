<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="high_stress">
    <h3> High Stress</h3>
    <div class="form-group mb-3">
        <label class="control-label">
            How often is stress a problem for you in handling such things as:
        </label>
        <ul class="list-unstyled">
            <li>
                <ul>
                    <li class="">Your health?</li>
                    <li class="">Your finances?</li>
                    <li class="">Your family or social relationships?</li>
                    <li class="">Your work?</li>
                </ul>
            </li>
        </ul>
        @foreach(Config('constants.high_stress') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="high_stress[stress_problem]" 
            type="radio" 
            value="{{$val}}"
            @if(@$row['stress_problem']==$val) checked @endif
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