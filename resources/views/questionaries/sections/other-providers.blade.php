<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="other_Provider">
    <h3> Other Provider </h3>
    <div class="form-group mb-3">
        <label class="control-label">
            Do you see any other Provider beside PCP?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="other_Provider[other_provider_beside_pcp]" type="radio" value="{{$val}}" onclick="other_Provider_beside_PCP_Yes()" @if(@$row['other_provider_beside_pcp']==$key) checked @endif>
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>
    {{-- other_Provider_beside_PCP_Yes Section (show if yes adls)  start--}}

    <div class="form-group mb-3 d-none other_Provider_beside_PCP_section row">                                   
        <div class="form-group col">
            <label class="control-label"> Name </label>
            <input type="text" class="typeahead form-control full_Name" name="other_provider[full_name]" value="{{@$row['full_name'] ?? ''}}" id ="searchaabb">
        </div>

        <div class="form-group col">
            <label class="control-label">Speciality </label>
            <input type="text" class="form-control speciality" name="other_provider[speciality]"  value="{{@$row['speciality'] ?? ''}}">
        </div>

 </div>
 {{-- Live_the_patient Section (show if yes adls)  end--}} 

 <div class="pull-right align-items-end">
    <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
        <button class="btn btn-primary nextBtn mx-2" type="button" >Next</button>
        <button class="btn btn-primary prevBtn" type="button">Previous</button>
    </div>
</div>

</div>