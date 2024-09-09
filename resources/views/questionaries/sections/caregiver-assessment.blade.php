<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="caregiver_assessment">
    <h3> Caregiver Assessment </h3>       

    <div class="form-group mb-3">
        <label class="control-label">
            In the past 7 days, did you need help from others to perform every day activities such as eating, getting dressed, grooming, bathing, walking or using the toilet?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="caregiver_assessment[every_day_activities]" type="radio" value="{{$val}}" onclick="show_Adls()" @if(@$row['every_day_activities']==$key) checked @endif>
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div> 

    <div class="form-group mb-3">
        <label class="control-label">
            In the past 7 days, did you need help from others to take care of things such as laundry, house-keeping, banking, shopping, using the telephone, food preparation, transportation or taking your own medications?  
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="caregiver_assessment[medications]" type="radio" value="{{$val}}" onclick="show_Adls()" @if(@$row['medications']==$key) checked @endif>
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    {{-- adls Section (show if(no) on both) --}}

    <div class="form-group mb-3 d-none adls_section">
        <label class="control-label"> Do You have a Care giver to help take care of ADLs? </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check-inline">
            <input class="form-check-input" name="caregiver_assessment[adls]" type="radio" value="{{$val}}" onclick="adls_section_Yes(this)" @if(@$row['adls']==$key) checked @endif>
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    {{-- Live_the_patient Section (show if yes adls)  start--}}

    <div class="form-group mb-3 d-none Live_the_patient_section">                                   
        <div class="form-group mb-3">

            <label class="control-label"> Who is your help?</label>
            <input type="text" class="form-control your_help_wife" name="caregiver_assessment[your_help_wife]" value="{{@$row['your_help_wife'] ?? ''}}">
        </div>

        <div class="form-group mb-3">

         <label class="control-label">Live with the patient?</label>
         <input type="text" class="form-control Live_patient" name="caregiver_assessment[live_patient]"  value="{{@$row['live_patient'] ?? ''}}">
     </div>

 </div>
 {{-- Live_the_patient Section (show if yes adls)  end--}}

 {{-- Live_the_patient Section (show if no adls)  start--}} 
 <div class="form-group mb-3 d-none adls_section_No">
   <label class="control-label"> Referred to Home Health? </label>
   @foreach(Config('constants.agree_options') as $key => $val)
   <div class="form-group form-check-inline">
    <input class="form-check-input" name="caregiver_assessment[adls_no]" type="radio" value="{{$val}}" @if(@$row['adls_no']==$key) checked @endif>
    <label class="form-check-label"> {{$val}} </label>
</div>
@endforeach
</div>

{{-- Live_the_patient Section (show if no adls) End--}}

<div class="pull-right align-items-end">
    <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
        <button class="btn btn-primary nextBtn mx-2 pull-right" type="button" >Next</button>
        <button class="btn btn-primary prevBtn" type="button">Previous</button>
    </div>
</div>
</div>