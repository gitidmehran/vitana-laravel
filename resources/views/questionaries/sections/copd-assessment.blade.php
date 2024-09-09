<div class="row setup-content ccm_copd" id="step-{{$stepNo ?? '1'}}" data-type="copd_assessment">
    <h3> Chronic Obstructive Pulmonary Disease</h3>

    <div class="container mb-5 col-12">
        <label> Please answer the following questions, rating your symptoms on a scale of 1 to 5. </label>

        <table class="table">

            <thead>
                <tr>
                  <th scope="col"></th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                  <th scope="col">Score</th>
                </tr>

            <tbody>
                <tr>
                    <td>I never Cough</td>
                    <td>
                        @for ($i = 0; $i <= 5 ; $i++)
                        <div class="form-group form-check-inline">
                            <input class="form-check-input" name="copd_assessment[cough]" type="radio" value="{{$i}}" onchange="assessment_score(this)"
                            @if(!empty($row['cough']) && $row['cough']==$i) checked @endif
                            />
                            <label class="form-check-label"> {{$i}} </label>
                        </div>
                        @endfor
                    </td>
                    <td>I cough all the time</td>
                    <th scope="row">
                        <input type="number" name="copd_assessment[cough_score]" class="border border-success rounded text-center scoreinput" disabled value="{{@$row['cough_score'] ?? ""}}"/>
                    </th>
                </tr>
                
                <tr>
                    <td>I have no phlegum (mucus) in my chest at all</td>
                    <td>
                        @for ($i = 0; $i <= 5 ; $i++)
                        <div class="form-group form-check-inline">
                            <input class="form-check-input" name="copd_assessment[phlegum_in_chest]" type="radio" value="{{$i}}" onchange="assessment_score(this)"
                            @if(!empty($row['phlegum_in_chest']) && $row['phlegum_in_chest']==$i) checked @endif
                            />
                            <label class="form-check-label"> {{$i}} </label>
                        </div>
                        @endfor
                    </td>
                    <td>My chest is completely full of phlegum (mucus)</td>
                    <th scope="row">
                        <input type="number" name="copd_assessment[phlegum_score]" class="border border-success rounded text-center scoreinput" disabled value="{{@$row['phlegum_score'] ?? ""}}"/>
                    </th>
                </tr>
                
                <tr>
                    <td>My chest does not feel tight at all</td>
                    <td>
                        @for ($i = 0; $i <= 5 ; $i++)
                        <div class="form-group form-check-inline">
                            <input class="form-check-input" name="copd_assessment[tight_chest]" type="radio" value="{{$i}}" onchange="assessment_score(this)"
                            @if(!empty($row['tight_chest']) && $row['tight_chest']==$i) checked @endif
                            />
                            <label class="form-check-label"> {{$i}} </label>
                        </div>
                        @endfor
                    </td>
                    <td>My chest feels very tight</td>
                    <th scope="row">
                        <input type="number" name="copd_assessment[tight_chest_score]" class="border border-success rounded text-center scoreinput" disabled value="{{@$row['tight_chest_score'] ?? ""}}"/>
                    </th>
                </tr>
                
                <tr>
                    <td>When i walk upto a hill or one flight of stairs i am not breathless</td>
                    <td>
                        @for ($i = 0; $i <= 5 ; $i++)
                        <div class="form-group form-check-inline">
                            <input class="form-check-input" name="copd_assessment[breathless]" type="radio" value="{{$i}}" onchange="assessment_score(this)"
                            @if(!empty($row['breathless']) && $row['breathless']==$i) checked @endif
                            />
                            <label class="form-check-label"> {{$i}} </label>
                        </div>
                        @endfor
                    </td>
                    <td>When i walk upto a hill or one flight of stairs i am very breathless</td>
                    <th scope="row">
                        <input type="number" name="copd_assessment[breathless_score]" class="border border-success rounded text-center scoreinput" disabled value="{{@$row['breathless_score'] ?? ""}}"/>
                    </th>
                </tr>
                
                <tr>
                    <td>I am not limited doing any activities at home</td>
                    <td>
                        @for ($i = 0; $i <= 5 ; $i++)
                        <div class="form-group form-check-inline">
                            <input class="form-check-input" name="copd_assessment[limited_activities]" type="radio" value="{{$i}}" onchange="assessment_score(this)"
                            @if(!empty($row['limited_activities']) && $row['limited_activities']==$i) checked @endif
                            />
                            <label class="form-check-label"> {{$i}} </label>
                        </div>
                        @endfor
                    </td>
                    <td>I am very limited doing activities at home</td>
                    <th scope="row">
                        <input type="number" name="copd_assessment[limited_activity_score]" class="border border-success rounded text-center scoreinput" disabled value="{{@$row['limited_activity_score'] ?? ""}}"/>
                    </th>
                </tr>
                
                <tr>
                    <td>I am confident leaving my home despite my lung condition</td>
                    <td>
                        @for ($i = 0; $i <= 5 ; $i++)
                        <div class="form-group form-check-inline">
                            <input class="form-check-input" name="copd_assessment[lung_condition]" type="radio" value="{{$i}}" onchange="assessment_score(this)"
                            @if(!empty($row['lung_condition']) && $row['lung_condition']==$i) checked @endif
                            />
                            <label class="form-check-label"> {{$i}} </label>
                        </div>
                        @endfor
                    </td>
                    <td>I am not at all confident leaving my home because of my lung condition</td>
                    <th scope="row">
                        <input type="number" name="copd_assessment[lung_condition_score]" class="border border-success rounded text-center scoreinput" disabled value="{{@$row['lung_condition_score'] ?? ""}}"/>
                    </th>
                </tr>
                
                <tr>
                    <td>I sleep soundly</td>
                    <td>
                        @for ($i = 0; $i <= 5 ; $i++)
                        <div class="form-group form-check-inline">
                            <input class="form-check-input" name="copd_assessment[sound_sleep]" type="radio" value="{{$i}}" onchange="assessment_score(this)"
                            @if(!empty($row['sound_sleep']) && $row['sound_sleep']==$i) checked @endif
                            />
                            <label class="form-check-label"> {{$i}} </label>
                        </div>
                        @endfor
                    </td>
                    <td>I don't sleep soundly because of my lung condition</td>
                    <th scope="row">
                        <input type="number" name="copd_assessment[sound_sleep_score]" class="border border-success rounded text-center scoreinput" disabled value="{{@$row['sound_sleep_score'] ?? ""}}"/>
                    </th>
                </tr>
                
                <tr>
                    <td>I have lots of energy</td>
                    <td>
                        @for ($i = 0; $i <= 5 ; $i++)
                        <div class="form-group form-check-inline">
                            <input class="form-check-input" name="copd_assessment[energy_level]" type="radio" value="{{$i}}" onchange="assessment_score(this)"
                            @if(!empty($row['energy_level']) && $row['energy_level']==$i) checked @endif
                            />
                            <label class="form-check-label"> {{$i}} </label>
                        </div>
                        @endfor
                    </td>
                    <td>I have no energy at all</td>
                    <th scope="row">
                        <input type="number" name="copd_assessment[energy_level_score]" class="border border-success rounded text-center scoreinput" disabled value="{{@$row['energy_level_score'] ?? ""}}"/>
                    </th>
                </tr>
                
                <tr>
                    <td></td>
                    <td></td>
                    <th scope="row">Total Score</td>
                    <th class="total_score" scope="row">
                        <input type="number" name="copd_assessment[total_assessment_score]" class="border border-success rounded text-center" disabled value="{{@$row['total_assessment_score'] ?? ""}}"/>
                    </th>
                </tr>
            </tbody>
        </table>
    </div>


    <div class="container">
        <div class="row mb-5">
            <div class="col-6">
                <h5> Treatment Goals</h5>
            </div>
            <div class="col-3">
                <h5>Start Date</h5>
            </div>
            <div class="col-3">
                <h5>End Date</h5>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To understand the importance of smoking cessation
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="copd_assessment[smoking_cessation_start_date]" value="{{@$row['smoking_cessation_start_date'] ?? ''}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="copd_assessment[smoking_cessation_end_date]" value="{{@$row['smoking_cessation_end_date'] ?? ''}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To recognize the importance of discipline in taking COPD medication as prescribed
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="copd_assessment[copd_medication_start_date]" value="{{@$row['copd_medication_start_date'] ?? ''}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="copd_assessment[copd_medication_end_date]" value="{{@$row['copd_medication_end_date'] ?? ''}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To have an understanding regarding safe utilization and management of supplemental oxygen therapy
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="copd_assessment[supplemental_oxygen_start_date]" value="{{@$row['supplemental_oxygen_start_date'] ?? ''}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="copd_assessment[supplemental_oxygen_end_date]" value="{{@$row['supplemental_oxygen_end_date'] ?? ''}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To have an understanding regarding self-management of COPD
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="copd_assessment[self_mgmt_start_date]" value="{{@$row['self_mgmt_start_date'] ?? ''}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="copd_assessment[self_mgmt_end_date]" value="{{@$row['self_mgmt_end_date'] ?? ''}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To identify and avoid triggers for exacerbations
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="copd_assessment[tirgger_exacerbations_start_date]" value="{{@$row['tirgger_exacerbations_start_date'] ?? ''}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="copd_assessment[tirgger_exacerbations_end_date]" value="{{@$row['tirgger_exacerbations_end_date'] ?? ''}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To recognize signs and symptoms of exacerbations which must be reported to the doctor/nurse
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="copd_assessment[exacerbations_symptoms_start_date]" value="{{@$row['exacerbations_symptoms_start_date'] ?? ''}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="copd_assessment[exacerbations_symptoms_end_date]" value="{{@$row['exacerbations_symptoms_end_date'] ?? ''}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To understand the importance of regular follow-up with PCP and Pulmonologist
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="copd_assessment[followup_imp_start_date]" value="{{@$row['followup_imp_start_date'] ?? ''}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="copd_assessment[followup_imp_end_date]" value="{{@$row['followup_imp_end_date'] ?? ''}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To understand the importance of pneumonia and flu vaccination
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="copd_assessment[imp_of_vaccine_start_date]" value="{{@$row['imp_of_vaccine_start_date'] ?? ''}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="copd_assessment[imp_of_vaccine_end_date]" value="{{@$row['imp_of_vaccine_end_date'] ?? ''}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To develop knowledge regarding and engage in symptom-limited, safe physical activity
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="copd_assessment[safe_physical_activity_start_date]" value="{{@$row['safe_physical_activity_start_date'] ?? ''}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="copd_assessment[safe_physical_activity_end_date]" value="{{@$row['safe_physical_activity_end_date'] ?? ''}}" placeholder="End Date" autocomplete="off"/>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6>
                    To utilize counseling/group support.
                </h6>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control start_date" name="copd_assessment[group_support_start_date]" value="{{@$row['group_support_start_date'] ?? ''}}" placeholder="Start Date" autocomplete="off"/>
            </div>
            <div class="form-group col-3">
                <input type="text" class="form-control end_date" name="copd_assessment[group_support_end_date]" value="{{@$row['group_support_end_date'] ?? ''}}" placeholder="End Date" autocomplete="off"/>
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