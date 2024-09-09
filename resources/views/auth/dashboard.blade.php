@extends('auth.master')

@section('content')
<!-- MultiStep Form -->
<div class="container">

    <form role="form" id="programdata">

        <div class="col-md-4" style="margin-bottom: 20px;">
            <label class="control-label">Select Patient</label>
            <select class="form-select form-select-lg" name="patient[patient_selected]" aria-label="Default select example">
                <option value="1" selected>Patient 1</option>
                <option value="2">Patient 2</option>
                <option value="3">Patient 3</option>
            </select>
        </div>

        <div class="col-md-4" style="margin-bottom: 20px;">
            <label class="control-label">Select Program</label>
            <select class="form-select form-select-lg" name="program[program_selected]" aria-label="Default select example">
                <option value="1" selected>AWV</option>
                <option value="2">Program 2</option>
                <option value="3">Program 3</option>
            </select>

        </div>

        <div class="stepwizard">
            <div class="stepwizard-row setup-panel">
                <div class="stepwizard-step">
                    <a href="#step-1" type="button" class="btn btn-primary btn-circle">1</a>
                    <p>Physical Activity</p>
                </div>

                <div class="stepwizard-step">
                    <a href="#step-2" type="button" class="btn btn-default btn-circle" disabled="disabled">2</a>
                    <p>Alcohol Use</p>
                </div>

                <div class="stepwizard-step">
                    <a href="#step-3" type="button" class="btn btn-default btn-circle" disabled="disabled">3</a>
                    <p>Nutrition</p>
                </div>

                <div class="stepwizard-step">
                    <a href="#step-4" type="button" class="btn btn-default btn-circle" disabled="disabled">4</a>
                    <p>Seat Belt Use</p>
                </div>

                <div class="stepwizard-step">
                    <a href="#step-5" type="button" class="btn btn-default btn-circle" disabled="disabled">5</a>
                    <p>Submit</p>
                </div>
            </div>
        </div>

        <!-- Physical Activity -->
        <div class="row setup-content" id="step-1" tabno="1">
            <div class="col-xs-12">
                <div class="col-md-12">
                    <h3> Physical Activity</h3>
                    <div class="form-group">
                        <label class="control-label">In the past 7 days, how many days did you exercise?</label>
                        <input type="number" min="0" max="7" name="physical_activity[days_of_exercise]"  class="form-control tab1" placeholder="Days" />
                    </div>

                    <div class="form-group">
                        <label class="control-label">On days when you exercised, for how long did you exercise (in minutes)?</label>
                        <input type="number" min="0" name="physical_activity[mins_of_exercise]"  class="form-control tab1" placeholder="Minutes per day"  />
                    </div>

                    <div class="form-group">
                        <label class="control-label">How intense was your typical exercise?</label>
                    </div>

                    <div class="form-group form-check" style="padding-left: 5em;">
                        <input class="form-check-input tab1" type="radio" name="physical_activity['exercise_intensity']" value="light">
                        <label class="form-check-label"> Light (like stretching or slow walking) </label>
                    </div>

                    <div class="form-group form-check" style="padding-left: 5em;">
                        <input class="form-check-input tab1" type="radio" name="physical_activity['exercise_intensity']" value="moderate">
                        <label class="form-check-label"> Moderate (like stretching or slow walking)
                        </label>
                    </div>

                    <div class="form-group form-check" style="padding-left: 5em;">
                        <input class="form-check-input tab1" type="radio" name="physical_activity['exercise_intensity']" value="heavy">
                        <label class="form-check-label"> Heavy (like stretching or slow walking) </label>
                    </div>

                    <div class="form-group form-check" style="padding-left: 5em;">
                        <input class="form-check-input tab1" type="radio" name="physical_activity['exercise_intensity']" value="veryheavy">
                        <label class="form-check-label"> Very Heavy (like stretching or slow walking)
                        </label>
                    </div>

                    <div class="form-group form-check" style="padding-left: 5em;">
                        <input class="form-check-input tab1" type="radio" name="physical_activity['exercise_intensity']" value="noexercise">
                        <label class="form-check-label"> I am currently not exercising </label>
                    </div>

                    <div class="form-group">
                        <input class="form-check-input tab1" type="checkbox" value="0" name="physical_activity[does_not_apply]" id="flexCheckDefault" onChange="exerciseTypeForm(this)">
                        
                        <label class="form-check-label"> Does not apply</label>
                    </div>

                    <div id="physical_activity_report"></div>

                    <button class="btn btn-primary nextBtn btn-md pull-right" type="button" >Next</button>
                    <!-- <button class="btn btn-primary btn-md pull-right" type="submit" >Submit</button> -->
                </div>
            </div>
        </div>

        <!-- Alcohol Use -->
        <div class="row setup-content" id="step-2">
            <div class="col-xs-12">
                <div class="col-md-12">
                    <h3> Alcohol Use</h3>

                    <div class="form-group">
                        <label class="control-label">In the past 7 days,on how many days did you drink alcohol?</label>
                        <input maxlength="100" type="number" min="0"  name="alcohol_use['days_of_alcoholuse']" class="form-control tab3" placeholder="Days of Alcohol usage" />
                    </div>

                    <div class="form-group">
                        <label class="control-label">How many drinks per day?</label>
                        <input maxlength="100" type="number" min="0"  name="alcohol_use['drinks_per_day']" class="form-control tab3" placeholder="Drinks per day" />
                    </div>

                    <div class="form-group">
                        <label class="control-label">On days when you drank alcohol, how often did you have alcoholic drinks on one occasion?</label>
                        <input maxlength="100" type="number" min="0"  name="alcohol_use['drinks_per_occasion']" class="form-control tab3" placeholder="Drinks per occasion" />
                    </div>
                    
                    <div class="form-group form-check-inline">
                        <input class="form-check-input " name="alcohol_use['average_usage']" type="radio" value="never">
                        <label class="form-check-label"> Never </label>
                    </div>
                    <div class="form-group form-check-inline">
                        <input class="form-check-input " name="alcohol_use['average_usage']" type="radio" value="once_per_week">
                        <label class="form-check-label"> Once during the week </label>
                    </div>
                    <div class="form-group form-check-inline">
                        <input class="form-check-input " name="alcohol_use['average_usage']" type="radio" value="2-3 per week">
                        <label class="form-check-label"> 2–3 times during the week </label>
                    </div>
                    <div class="form-group form-check-inline">
                        <input class="form-check-input " name="alcohol_use['average_usage']" type="radio" value="More then 3 per week">
                        <label class="form-check-label"> More than 3 times during the week  </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Do you ever drive after drinking, or ride with a driver who has been drinking?</label>
                        <div class="form-group form-check-inline">
                            <input class="form-check-input " name="alcohol_use['drink_drive_yes']" type="radio" value="Yes">
                            <label class="form-check-label"> YES  </label>
                        </div>
                        <div class="form-group form-check-inline">
                            <input class="form-check-input " name="alcohol_use['drink_drive_no']" type="radio" value="No">
                            <label class="form-check-label"> No  </label>
                        </div>
                    </div>

                   

                    <button class="btn btn-primary nextBtn btn-md pull-right" type="button" >Next</button>
                    <button class="btn btn-primary prevBtn btn-md pull-right" type="button" style="margin-right: 10px;" >Previous</button>
                </div>
            </div>
        </div>

        <!-- Nutrition -->
        <div class="row setup-content" id="step-3">
            <div class="col-xs-12">
                <div class="col-md-12">
                    <h3> Nutrition</h3>

                    <div class="form-group">
                        <label class="control-label">In the past 7 days, how many servings of fruits and vegetables did you typically eat each day?</label>
                        </br><span style="color:grey;">(1 serving = 1 cup of fresh vegetables, ½ cup of cooked vegetables, or 1 medium piece of fruit. 1 cup = size of a baseball.)</span>
                        <input maxlength="100" type="number" min="0"  name="nutrition['fruits_vegs']" class="form-control tab3" placeholder="servings per day" style="margin-top:5px;" />
                    </div>
                    <div class="form-group">
                        <label class="control-label">In the past 7 days, how many servings of high fiber or whole (not refined) grain foods did you typically eat each day?</label>
                        </br><span style="color:grey;">(1 serving = 1 slice of 100% whole wheat bread, 1 cup of whole-grain or high-fiber ready-to-eat cereal, ½ cup of cooked cereal such as oatmeal, or ½ cup of cooked brown rice or whole wheat pasta.)</span>
                        <input maxlength="100" type="number" min="0"  name="nutrition['whole_grain_food']" class="form-control tab3" placeholder="servings per day" style="margin-top:5px;" />
                    </div>
                    <div class="form-group">
                        <label class="control-label">In the past 7 days, how many servings of fried or high-fat foods did you typically eat each day?</label>
                        </br><span style="color:grey;">(Examples include fried chicken, fried fish, bacon, French fries, potato chips, corn chips, doughnuts, creamy salad dressings, and foods made with whole milk, cream, cheese, or mayonnaise.)</span>
                        <input maxlength="100" type="number" min="0"  name="nutrition['high_fat_food']" class="form-control tab3" placeholder="servings per day" style="margin-top:5px;" />
                    </div>
                    <div class="form-group">
                        <label class="control-label">In the past 7 days, how many sugar-sweetened (not diet) beverages did you typically consume each day?</label>
                        <input maxlength="100" type="number" min="0"  name="nutrition['sugar_beverages']" class="form-control tab3" placeholder="servings per day" />
                    </div>

                    <button class="btn btn-primary nextBtn btn-md pull-right" type="button" >Next</button>
                    <button class="btn btn-primary prevBtn btn-md pull-right" type="button" style="margin-right: 10px;" >Previous</button>
                </div>
            </div>
        </div>

        <!-- Seat Belt Use -->
        <div class="row setup-content" id="step-4">
            <div class="col-xs-12">
                <div class="col-md-12">
                    <h3> Seat Belt Use</h3>

                    <div class="form-group">
                        <label class="control-label">Do you always fasten your seat belt when you are in a car?</label>
                        <div class="form-group form-check-inline">
                            <input class="form-check-input tab6" name="seatbelt_use['wear_seal_belt']" type="radio" value="Yes">
                            <label class="form-check-label"> Yes </label>
                        </div>
                        <div class="form-group form-check-inline">
                            <input class="form-check-input tab6" name="seatbelt_use['wear_seal_belt']" type="radio" value="No">
                            <label class="form-check-label"> No </label>
                        </div>
                    </div>

                   

                    <button class="btn btn-primary nextBtn btn-md pull-right" type="button" >Next</button>
                    <button class="btn btn-primary prevBtn btn-md pull-right" type="button" style="margin-right: 10px;" >Previous</button>
                </div>
            </div>
        </div>

        <div class="row setup-content" id="step-5" >
            <div class="col-xs-12">
                <div class="col-md-12">
                    <h3> Submit</h3>
                    <button class="btn btn-success btn-md pull-right" type="submit">Finish!</button>
                </div>
            </div>
        </div>
        
    </form>
</div>
@endsection
