<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="screening">
    <h3> Screening</h3>

    {{-- MAMMOGRAM SECTION STARTS --}}
    <div class="form-group mb-3">
        <label class="control-label">
            Mammogram done ?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="screening[mammogram_done]" 
            type="radio" 
            value="{{$val}}" 
            @if(!empty($row['mammogram_done']) && $row['mammogram_done']==$val) checked @endif
            onchange="mammogramInformation(this)">
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    @php
        $askMammogram = @$row['mammogram_done'] == 'No' ? '' : 'd-none';
    @endphp
    <div class="form-group mb-3 askMammogram {{$askMammogram}}">
        <label class="control-label">
            Refused Mammogram ?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="screening[mammogram_refused]" 
            type="radio" 
            value="{{$val}}"
            @if(!empty($row['mammogram_refused']) && $row['mammogram_refused']==$val) checked @endif
            onclick="askMammogram(this)"
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    @php
    $hideMammogram_fields = @$row['mammogram_done'] == 'Yes' ? '' : 'd-none';
    @endphp

    <div class="mammogramSection {{$hideMammogram_fields}}">
        
        <div class="form-group mb-3">
            <label class="control-label">
                Mammogram done on ?
            </label>
            <input type="text" class="form-control datepicker"
            name="screening[mammogram_done_on]" 
            placeholder="Mammogram done on MM/YYYY" value="{{$row['mammogram_done_on'] ?? ''}}"/>
        </div>

        <div class="form-group mb-3">
            <label class="control-label">
                Mammogram done at ?
            </label>
            <input type="text" class="form-control" name="screening[mammogram_done_at]" 
            placeholder="Mammogram done at" value="{{$row['mammogram_done_at'] ?? ''}}"/>
        </div>

        <div class="form-group mb-3">
            <input class="form-check-input tab1" type="checkbox" value="1" 
            name="screening[mommogram_report_reviewed]" id="flexCheckDefault"
            @if(!empty(@$row['mommogram_report_reviewed'])) checked @endif
            />

            <label class="form-check-label"> Report reviewed</label>
        </div>

        <div class="form-group mb-3">
            <label class="control-label">
                Next Mammogram due on
            </label>
            <input type="text" class="form-control datepicker"
            name="screening[next_mommogram]" placeholder="Next mommogram on MM/YYYY"
            value="{{$row['next_mommogram'] ?? ''}}"/>     
        </div>
    </div>

    @php
        $mammogramScript = @$row['mammogram_refused'] == 'No' && @$row['mammogram_done'] == 'No' ? '' : 'd-none';
    @endphp
    <div class="form-group mb-3 mammogram_script {{$mammogramScript}}">
        <label class="control-label">
            Script given for the Screening Mammogram ?
        </label>

        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="screening[mammogram_script]" type="radio" value="{{$val}}"
            @if(!empty($row['mammogram_script']) && $row['mammogram_script']==$val) checked @endif
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>
    {{-- MAMMOGRAM SECTION ENDS --}}


    {{-- COLONOSCOPY FIT TEST SECTION STARTS --}}

    <div class="form-group mb-3">
        <label class="control-label">
            Colonoscopy / FIT Test / Cologuard done ?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" 
            name="screening[colonoscopy_done]" 
            type="radio" value="{{$val}}" 
            @if(!empty($row['colonoscopy_done']) && $row['colonoscopy_done']==$val) checked @endif
            onchange="colonoscopyInformation(this)">
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    @php
        $askColofitguard = @$row['colonoscopy_done'] == 'No' ? '' : 'd-none';
    @endphp
    <div class="form-group mb-3 askColofitguard {{$askColofitguard}}">
        <label class="control-label">
            Refused Colonoscopy & FIT Test ?
        </label>
        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="screening[colonoscopy_refused]" type="radio" value="{{$val}}"
            @if(!empty($row['colonoscopy_refused']) && $row['colonoscopy_refused']==$val) checked @endif
            onclick="refusedColfitguard(this)"
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>


    @php
    $hideColonogrph_fields = @$row['colonoscopy_done'] == 'Yes' ? '' : 'd-none';
    @endphp
    <div class="colonographSection {{$hideColonogrph_fields}}">
        <div class="form-group mb-3">
            @foreach(Config('constants.colon_test_type') as $key => $val)
            <div class="form-group form-check-inline">
                <input class="form-check-input tab6" name="screening[colon_test_type]" type="radio" value="{{$val}}"
                @if(!empty($row['colon_test_type']) && $row['colon_test_type']==$val) checked @endif
                onclick="udpateFieldsLable(this)"
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>

        <div class="form-group mb-3 performed_on">
            <label class="control-label">
                Colonoscopy / FIT Test / Cologuard done on
            </label>
            <input type="text" class="form-control datepicker"
            name="screening[colonoscopy_done_on]" placeholder="Perfomed on MM/YYYY"
            value="{{$row['colonoscopy_done_on'] ?? ''}}"/> 
        </div>

        <div class="form-group mb-3 performed_at">
            <label class="control-label">
                Colonoscopy / FIT Test / Cologuard done at
            </label>
            <input type="text" class="form-control" name="screening[colonoscopy_done_at]" placeholder="Performed at"
            value="{{$row['colonoscopy_done_at'] ?? ''}}"/>
        </div>

        <div class="form-group mb-3">
            <input class="form-check-input tab1" type="checkbox" value="1" 
            name="screening[colonoscopy_report_reviewed]" id="flexCheckDefault"
            @if(!empty(@$row['colonoscopy_report_reviewed'])) checked @endif
            />

            <label class="form-check-label"> Report reviewed</label>
        </div>

        <div class="form-group mb-3 next_perform">
            <label class="control-label">
                Next due on
            </label>
            <input type="text" class="form-control datepicker"
            name="screening[next_colonoscopy]" placeholder="Next due on MM/YYYY"
            value="{{$row['next_colonoscopy'] ?? ''}}"/>  
        </div>
    </div>

    @php
        $colofitguardScript = @$row['colonoscopy_done'] == 'No' && @$row['colonoscopy_refused'] == 'No' ? '' : 'd-none';
    @endphp
    <div class="form-group mb-3 colonscopy_script {{$colofitguardScript}}">
        <label class="control-label">
            Script given for the Screening Colonoscopy
        </label>

        @foreach(Config('constants.agree_options') as $key => $val)
        <div class="form-group form-check">
            <input class="form-check-input tab6" name="screening[colonoscopy_script]" type="radio" value="{{$val}}"
            @if(!empty($row['colonoscopy_script']) && $row['colonoscopy_script']==$val) checked @endif
            />
            <label class="form-check-label"> {{$val}} </label>
        </div>
        @endforeach
    </div>

    <div class="form-group mb-3 comment-box">
        <label for="exampleFormControlTextarea1">Comments</label>
        <textarea class="form-control" name="screening[comments]" rows="3"> {{@$row['comments'] ?? ''}} </textarea>
    </div>


    <div class="pull-right align-items-end">
        <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
            @if(isset($end) && $end==true)
            <button class="btn btn-primary mx-2" type="submit" >Finish!</button>
            <button class="btn btn-primary prevBtn" type="button">Previous</button>
            @else
            <button class="btn btn-primary nextBtn mx-2" type="button" >Next</button>
            <button class="btn btn-primary prevBtn" type="button">Previous</button>
            @endif
        </div>
    </div>
</div>