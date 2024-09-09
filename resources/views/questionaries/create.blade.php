@extends('layout.layout')

@section('content')
<!-- MultiStep Form -->
<div class="container-fluid mt-3">
    <div class="card">
        <form action="{{url($action)}}" method="post" class="make_ajax">
            <div class="card-header bg-success"> Add New {{$singular}}</div>
            <div class="card-body">
                <div class="row my-2">
                  <div class="col-md-6">
                    <label class="control-label">Select Patient <span style="color:red;">*</span></label>
                    <select 
                        class="form-select" 
                        name="patient_id" 
                        id="patient_id" 
                        onchange="autoFillage(this, {{!! !empty($patients) ? json_encode($patients) : '' }})"
                        >
                            <option value="">Select Patient</option>
                            @if(!empty($patients))
                            @foreach($patients as $key => $val)
                                <option 
                                    value="{{$val['id']}}"
                                    @if($val['id']==$patient_id) selected @endif
                                >
                                    {{@$val['first_name'].' '.@$val['mid_name'].' '.@$val['last_name']}}
                                </option>
                            @endforeach
                            @endif
                    </select>
                    <span class="patient-error d-none"></span>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="control-label">Select Program <span style="color:red;">*</span></label>
                    <select class="form-select" name="program_id" id="program_id" >
                        <option value="">Select Programs</option>
                        @if(!empty($programs))
                        @foreach($programs as $key => $val)
                            <option value="{{$val['id']}}" @if($val['id']==$program_id) selected @endif>{{$val['name']}}</option>
                        @endforeach
                        @endif
                    </select>
                </div>                
                
                <div class="col-md-6">
                    <label class="control-label">Date of Service <span style="color:red;">*</span></label>
                    <input type="text" class="form-control datepickerdate"
                    name="date_of_service" id="date_of_service" placeholder="Date of service"
                    value="{{$date_of_service}}"/>
                    <span class="date-error d-none"></span>
                </div>                
            </div>
            <div class="row">
                <div class="col mt-3">
                    <div class="loader d-none">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div>
                        </div>
                    </div>
                    @if(!empty($program_id))
                        @include($path,['list'=>$questions])
                    @else
                        <div class="program-data"></div>
                    @endif
                </div>
            </div>
        </form>
    </div>       
</div>
@endsection
@section('footer')
<script type="text/javascript">
    $(document).ready(function(){
        handleStepChange('{{(int)$last_step+1}}')
        $(document).on('change','#program_id',async function(){
            const patient = $('#patient_id').val();
            const dateOfService = $('#date_of_service').val();
            const errspan = $('.patient-error');
            const dateErrorSpan = $('.date-error');
            $('#patient_id,#date_of_service').removeClass('is-invalid');
            $(errspan).addClass('d-none');
            $(dateErrorSpan).addClass('d-none');
            if(patient===''){
                $(errspan).removeClass('d-none').addClass('text-danger').text('Please Select Patient First.');
                $('#patient_id').addClass('is-invalid');
                $(this).val('');
            }
            if(dateOfService===''){
                $(dateErrorSpan).removeClass('d-none').addClass('text-danger').text('Please Select Patient First.');
                $('#date_of_service').addClass('is-invalid');
                $(this).val('');
            }
            if(patient ==='' || dateOfService ===''){
                return false;
            }
            $('.loader').removeClass('d-none');
            $('.program-data').html('');
            const data = {patient_id: patient,program_id:$(this).val(),'date_of_service':dateOfService};
            const response = await makeAjaxCall(data,'get-programm-data');
            $('.loader').addClass('d-none')
            if(response.success){
                $('.program-data').html(response.view);
                $('.setup-content').addClass('d-none');
                $('#step-1').removeClass('d-none')
            } else {
                toastr["error"](response.errors);
            }
        });
    });
</script>
@endsection
