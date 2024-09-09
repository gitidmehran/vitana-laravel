<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="physical_exam">
    <h3> Physical Exam </h3>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            General
        </label>
        
        <select class="form-select" name="physical_exam[general]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['general'] == $val) selected @endif > {{$val}} </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            Eyes
        </label>
        
        <select class="form-select" name="physical_exam[eyes]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['eyes'] == $val) selected @endif > {{$val}} </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            Neck
        </label>
        
        <select class="form-select" name="physical_exam[neck]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['neck'] == $val) selected @endif > {{$val}} </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            Lungs
        </label>
        
        <select class="form-select" name="physical_exam[lungs]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['lungs'] == $val) selected @endif > {{$val}} </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            Heart
        </label>
        
        <select class="form-select" name="physical_exam[heart]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['heart'] == $val) selected @endif > {{$val}} </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            Neuro
        </label>
        
        <select class="form-select" name="physical_exam[neuro]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['neuro'] == $val) selected @endif > {{$val}} </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            Extremeties
        </label>
        
        <select class="form-select" name="physical_exam[extremeties]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['extremeties'] == $val) selected @endif > {{$val}} </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            GI
        </label>
        
        <select class="form-select" name="physical_exam[gi]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['gi'] == $val) selected @endif > {{$val}} </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            Ears
        </label>
        
        <select class="form-select" name="physical_exam[ears]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['ears'] == $val) selected @endif > {{$val}} </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            Nose
        </label>
        
        <select class="form-select" name="physical_exam[nose]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['nose'] == $val) selected @endif > {{$val}} </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            Throat
        </label>
        
        <select class="form-select" name="physical_exam[throat]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['throat'] == $val) selected @endif > {{$val}} </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            Skin
        </label>
        
        <select class="form-select" name="physical_exam[skin]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['skin'] == $val) selected @endif > {{$val}} </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            Oral cavity
        </label>
        
        <select class="form-select" name="physical_exam[oral_cavity]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['oral_cavity'] == $val) selected @endif> {{$val}} </option>
            @endforeach
        </select>
    </div>
    
    <div class="form-group mb-3 col-4">
        <label class="control-label">
            MS
        </label>
        
        <select class="form-select" name="physical_exam[ms]" >
            @foreach (Config('constants.condition_options') as $val)
                <option value="{{$val}}" @if (@$row['ms'] == $val) selected @endif > {{$val}} </option>
            @endforeach
        </select>
    </div>

    <div class="pull-right align-items-end">
        <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
            <button class="btn btn-primary nextBtn mx-2" type="button" >Next</button>
            <button class="btn btn-primary prevBtn" type="button">Previous</button>
        </div>
    </div>
</div>