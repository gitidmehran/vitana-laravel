<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="bp_assessment">
    <h3> BP Assessment</h3>

    <div class="form-group mb-3">
        <label class="control-label">
            BP ?
        </label>
        <input type="text" class="form-control" name="bp_assessment[bp_value]" placeholder="BP e.g (120/90)"
        value="{{$row['bp_value'] ?? ''}}"/>
    </div>

    <div class="pull-right align-items-end">
        <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
            <button class="btn btn-primary nextBtn mx-2" type="button" >Next</button>
            <button class="btn btn-primary prevBtn" type="button">Previous</button>
        </div>
    </div>
</div>