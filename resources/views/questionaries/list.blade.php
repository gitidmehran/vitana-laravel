@extends('./layout/layout')

@section('content')

<div class="container-fluid mt-3">
	<div class="card">
		<div class="card-header">
			<a class="btn btn-primary" href="{{url($action.'/create')}}">
			  New Patient {{@$singular}}
			</a>
			<div class="card-body">
				<table id="example" class="table table-striped" style="width:100%">
					<thead>
						<tr>
							<th>#</th>
							<th>Serial No.</th>
							<th>Patient Name</th>
							<th>DOB</th>							
							<th>Contact</th>							
							<th>Program</th>
							<th width="10%">Status</th>			
							<th>Screening Date</th>
							<th>Date of Service</th>							
							<th width="7%">Actions</th>
						</tr>
					</thead>
					<tbody class="list">
						@foreach ($list as $key => $val)
						<tr class="list_{{$val['id']}}">
							<th>{{++$key}}</th>
							<td>{{$val['serial_no']}}</td>
							<td>{{@$val['patient']['name'] ?? ''}}</td>
										
							<td>{{date('m/d/Y',strtotime($val['patient']['dob'])) ?? ''}}</td>
							{{-- <td>{{$val['patient']['dob'] ?? ''}}</td> --}}
							<td>{{$val['patient']['contact_no'] ?? ''}}</td>
							<td>{{$val['program']['short_name'] ?? ''}}</td>


							<td width="10%">
								<div class="dropdown">
								  <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								    <i class="bi bi-gear"></i> Status
								  </button>
								  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="min-width: 6rem!important;">
								    <a class="dropdown-item tooltip1" >
										<i class="bi bi-pencil-square "></i> PSC <span class="tooltiptext1"> &nbsp Pre-Screening Completed &nbsp</span>
									</a>

									<a class="dropdown-item tooltip1">
										<i class="bi bi-trash"></i> Seen <span class="tooltiptext1">&nbsp Seen by Dr. {{$val['user']['name'] ?? ''}} &nbsp</span>
									</a>
									<?php if (!empty($val['signed_date'])) {?>
									<a class="dropdown-item tooltip1" >
										<i class="bi bi-file-earmark-pdf"></i> Signed <span class="tooltiptext1">&nbsp Signed by Dr. {{$val['user']['name'] ?? '' }}&nbsp</span>
									</a>
								<?php }else{ ?>
									<a class="dropdown-item tooltip1" >
										<i class="bi bi-file-earmark-pdf"></i> Signed <span class="tooltiptext1">&nbsp Incompleted Job &nbsp</span>
									</a>
									<?php } ?>
								  </div>
								</div>
							
							</td>
							<td>{{date('m/d/Y',strtotime($val['created_at']))}}</td>

							<td>{{date('m/d/Y',strtotime($val['date_of_service']))}}</td>
							<td>
								<div class="dropdown">
								  <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								    <i class="bi bi-gear"></i> Actions
								  </button>
								  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
								    <a 
								    	class="dropdown-item" 
								    	href="{{url($action.'/'.$val['id'].'/edit')}}"
								    >
										<i class="bi bi-pencil-square"></i> Edit
									</a>
									<a 
								    	class="dropdown-item delete" 
								    	href="javascript:void(0);"
								    	data-url="{{url($action.'/delete/'.$val['id'])}}" 
								    	data-remove="list_{{$val['id']}}"
								    >
										<i class="bi bi-trash"></i> Delete
									</a>
									<hr>
									<a 
								    	class="dropdown-item" 
								    	href="{{url('/dashboard/reports/analytics-report/'.$val['serial_no'])}}"
								    	target="_blank" 
								    >
										<i class="bi bi-file-earmark-pdf"></i> View AWV Care Plan
									</a>
									<a 
								    	class="dropdown-item" 
								    	href="{{url('/dashboard/reports/download-analyticalreport-pdf/'.$val['serial_no'])}}"
								    >
										<i class="bi bi-download"></i> Download AWV Care Plan
									</a>
									<hr>
									<a 
								    	class="dropdown-item" 
								    	href="{{url('/dashboard/reports/core-report/'.$val['serial_no'])}}"
								    	target="_blank" 
								    >
										<i class="bi bi-file-earmark-pdf"></i> View AWV Questionnaire
									</a>
									<a 
								    	class="dropdown-item" 
								    	href="{{url('/dashboard/reports/download-fullreport-pdf/'.$val['serial_no'])}}"
								    >
										<i class="bi bi-download"></i> Download AWV Questionnaire
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
