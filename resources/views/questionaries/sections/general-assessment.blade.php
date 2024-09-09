<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="general_assessment">
    <h3> General Assessment</h3>

    <div class="pull-right align-items-end">
        <div class="mb-3">
            <h5> General Hygiene Goal </h5>
        </div>

        
        <div class="row mb-5">
            <div class="col-6">
                <h6> To Understand importance of Hand Washing in Infection Control Start date Completed</h6>
            </div>
            <div class="col-3">
                <h6>Start Date</h6>
            </div>
            <div class="col-3">
                <h6>End Date</h6>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    Instructed on Importance of Hand Washing
                </h6>
                <p class="mt-4">
                    Scientific studies show that you need to scrub for 20 seconds to remove harmful germs and chemicals from your hands.
                    If you wash for a shorter time, you will not remove as many germs. 
                    Make sure to scrub all areas of your hands, including your palms, backs of your hands, between your fingers, and under your fingernails.
                </p>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="general_assessment[imp_handwash_start_date]" value="{{@$row['imp_handwash_start_date']}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="general_assessment[imp_handwash_end_date]" value="{{@$row['imp_handwash_end_date']}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    Patient shows understanding of Importance of Hand Washing
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="general_assessment[und_handwash_start_date]" value="{{@$row['und_handwash_start_date']}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="general_assessment[und_handwash_end_date]" value="{{@$row['und_handwash_end_date']}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    Instructed on how washing with Soap remove germs
                </h6>
                <p class="mt-4">
                    Soap and water, worked into a lather, trap and remove germs and chemicals from hands. Wetting
                    your hands with clean water before applying soap helps you get a better lather than applying
                    soap to dry hands. A good lather forms pockets called micelles that trap and remove germs,
                    harmful chemicals, and dirt from your hands.
                    Lathering with soap and scrubbing your hands for 20 seconds is important to this process because
                    these actions physically destroy germs and remove germs and chemicals from your skin. When
                    you rinse your hands, you wash the germs and chemicals down the drain.
                </p>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="general_assessment[washwithsoap_start_date]" value="{{@$row['washwithsoap_start_date']}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="general_assessment[washwithsoap_end_date]" value="{{@$row['washwithsoap_end_date']}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    Patient shows understanding on how washing with Soap remove germs
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="general_assessment[und_washhands_start_date]" value="{{@$row['und_washhands_start_date']}}"  placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="general_assessment[und_washhands_end_date]" value="{{@$row['und_washhands_end_date']}}"  placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6> Instructed on proper way to turn off the faucet</h6>

                <p class="mt-4">
                    CDC recommends turning off the faucet after wetting your hands to reduce water use. Then, turn
                    it on again after you have washed them for 20 seconds, to rinse off the soap. If you are concerned
                    about getting germs on your hands after you wash them, you can use a paper towel, your elbow,
                    or another hands-free way to turn off the faucet.
                </p>

            </div>
            <div class="col-3">
                <input type="text" class="form-control start_date" id="start_date" name="general_assessment[turnoff_faucet_start_date]" value="{{@$row['turnoff_faucet_start_date']}}" placeholder="Start Date" autocomplete="off">
            </div>
            <div class="col-3">
                <input type="text" class="form-control end_date" id="end_date" name="general_assessment[turnoff_faucet_end_date]" value="{{@$row['turnoff_faucet_end_date']}}" placeholder="End Date" autocomplete="off">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6> Patient shows understanding on proper way to turn off the faucet</h6>

                <p class="mt-4">
                    Which Soap is better: Plain or Anti-bacterial Soap? 7/1/22
                    Use plain soap and water to wash your hands. Studies have not found any added health benefit
                    from using antibacterial soap, other than for professionals in healthcare settings. In 2016, FDA
                    banned over-the-counter sale of antibacterial soaps that contain certain ingredients external
                    icon because these soaps are no better than plain soap at preventing people from getting sick
                    and their ingredients may not be safe for long-term, daily use. Some studies external icon have
                    shown that using antibacterial soap may contribute to antibiotic resistance.
                </p>

            </div>
            <div class="col-3">
                <input type="text" class="form-control start_date" id="start_date" name="general_assessment[understand_faucet_start_date]" value="{{@$row['understand_faucet_start_date']}}" placeholder="Start Date" autocomplete="off">
            </div>
            <div class="col-3">
                <input type="text" class="form-control end_date" id="end_date" name="general_assessment[understand_faucet_end_date]" value="{{@$row['understand_faucet_end_date']}}" placeholder="End Date" autocomplete="off">
            </div>
        </div>


        <div class="row mb-3">
            <div class="col-6">
                <h6> Patient shows understanding of using plain Soap</h6>
            </div>
            <div class="col-3">
                <input type="text" class="form-control start_date" id="start_date" name="general_assessment[plain_soap_usage_start_date]" value="{{@$row['plain_soap_usage_start_date']}}" placeholder="Start Date" autocomplete="off">
            </div>
            <div class="col-3">
                <input type="text" class="form-control end_date" id="end_date" name="general_assessment[plain_soap_usage_end_date]" value="{{@$row['plain_soap_usage_end_date']}}" placeholder="End Date" autocomplete="off">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6> Is Bar Soap or Liquid Soap better?</h6>
                <p class="mt-4">Both bar and liquid soap work well to remove germs. Use plain soap in either bar or liquid
                    form to wash your hands.</p>
            </div>
            <div class="col-3">
                <input type="text" class="form-control start_date" name="general_assessment[bar_or_liquid_start_date]" value="{{@$row['bar_or_liquid_start_date']}}" placeholder="Start Date" autocomplete="off">
            </div>
            <div class="col-3">
                <input type="text" class="form-control end_date" name="general_assessment[bar_or_liquid_end_date]" value="{{@$row['bar_or_liquid_end_date']}}" placeholder="End Date" autocomplete="off">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6> Patient shows understanding about importance of plain soap in any form</h6>
            </div>
            <div class="col-3">
                <input type="text" class="form-control start_date" name="general_assessment[uips_start_date]" value="{{@$row['uips_start_date']}}" placeholder="Start Date" autocomplete="off">
            </div>
            <div class="col-3">
                <input type="text" class="form-control end_date" name="general_assessment[uips_end_date]" value="{{@$row['uips_end_date']}}"  placeholder="End Date" autocomplete="off">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6> What if there is no Soap?</h6>
                <p class="mt-3">
                    If you don’t have soap and water, use a hand sanitizer with at least 60% alcohol. If you don’t have
                    hand sanitizer or soap, but do have water, rub your hands together under the water and dry them
                    with a clean towel or air dry. Rubbing your hands under water will rinse some germs from your
                    hands, even though it’s not as effective as washing with soap.
                </p>
            </div>
            <div class="col-3">
                <input type="text" class="form-control start_date" name="general_assessment[no_soap_condition_start_date]" value="{{@$row['no_soap_condition_start_date']}}" placeholder="Start Date" autocomplete="off">
            </div>
            <div class="col-3">
                <input type="text" class="form-control end_date" name="general_assessment[no_soap_condition_end_date]" value="{{@$row['no_soap_condition_end_date']}}" placeholder="End Date" autocomplete="off">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6> Patient shows understanding about Hand Sanitizer?</h6>
            </div>
            <div class="col-3">
                <input type="text" class="form-control start_date" name="general_assessment[understand_hand_sanitizer_start_date]" value="{{@$row['understand_hand_sanitizer_start_date']}}" placeholder="Start Date" autocomplete="off">
            </div>
            <div class="col-3">
                <input type="text" class="form-control end_date" name="general_assessment[understand_hand_sanitizer_end_date]" value="{{@$row['understand_hand_sanitizer_end_date']}}" placeholder="End Date" autocomplete="off">
            </div>
        </div>

        <h5 class="mb-3"> Medication Reconciliation</h5>

        
        <div class="form-group mb-3">
            <label class="control-label">
                Are you taking all medications for (insert disease names selected above, separated by and if two
                conditions. If 3 or more conditions, A,B,C and D) as prescribed?
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input 
                class="form-check-input tab6" 
                name="general_assessment[is_taking_medication]" 
                type="radio" 
                value="{{$val}}"
                onclick="showMedicationReason(this)"
                @if(!empty($row['is_taking_medication']) && $row['is_taking_medication']==$val) checked @endif
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>
        
        <div class="medication_reason d-none">
            <div class="form-group mb-3">
                <label class="control-label">
                    Which medications are not being taken as prescribed?
                </label>
                <div class="col-4 my-2">
                    <div class="form-group">
                        <label for="name">Medication </label>
                        <select name="general_assessment[prescribed_medications]" class="form-control">
                            <option value="medication-one">Medication 1</option>
                            <option value="medication-two">Medication 2</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group mb-3">
                <div> <label>Reason</label></div>
                <textarea class="form-control" name="general_assessment[reason_for_not_taking_medication]" value="{{@$row['reason_for_not_taking_medication'] ?? ""}}" placeholder="Reason for not taking medications" id="exampleFormControlTextarea1" rows="3">{{@$row['reason_for_not_taking_medication'] ?? ""}}</textarea>
            </div>
        </div>

        <h5>Lifestyle Assessment</h5>
        <div class="form-group mb-3">
            <label class="control-label">
                In the last 30 days, have you used tobacco?
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input 
                class="form-check-input tab6" 
                name="general_assessment[is_consuming_tobacco]" 
                type="radio" 
                value="{{$val}}"
                onclick="tobaccoUsage(this)"
                @if(!empty($row['is_consuming_tobacco']) && $row['is_consuming_tobacco']==$val) checked @endif
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>

        <div class="form-group quitting_tobacco d-none mb-3">
            <label class="control-label">
                Would you be interested in quitting tobacco use within the next month?
            </label>
            @foreach(Config('constants.agree_options') as $key => $val)
            <div class="form-group form-check-inline">
                <input 
                class="form-check-input tab6" 
                name="general_assessment[quitting_tobacco]" 
                type="radio" 
                value="{{$val}}"
                @if(!empty($row['quitting_tobacco']) && $row['quitting_tobacco']==$val) checked @endif
                />
                <label class="form-check-label"> {{$val}} </label>
            </div>
            @endforeach
        </div>


        
        <div class="form-group mb-3 ">
            <label class="control-label">
                In the last 30 days, other than the activities you did for work, on average, how many days
                per week did you engage in moderate exercise (like walking fast, running, jogging,
                dancing, swimming, biking, or other similar activities)?                
            </label>
        
            @for ($i = 0; $i <=7; $i++)
            <div class="form-group form-check-inline">
                <input class="form-check-input" name="general_assessment[physical_exercises]" @if(!empty($row['physical_exercises']) && $row['physical_exercises']==$i) checked @endif type="radio" value="{{$i}}"/>
                <label class="form-check-label"> {{$i}} </label>
            </div>
            @endfor
        </div>

        <div class="form-group mb-3 ">
            <label class="control-label">
                On average, how many minutes did you usually spend exercising at this level on one of
                those days?
            </label>
        
            @for ($i = 0; $i <=60; $i+=10)
            <div class="form-group form-check-inline">
                <input class="form-check-input" name="general_assessment[physical_exercise_level]" @if(!empty($row['physical_exercise_level']) && $row['physical_exercise_level']==$i) checked @endif type="radio" value="{{$i}}""/>
                <label class="form-check-label"> {{$i}} </label>
            </div>
            @endfor
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