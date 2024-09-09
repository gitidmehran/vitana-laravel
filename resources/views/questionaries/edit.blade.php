@extends('layout.layout')

@php
    $key = array_search($row['patient_id'], array_column($patients, 'id'));
    $patientAge = $patients[$key]['age'];
    $dateofService = \Carbon\Carbon::parse($row['date_of_service'])->format('m/d/Y');
@endphp

@section('content')
<!-- MultiStep Form -->
<div class="container-fluid mt-3">
    <div class="card">
        <form action="{{url($action.'/update/'.$row['id'])}}" method="post" class="make_ajax">
            <div class="card-header bg-success">{{$singular}}</div>
            <div class="card-body">
                <div class="row my-2">
                    <div class="col-md-6">
                        <label class="control-label">Select Patient <span style="color:red;">*</span></label>
                        <select class="form-select" name="patient_id" id="patient_id" disabled>
                            <option value="">Select Patient</option>
                            @if(!empty($patients))
                            @foreach($patients as $key => $val)
                                <option value="{{$val['id']}}" @if($val['id']==$row['patient_id']) selected @endif>{{$val['name']}}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="control-label">Select Program <span style="color:red;">*</span></label>
                        <select class="form-select" name="program_id" id="program_id" disabled>
                            <option value="">Select Programs</option>
                            @if(!empty($programs))
                            @foreach($programs as $key => $val)
                                <option value="{{$val['id']}}" @if($val['id']==$row['program_id']) selected @endif>{{$val['name']}}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="control-label">Date of Service <span style="color:red;">*</span></label>
                        <input type="text" class="form-control datepickerdate"
                        name="date_of_service" id="date_of_service" placeholder="Date of service"
                        value="{{$dateofService}}"/>
                        <span class="date-error d-none"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col mt-3">
                        @include('questionaries/'.$path.'/edit',['list' => $list])
                    </div>
                </div>
            </div>
        </form>
    </div>       
</div>
@endsection
@section('footer')
<script type="text/javascript">
    $(document).ready(function(){
        setTimeout(()=>{
            $('#step-1').removeClass('d-none')
        },2000)
    });
</script>
@endsection