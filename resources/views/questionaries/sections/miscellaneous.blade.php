<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="misc">
    <h3> Miscellaneous </h3>

    <h5> Vitals </h5>
    <div class="form-group mb-3 col-6">
        <label class="control-label">
            Height ?
        </label>
        <input type="text" min="0" step="0.01" class="form-control" name="misc[height]" placeholder="Height (ft,inches)" value="{{@$row['height'] ?? ''}}" />
    </div>
    
    <div class="form-group mb-3 col-6">
        <label class="control-label">
            Weight ?
        </label>
        <input type="number" min="0" step="0.01" class="form-control" name="misc[weight]" placeholder="Weight (lbs)" value="{{@$row['weight'] ?? ''}}" />
    </div>

    <h5> Advance Care Plan </h5>
    <p>
        Advanced Care planning was discussed with the patient. A packet is given to the patient. The patient shows understanding. The patient was by himself during the discussion
    </p>
    <div class="form-group mb-3 col-6">
        <label class="control-label">
            Time spent
        </label>
        <input type="number" min="0" step="0.01" class="form-control" name="misc[time_spent]" placeholder="Time spent in minutes" value="{{@$row['time_spent'] ?? ''}}" />
    </div>
    
    <h5> Intensive behavioral therapy for cardiovascular disease (CVD) </h5>
    <div class="form-group form-check" style="padding-left: 2.3em;">
        <input class="form-check-input tab1" type="checkbox" name="misc[asprin_use]" value="check" @if(!empty(@$row['asprin_use'])) checked @endif />
        <label class="form-check-label">
            Encouraged aspirin use for primary prevention a cardiovascular disease when the benefits outweigh the risks for men age 45-79 and women 55-79.
        </label>
    </div>
    
    <div class="form-group form-check" style="padding-left: 2.3em;">
        <input class="form-check-input tab1" type="checkbox" name="misc[high_blood_pressure]" value="check" @if(!empty(@$row['high_blood_pressure'])) checked @endif />
        <label class="form-check-label">
            Screened for high blood pressure.
        </label>
    </div>
    
    <div class="form-group form-check" style="padding-left: 2.3em;">
        <input class="form-check-input tab1" type="checkbox" name="misc[behavioral_counselling]" value="check" @if(!empty(@$row['behavioral_counselling'])) checked @endif />
        <label class="form-check-label">
            Intensive behavioral counseling provided to promote a healthy diet for adults who already have hyperlipidemia, hypertension, advancing age, and other known risk factors for cardiovascular and diet related chronic diseases.
        </label>
    </div>

    <div class="pull-right align-items-end">
        <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
            <button class="btn btn-primary mx-2" type="submit" >Finish!</button>
            <button class="btn btn-primary prevBtn" type="button">Previous</button>
        </div>
    </div>
</div>