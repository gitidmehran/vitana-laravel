 <div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="ldct_counseling">
    <h3> LDCT Counseling</h3>

    <div class="form-group mb-3">
        <label class="control-label">
            Sign and Symptoms of Lung Cancer?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check-inline">
            <input class="form-check-input " name="ldct_counseling[cancer_symptoms]" type="radio" value="{{$val}}"
            @if(@$row['cancer_symptoms']==$val) checked @endif
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    <div class="form-group mb-3">
        <label class="control-label">
            No of Pack-Year?
        </label>
        <input maxlength="100" type="number" min="0"  name="ldct_counseling[no_of_packs_year]" class="form-control tab3" disabled placeholder="No of packs year" 
        value="{{$tobaccoUse['average_packs_per_year'] ?? ''}}"
        />
    </div>

    <div class="form-group mb-3">
        <label class="control-label">
            Current Smoker or Years since quit?
        </label>
        <input type="text" name="ldct_counseling[current_quit_smoker]" class="form-control tab3" placeholder="Current smoker or year since quit" 
        value="{{$row['current_quit_smoker'] ?? ''}}"
        />
    </div>

    <ul class="list-group">
        <li class="list-group-item border-0">Patient Counseled that LDCT will help find the effect of Smoking on the Lungs and help identify Nodules or masses that might need a follow up. Advised the it has low dose radiation exposure.</li>
        <li class="list-group-item border-0">Patient understand that we would need annual LDCT. Patient if needed will undergo treatment.</li>
        <li class="list-group-item border-0">Patient counseled to quit smoking. Patient understand the importance of smoking abstinence.</li>
    </ul>

    <div class="pull-right align-items-end">
        <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
          <button class="btn btn-primary nextBtn mx-2" type="button" >Next</button>
          <button class="btn btn-primary prevBtn" type="button">Previous</button>
      </div>
  </div>

</div>