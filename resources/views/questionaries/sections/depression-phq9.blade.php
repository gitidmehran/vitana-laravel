<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="depression_phq9">
    <h3> Depression PHQ-9</h3>
    <ul class="list-group">
        <li class="list-group-item border-0 fw-bold lh-lg">In the past two weeks.</li>
    </ul>
    <div class="form-group mb-3">
        <label class="control-label">
            How often have you felt down, depressed, or hopeless?
        </label>
        @foreach(Config('constants.depression_phq_9') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="depression_phq9[feltdown_depressed_hopeless]" 
            type="radio" 
            value="{{$val}}" 
            @if(@$row['feltdown_depressed_hopeless']==="$val") checked @endif
            />
            <label class="form-check-label"> {{$key}} </label>
        </div>
        @endforeach
    </div>
    <div class="form-group mb-3">
        <label class="control-label">
            How often have you felt little interest or pleasure in doing things?
        </label>
        @foreach(Config('constants.depression_phq_9') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="depression_phq9[little_interest_pleasure]" type="radio" 
            value="{{$val}}" 
            @if(@$row['little_interest_pleasure']==="$val") checked @endif
            />
            <label class="form-check-label"> {{$key}} </label>
        </div>
        @endforeach
    </div>
    <div class="form-group mb-3">
        <label class="control-label">
            How often have you trouble falling or staying asleep, or sleeping too much?
        </label>
        @foreach(Config('constants.depression_phq_9') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="depression_phq9[trouble_sleep]" 
            type="radio" 
            value="{{$val}}" 
            @if(@$row['trouble_sleep']==="$val") checked @endif
            />
            <label class="form-check-label"> {{$key}} </label>
        </div>
        @endforeach
    </div>
    <div class="form-group mb-3">
        <label class="control-label">
            Feeling tired or having little energy?
        </label>
        @foreach(Config('constants.depression_phq_9') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="depression_phq9[tired_little_energy]" 
            type="radio" 
            value="{{$val}}" 
            @if(@$row['tired_little_energy']==="$val") checked @endif
            />
            <label class="form-check-label"> {{$key}} </label>
        </div>
        @endforeach
    </div>
    <div class="form-group mb-3">
        <label class="control-label">
            Poor appetite or overeating?
        </label>
        @foreach(Config('constants.depression_phq_9') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="depression_phq9[poor_over_appetite]" 
            type="radio" 
            value="{{$val}}" 
            @if(@$row['poor_over_appetite']==="$val") checked @endif
            />
            <label class="form-check-label"> {{$key}} </label>
        </div>
        @endforeach
    </div>
    <div class="form-group mb-3">
        <label class="control-label">
           How often have you felt bad about yourself that you are a failure or have let yourself or your family down?
        </label>
        @foreach(Config('constants.depression_phq_9') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="depression_phq9[feeling_bad_failure]" 
            type="radio" 
            value="{{$val}}" 
            @if(@$row['feeling_bad_failure']==="$val") checked @endif
            />
            <label class="form-check-label"> {{$key}} </label>
        </div>
        @endforeach
    </div>
    <div class="form-group mb-3">
        <label class="control-label">
            Trouble concentrating on things, such as reading the newspaper or watching television?
        </label>
        @foreach(Config('constants.depression_phq_9') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="depression_phq9[trouble_concentrating]" 
            type="radio" value="{{$val}}" 
            @if(@$row['trouble_concentrating']==="$val") checked @endif
            />
            <label class="form-check-label"> {{$key}} </label>
        </div>
        @endforeach
    </div>
    <div class="form-group mb-3">
        <label class="control-label">
             How often have you moved or spoken so slowly that other people could have noticed, or How often have you been so fidgety or restless that you have been moving around a lot more than usual?
        </label>
        @foreach(Config('constants.depression_phq_9') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="depression_phq9[slow_fidgety]" 
            type="radio" 
            value="{{$val}}" 
            @if(@$row['slow_fidgety']==="$val") checked @endif
            />
            <label class="form-check-label"> {{$key}} </label>
        </div>
        @endforeach
    </div>
    <div class="form-group mb-3">
        <label class="control-label">
            How often have you thought you would be better off dead, or hurting yourself somehow?
        </label>
        @foreach(Config('constants.depression_phq_9') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="depression_phq9[suicidal_thoughts]" 
            type="radio" 
            value="{{$val}}" 
            @if(@$row['suicidal_thoughts']==="$val") checked @endif
            />
            <label class="form-check-label"> {{$key}} </label>
        </div>
        @endforeach
    </div>

    <div class="form-group mb-3">
        <label class="control-label">
            How often have you checked off problems that made it difficult for you to do your work, take care of things at home, or get along with other people?
        </label>
        @foreach(Config('constants.problem_difficulty') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="depression_phq9[problem_difficulty]" 
            type="radio" 
            value="{{$val}}" 
            @if(@$row['problem_difficulty']==="$val") checked @endif
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    <div class="form-group mb-3 comment-box">
        <label for="exampleFormControlTextarea1">Comments</label>
        <textarea class="form-control" name="depression_phq9[comments]" rows="3"> {{@$row['comments'] ?? ''}} </textarea>
    </div>

    <div class="pull-right align-items-end">
        <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
          <button class="btn btn-primary nextBtn mx-2" type="button" >Next</button>
          <button class="btn btn-primary prevBtn" type="button">Previous</button>
      </div>
  </div>
</div>