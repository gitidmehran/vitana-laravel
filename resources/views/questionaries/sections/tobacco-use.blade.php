<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="tobacco_use">
    <h3> Tobacco Use</h3>

    <div class="thirty-days-record">

        <div class="form-group mb-3">
            <label class="control-label"> Patient Age? </label>
            <input maxlength="100" type="number" min="0"  name="tobacco_use[patient_age]" class="form-control tab3" placeholder="Age" 
            value="{{$patientAge ?? ''}}" disabled/>
        </div>

        <div class="form-group mb-3">
            <label class="control-label">
                In the last 30 days, have you used tobacco?
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input " name="tobacco_use[smoked_in_thirty_days]" type="radio" value="{{$val}}"  @if(@$row['smoked_in_thirty_days']==$val) checked @endif onchange="smokingStatus()">
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>

        <div class="form-group mb-3">
            <label class="control-label">Have you ever used a smokeless tobacco product?</label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input " 
                name="tobacco_use[smokeless_product_use]" 
                type="radio" 
                value="{{$val}}" 
                @if(@$row['smokeless_product_use']==$val) checked @endif
                onclick="smokingStatus()">
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>


    <div class="fifteen-years-record">
        <div class="form-group mb-3">
            <label class="control-label">
                In the last 15 years, have you used tobacco?
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input " name="tobacco_use[smoked_in_fifteen_years]" type="radio" value="{{$val}}" 
                @if(@$row['smoked_in_fifteen_years']==$val) checked @endif
                onclick="smokingStatus()">
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>


    @php
    $hideAverageSmoking = @$row['average_smoking_years'] ? '' : 'd-none';
    $hideLdctQuestion = @$row['perform_ldct'] == 'Yes' ? '' : 'd-none';
    @endphp

    <div class="average-smoking {{$hideAverageSmoking}}">
        <div class="form-group mb-3">
            <label class="control-label">Average smoking years?</label>
            <input maxlength="100" 
            type="number" min="0" 
            name="tobacco_use[average_smoking_years]" 
            class="form-control tab3" 
            placeholder="Average smoking years"
            value="{{$row['average_smoking_years'] ?? ''}}"
            onkeyup="claculateSmokingpacks()" onmouseup="claculateSmokingpacks()" />
        </div>

        <div class="form-group mb-3">
            <label class="control-label">Average packs per day?</label>
            <input maxlength="100" 
            type="number" min="0"  
            name="tobacco_use[average_packs_per_day]" 
            class="form-control tab3" 
            placeholder="Average pack per day" 
            value="{{$row['average_packs_per_day'] ?? ''}}"
            onkeyup="claculateSmokingpacks()" onmouseup="claculateSmokingpacks()" />
        </div>

        <div class="form-group mb-3">
            <label class="control-label">Average packs per year?</label>
            <input maxlength="100" 
            type="number" min="0"  
            name="tobacco_use[average_packs_per_year]" 
            class="form-control tab3" 
            value="{{$row['average_packs_per_year'] ?? ''}}"
            placeholder="Average packs per year" />
        </div>

        <div class="form-group mb-3">
            <label class="control-label">Would you be interested in quitting tobacco use within the next month?</label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input" 
                name="tobacco_use[quit_tobacco]" 
                type="radio" value="{{$val}}" 
                @if(@$row['quit_tobacco']==$val) checked @endif
                onclick="quitTobacco()">
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>


    <div class="ldctquestion {{$hideLdctQuestion}}">
        <div class="form-group mb-3">
            <label class="control-label">Would you be interested to Perform LDCT?</label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input " 
                name="tobacco_use[perform_ldct]" 
                type="radio"
                value="{{$val}}"
                @if(@$row['perform_ldct']==$val) checked @endif
                onclick="ldctCouncelingSection(this)"
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>


    @php
    $hideTobaccoAlternate = @$row['tobacoo_alternate'] == 'Yes' ? '' : 'd-none';
    @endphp

    <div class="tobacoo-alternate {{$hideTobaccoAlternate}}">
        <div class="form-group mb-3">
            <label class="control-label">Would you be interested in using any alternate?</label>
            @foreach(Config('constants.tobacco_alternate') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input" 
                name="tobacco_use[tobacoo_alternate]" 
                type="radio" 
                @if(@$row['tobacoo_alternate']==$val) checked @endif
                value="{{$val}}">
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>

        <div class="form-group mb-3">
            @foreach(Config('constants.tobacco_alternate_qty') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input " 
                name="tobacco_use[tobacoo_alternate_qty]" 
                type="radio" 
                @if(@$row['tobacoo_alternate_qty']==$val) checked @endif
                value="{{$val}}">
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
    </div>

    <div class="pull-right align-items-end">
        <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
          <button class="btn btn-primary nextBtn mx-2" type="button" >Next</button>
          <button class="btn btn-primary prevBtn" type="button">Previous</button>
      </div>
  </div>

</div>