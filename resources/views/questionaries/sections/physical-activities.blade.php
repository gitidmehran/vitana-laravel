<div class="setup-content" id="step-{{$stepNo ?? '1'}}" tabno="1" data-type="physical_activities">
    <h4> Physical Activity</h4>
    <div class="form-group mb-3">
        <label class="control-label">In the past 7 days, how many days did you exercise?</label>
        <input 
        type="number" 
        min="0" 
        max="7" 
        name="physical_activities[days_of_exercise]"  
        class="form-control tab1" 
        placeholder="Days" 
        value="{{@$row['days_of_exercise']}}"
        onkeyup="hidePhysicalSection(this)"
        />
    </div>

    @php
        $showHide = (@$row['days_of_exercise'] != 0) ? '' : 'd-none' ;
    @endphp

    <div class="physicalActivitySection {{$showHide}}">
        <div class="form-group mb-3">
            <label class="control-label">On days when you exercised, for how long did you exercise (in minutes)?</label>
            <input 
            type="number" 
            min="0" 
            name="physical_activities[mins_of_exercise]"  
            class="form-control tab1" 
            placeholder="Minutes per day"  
            value="{{@$row['mins_of_exercise']}}"
            />
        </div>

        <div class="form-group mb-3">
            <label class="control-label mb-2">
                How intense was your typical exercise?
            </label>
            @foreach(Config('constants.physical_intense') as $key => $val)
            <div class="form-group form-check" style="padding-left: 5em;">
                <input 
                class="form-check-input tab1" 
                type="radio" 
                name="physical_activities[exercise_intensity]" 
                value="{{$key}}" 
                @if(@$row['exercise_intensity']==$key) checked @endif 
                />
                <label class="form-check-label">
                    {{$val}}
                </label>
            </div>
            @endforeach
        </div>

        <div class="form-group mb-3">
            <input 
            class="form-check-input tab1" 
            type="checkbox" 
            value="0" 
            name="physical_activities[does_not_apply]"
            id="flexCheckDefault" 
            onChange="exerciseTypeForm(this)"
            @if(!empty(@$row['does_not_apply'])) checked @endif
            />

            <label class="form-check-label"> Does not apply</label>
        </div>
    </div>


    <button class="btn btn-primary prevBtn" type="button">Previous</button>
    <button class="btn btn-primary nextBtn btn-md" type="button" > Next </button>
</div>