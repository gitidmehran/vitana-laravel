@extends('./layout/layout')

@section('content')

@php 
   $deleted_date = \Carbon\Carbon::now(); 
@endphp
<div class="container-fluid mt-3">
	<div class="card">
		<div class="card-header">
			<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#data_modal" onclick="loadModal('{{$action}}/create');">
			  Add New {{@$singular}}
			</button>
			<div class="card-body">
				<table id="example" class="table table-striped" style="width:100%">
					<thead>
						<tr>
							<th>#</th>
							<th>Account</th>
							<th>Name</th>
							<th>Contact</th>
							<th>DOB</th>						
							<th>Age</th>							
							<th>Insurance</th>							
							<th>Address</th>
							<th>Primary Care Physician</th>
							<th style="width: 75px!important;">Status <input type="checkbox" id="Inactive_patients"style="margin-left: 5px;"  @if($active==2) checked="checked" @endif /></th>							
							<th width="5%">Actions</th>
						</tr>
					</thead>
					<tbody class="list">
						@foreach ($list as $key => $val)
						<tr class="list_{{$val['id']}}">
							<th>{{++$key}}</th>
							<td>{{!empty($val['identity'])?$val['identity']:'-'}}</td>
							<td>{{strtoupper($val['first_name'].' '.$val['mid_name'].' '.$val['last_name'])}}</td>
							<td>{{!empty($val['contact_no'])?$val['contact_no']:'-'}}</td>
							
							<td>{{date('m/d/Y',strtotime($val['dob']))}}</td>
							<td>{{!empty($val['age'])?$val['age']:'-'}}</td>
							<td>{{!empty($val['insurance'])?$val['insurance']['name']:'-'}}</td>
							
							<td>{{!empty($val['address'])?$val['address'].' '.$val['address_2'].' '.$val['zipCode'].' '.$val['state'].' '.$val['city']:'-'}}</td>
							

							<td>{{!empty($val['doctor'])?$val['doctor']['first_name'].' '.$val['doctor']['mid_name'].' '.$val['doctor']['last_name']:'-'}}
							</td>
							<td>
								<select name="" id="status_" class="form-control" data-id="{{ $val['id'] }}" >
                                	<option value="Active"  @if(empty($val['deleted_at'])) selected @endif>Active</option>
                                    <option value="Inactive" @if(!empty($val['deleted_at'])) selected @endif>Inactive</option>
                                </select>
							</td>
							<td>
							

								<div class="btn-group" role="group" aria-label="Basic example">


								  <button type="button" class="btn btn-warning mx-2" data-bs-toggle="modal" data-bs-target="#data_modal" onclick="loadModal('{{$action}}/{{$val['id']}}/edit')">Edit</button>
								  <!-- <button type="button" data-url="{{url($action.'/delete/'.$val['id'])}}" data-remove="list_{{$val['id']}}" class="btn btn-danger delete">Delete</button> -->
								</div>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>




@endsection
@section('footer')
<script type="text/javascript">
$(document).ready(function () {
  $('#status_').on('change', function () {
    var selectValue = $(this).val();
//alert(selectValue);

    var id = $(this).data("id");
    var token = $("meta[name='csrf-token']").attr("content");
 //  alert(id);
    $.ajax(
    {
        
        url: "status_change/"+id,
        type: 'POST',
        data: {
            "id": id,
            "_token": token,
            selected: selectValue,
        },
        success: function (){
            toastr.success('Status record is Update successfully!!');
        }
    });

    $(this).closest('tr').remove();
   
	});

  //set initial state.
 //$('#Inactive_patients').val($(this).is(':checked'));
$('input[type=checkbox]').change(function(){
	let url = '{{url()->current()}}';
  if($(this).is(':checked')){
  	url=`${url}?active=2`
  	var selectValue = $(this).is(':checked')
  	//alert(selectValue);
    }else{
    	url=`${url}?active=1`
    }
  window.location.href=url;

});
  

 
});

  
</script>
@endsection
