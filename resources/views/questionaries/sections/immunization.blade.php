<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="immunization">
    <h3> Immunization</h3>

    {{-- FLU VACCINE SECTION START --}}
    <div class="form-group mb-3">
        <h5>Flu Vaccine</h5>
        <label class="control-label">
            Received Flu Vaccine ?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="immunization[flu_vaccine_recieved]" 
            type="radio" value="{{$val}}" 
            @if(!empty($row['flu_vaccine_recieved']) && $row['flu_vaccine_recieved']==$val) checked @endif
            onclick="fluVaccineInformation(this)">
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    @php
        $askFluVaccine = @$row['flu_vaccine_recieved'] == 'No' ? '' : 'd-none';
    @endphp
    <div class="form-group mb-3 askFluVaccine {{$askFluVaccine}}">
        <label class="control-label">
            Refused Flu Vaccine ?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="immunization[flu_vaccine_refused]" 
            type="radio" 
            value="{{$val}}"
            @if(!empty($row['flu_vaccine_refused']) && $row['flu_vaccine_refused']==$val) checked @endif
            onclick="showFluvaccineSection(this)"
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    @php
        $hideFluVaccineFields = @$row['flu_vaccine_recieved'] == 'Yes' ? '' : 'd-none';
    @endphp
    <div class="fluvaccine_section {{$hideFluVaccineFields}}">
        <div class="form-group mb-3 recieved_flu_vaccine ">
            <label class="control-label">
                Flu vaccine recieved on
            </label>
            <input type="text" class="form-control datepicker"
            name="immunization[flu_vaccine_recieved_on]" 
            placeholder="Flu Vaccine Recieved on MM/YYYY" value="{{$row['flu_vaccine_recieved_on'] ?? ''}}"/>
        </div>

        <div class="form-group mb-3 recieved_flu_vaccine ">
            <label class="control-label">
                Flu vaccine recieved at
            </label>
            <input type="text" class="form-control" 
            name="immunization[flu_vaccine_recieved_at]" 
            placeholder="Flu Vaccine Recieved at place" value="{{$row['flu_vaccine_recieved_at'] ?? ''}}"/>
        </div>
    </div>
    
    @php
        $hideFluscript = @$row['flu_vaccine_refused'] == 'No' && @$row['flu_vaccine_recieved'] == 'No' ? '' : 'd-none';
    @endphp
    <div class="form-group mb-3 flu_script {{$hideFluscript}}">
        <label class="control-label">
            Script given for Flu Vaccine
        </label>

        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="immunization[flu_vaccine_script_given]" 
            type="radio" value="{{$val}}"
            @if(!empty($row['flu_vaccine_script_given']) && $row['flu_vaccine_script_given']==$val) checked @endif
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>
    {{-- FLU VACCINE SECTION ENDS --}}



    {{-- PNEUMOCOCCAL VACCINE SECTION START --}}
    <div class="form-group mb-3">
        <h5>Pneumococcal Vaccine</h5>
        <label class="control-label">
            Received Pneumococcal Vaccine ?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="immunization[pneumococcal_vaccine_recieved]" 
            type="radio" 
            value="{{$val}}" 
            @if(!empty($row['pneumococcal_vaccine_recieved']) && $row['pneumococcal_vaccine_recieved']==$val) checked @endif
            onclick="pneumococcalVaccineInformation(this)">
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    @php
        $askPneumococcalVaccine = @$row['pneumococcal_vaccine_recieved'] == 'No' ? '' : 'd-none';
    @endphp
    <div class="form-group mb-3 askPneumococcalVaccine {{$askPneumococcalVaccine}}">
        <label class="control-label">
            Refused Pneumococcal Vaccine ?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="immunization[pneumococcal_vaccine_refused]" 
            type="radio" 
            value="{{$val}}"
            @if(!empty($row['pneumococcal_vaccine_refused']) && $row['pneumococcal_vaccine_refused']==$val) checked @endif
            onclick="showpnemuvaccineSection(this)"
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    @php
        $hidePneumococcalVaccine_fields = @$row['pneumococcal_vaccine_recieved'] == 'Yes' ? '' : 'd-none';
    @endphp
    <div class="pneumococcal_vaccine_section {{$hidePneumococcalVaccine_fields}}">

        <div class="form-group mb-3 recieved_pneumococcal_vaccine">
            <label class="control-label">
                Recieved Prevnar 13 on
            </label>
            <input type="text" class="form-control datepicker"
            name="immunization[pneumococcal_prevnar_recieved_on]" 
            placeholder="Prevnar 13 Vaccine Recieved on MM/YYYY" value="{{$row['pneumococcal_prevnar_recieved_on'] ?? ''}}"/>
        </div>

        <div class="form-group mb-3 recieved_pneumococcal_vaccine">
            <label class="control-label">
                Recieved Prevnar 13 at
            </label>
            <input type="text" class="form-control" name="immunization[pneumococcal_prevnar_recieved_at]" 
            placeholder="Prevnar 13 Vaccine Recieved at place" value="{{$row['pneumococcal_prevnar_recieved_at'] ?? ''}}"/>
        </div>

        <div class="form-group mb-3 recieved_pneumococcal_vaccine">
            <label class="control-label">
                Recieved PPSV 23 on
            </label>
            <input type="text" class="form-control datepicker"
            name="immunization[pneumococcal_ppsv23_recieved_on]" 
            placeholder="PPSV 23 Vaccine Recieved on MM/YYYY" value="{{$row['pneumococcal_ppsv23_recieved_on'] ?? ''}}"/>
        </div>

        <div class="form-group mb-3 recieved_pneumococcal_vaccine">
            <label class="control-label">
                Recieved PPSV 23 at
            </label>
            <input type="text" class="form-control" name="immunization[pneumococcal_ppsv23_recieved_at]"
            placeholder="PPSV 23 Vaccine Recieved at place" value="{{$row['pneumococcal_ppsv23_recieved_at'] ?? ''}}"/>
        </div>

    </div>

    @php
        $hidepneumococcalscript = @$row['pneumococcal_vaccine_refused'] == 'No' && @$row['pneumococcal_vaccine_recieved'] == 'No' ? '' : 'd-none';
    @endphp
    <div class="form-group mb-3 script_pneumococcal {{$hidepneumococcalscript}}">
        <label class="control-label">
            Script given for Prevnar 13 / PPSV 23
        </label>

        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="immunization[pneumococcal_vaccine_script_given]" 
            type="radio" 
            value="{{$val}}"
            @if(!empty($row['pneumococcal_vaccine_script_given']) && $row['pneumococcal_vaccine_script_given']==$val) checked @endif
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>
    {{-- PNEUMOCOCCAL VACCINE SECTION ENDS --}}


    <div class="form-group mb-3 comment-box">
        <label for="exampleFormControlTextarea1">Comments</label>
        <textarea class="form-control" name="immunization[comments]" rows="3"> {{@$row['comments'] ?? ''}} </textarea>
    </div>

    <div class="pull-right align-items-end">
        <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
            <button class="btn btn-primary nextBtn mx-2" type="button" >Next</button>
            <button class="btn btn-primary prevBtn" type="button">Previous</button>
        </div>
    </div>
</div>