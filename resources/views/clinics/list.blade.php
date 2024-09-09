@extends('./layout/layout')

@section('content')

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
							<th>Name</th>
							<th>Short Name</th>							
							<th>Contact</th>							
														
							<th width="7%">Actions</th>
						</tr>
					</thead>
					<tbody class="list">
						@foreach ($list as $key => $val)
						<tr class="list_{{$val['id']}}">
							<th>{{++$key}}</th>
							<td>{{!empty($val['name'])?$val['name']:'-'}}</td>
							<td>{{!empty($val['short_name'])?$val['short_name']:'-'}}</td>
							<td>{{!empty($val['contact_no'])?$val['contact_no']:'-'}}</td>
					
							
							<td>
								{{-- <div class="btn-group" role="group" aria-label="Basic example">
								  <button type="button" class="btn btn-warning mx-2" data-bs-toggle="modal" data-bs-target="#data_modal" onclick="loadModal('{{$action}}/{{$val['id']}}/edit')">Edit</button>
								  <button type="button" data-url="{{url($action.'/delete/'.$val['id'])}}" data-remove="list_{{$val['id']}}" class="btn btn-danger delete">Delete</button>
								</div> --}}
								<div class="dropdown">
								  <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								    Actions
								  </button>
								  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
								    <a 
								    	class="dropdown-item" 
								    	href="#" 
								    	data-bs-toggle="modal" 
								    	data-bs-target="#data_modal" 
								    	onclick="loadModal('{{$action}}/{{$val['id']}}/edit')"
								    >
										Edit
									</a>
									<a 
								    	class="dropdown-item delete" 
								    	href="#" 
								    	data-url="{{url($action.'/delete/'.$val['id'])}}" 
								    	data-remove="list_{{$val['id']}}"
								    >
										Delete
									</a>
									
								  </div>
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
