<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="weight_assessment">
    <h3> Weight Assessment</h3>
    
    <div class="form-group mb-3">
        <label class="control-label">
            BMI ?
        </label>
        <input type="number" min="0" step="0.01" class="form-control" name="weight_assessment[bmi_value]" placeholder="BMI"
        value="{{$row['bmi_value'] ?? ''}}" onkeyup="askaboutNutritionist(this)"
        />
    </div>

    @php
        $showHide = (@$row['bmi_value'] == 30) ? '' : 'd-none' ;
    @endphp

    <div class="form-group mb-3 askForNutritionist {{$showHide}}">
        <label class="control-label">Would you like to follow up with the Nutritionist ?</label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check-inline">
            <input class="form-check-input " 
            name="weight_assessment[followup_withnutritionist]" 
            type="radio" value="{{$val}}"
            @if(@$row['followup_withnutritionist']==$val) checked @endif 
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