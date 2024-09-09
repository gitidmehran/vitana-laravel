<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="hypercholestrolemia">
    <h3> Hypercholesterolemia</h3>

    {{-- Hypercholesterolemia Details --}}
    <div class="container">
        <div class="form-group mb-3">
            <label class="control-label">
                Has Hypercholesterolemia assessment done in this calendar year or in last 1 year?
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input 
                class="form-check-input tab6" 
                name="hypercholestrolemia[assesment_done]" 
                onclick="showHypercholestrolemiaOptions(this)"
                type="radio"
                @if(!empty($row['assesment_done']) && $row['assesment_done']==$val) checked @endif 
                value="{{$val}}"
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>

        <div class="no_assesment d-none">
            <div class="form-group mb-3">
                <label class="control-label">
                    Is this Patient on Moderate to High Intensity statin?
                </label>
                @foreach(Config('constants.agree_options') as $key => $val)
                <div class="form-group form-check-inline">
                    <input 
                    class="form-check-input tab6" 
                    name="hypercholestrolemia[statin_intensity]" 
                    type="radio" 
                    value="{{$val}}"
                    @if(!empty($row['statin_intensity']) && $row['statin_intensity']==$val) checked @endif
                    />
                    <label class="form-check-label"> {{$val}} </label>
                </div>
                @endforeach
            </div>

            

            <div class="form-group mb-3">
                <label class="control-label">
                    Is this patient’s LDL at Goal?
                </label>
                @foreach(Config('constants.agree_options') as $key => $val)
                <div class="form-group form-check-inline">
                    <input 
                    class="form-check-input tab6" 
                    name="hypercholestrolemia[ldl_goal]" 
                    type="radio" 
                    @if(!empty($row['ldl_goal']) && $row['ldl_goal']==$val) checked @endif
                    value="{{$val}}"
                    />
                    <label class="form-check-label"> {{$val}} </label>
                </div>
                @endforeach
            </div>
        </div>
    </div>


    {{-- Goals --}}
    <div class="container">
        <div class="row mb-5 mt-3">
            <div class="col-8">
                <h5>
                    Goals
                </h5>
            </div>
            <div class="form-group col-2">
                <h5>Start Date</h5>
            </div>
            <div class="form-group col-2">
                <h5>End Date</h5>
            </div>
        </div>


            <div class="row mb-4">
                <div class="col-8">
                    <p>
                        To develope an understanding regarding risk factors and monitoring for Hyperlipidemia.
                    </p>
                </div>
                <div class="form-group col-2">
                    <input type="text" class="form-control start_date" name="hypercholestrolemia[ur_hyperlipidemia_start_date]" value="{{@$row['ur_hyperlipidemia_start_date'] ?? ''}}" placeholder="Start Date" autocomplete="off"/>
                </div>
                <div class="form-group col-2">
                    <input type="text" class="form-control end_date" name="hypercholestrolemia[ur_hyperlipidemia_end_date]" value="{{@$row['ur_hyperlipidemia_end_date'] ?? ''}}" placeholder="End Date" autocomplete="off"/>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-8">
                    <p>
                        To understand the effect of Lipids on Cardiovascular System
                    </p>
                </div>
                <div class="form-group col-2">
                    <input type="text" class="form-control start_date" name="hypercholestrolemia[el_cardio_start_date]" value="{{@$row['el_cardio_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off"/>
                </div>
                <div class="form-group col-2">
                    <input type="text" class="form-control end_date" name="hypercholestrolemia[el_cardio_end_date]" value="{{@$row['el_cardio_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off"/>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-8">
                    <p>
                        To understand the importance of healthy diet in controlling Lipids
                    </p>
                </div>
                <div class="form-group col-2">
                    <input type="text" class="form-control start_date" name="hypercholestrolemia[ui_controlling_start_date]" value="{{@$row['ui_controlling_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off"/>
                </div>
                <div class="form-group col-2">
                    <input type="text" class="form-control end_date" name="hypercholestrolemia[ui_controlling_end_date]" value="{{@$row['ui_controlling_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off"/>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-8">
                    <p>
                        To understand the effect of Exercise on Lipids
                    </p>
                </div>
                <div class="form-group col-2">
                    <input type="text" class="form-control start_date" name="hypercholestrolemia[ue_exercise_start_date]" value="{{@$row['ue_exercise_start_date'] ?? ""}}" placeholder="Start Date" autocomplete="off"/>
                </div>
                <div class="form-group col-2">
                    <input type="text" class="form-control end_date" name="hypercholestrolemia[ue_exercise_end_date]" value="{{@$row['ue_exercise_end_date'] ?? ""}}" placeholder="End Date" autocomplete="off"/>
                </div>
            </div>
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