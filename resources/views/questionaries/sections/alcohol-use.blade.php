<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="alcohol_use">
    <h3> Alcohol Use</h3>
    <div class="form-group mb-3">
        <label class="control-label">
            In the past 7 days,on how many days did you drink alcohol?
        </label>
        <input 
        type="number" 
        min="0"
        max="7"
        name="alcohol_use[days_of_alcoholuse]" 
        class="form-control tab3" 
        placeholder="Days of Alcohol usage"
        value="{{$row['days_of_alcoholuse'] ?? ''}}"
        onkeyup="hideAlcoholSection(this)"
        />
    </div>

    @php
        $showHide = (@$row['days_of_alcoholuse'] != 0) ? '' : 'd-none' ;
    @endphp

    <div class="alcoholSection {{$showHide}}">
        <div class="form-group mb-3">
            <label class="control-label">How many drinks per day?</label>
            <input 
            maxlength="100" 
            type="number" 
            min="0"  
            name="alcohol_use[drinks_per_day]" 
            class="form-control tab3" 
            placeholder="Drinks per day"
            value="{{$row['drinks_per_day'] ?? ''}}"
            />
        </div>

        <div class="form-group mb-3">
            <label class="control-label">On days when you drink alcohol, how often did you take it on one occasion?</label>
            <input 
            maxlength="100" 
            type="number" 
            min="0"
            name="alcohol_use[drinks_per_occasion]" 
            class="form-control tab3" 
            placeholder="Drinks per occasion" 
            value="{{$row['drinks_per_occasion'] ?? ''}}"
            />
        </div>

        @foreach(Config('constants.alcohol_average_use') as $key => $val)
        <div class="form-group mb-3 form-check-inline">
            <input 
            class="form-check-input " 
            name="alcohol_use[average_usage]" 
            type="radio" 
            value="{{$key}}"
            @if(@$row['average_usage']==$key) checked @endif
            />
            <label class="form-check-label">{{$val}}</label>
        </div>
        @endforeach

        <div class="form-group mb-3">
            <label class="control-label">Do you ever drive after drinking, or ride with a driver who has been drinking?</label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input 
                class="form-check-input " 
                name="alcohol_use[drink_drive_yes]" 
                type="radio" 
                value="{{$val}}"
                @if(@$row['drink_drive_yes']==$val) checked @endif
                />
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