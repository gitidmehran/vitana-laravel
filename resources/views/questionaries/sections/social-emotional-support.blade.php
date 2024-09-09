<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="social_emotional_support">
    <h3> Social/Emotional Support</h3>
    <div class="form-group mb-3">
        <label class="control-label">
            How often do you get the social and emotional support you need?
        </label>
        @foreach(Config('constants.social_emotional_support') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="social_emotional_support[get_social_emotional_support]" 
            type="radio" 
            value="{{$val}}"
            @if(!empty($row['get_social_emotional_support']) && $row['get_social_emotional_support']==$val) checked @endif
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