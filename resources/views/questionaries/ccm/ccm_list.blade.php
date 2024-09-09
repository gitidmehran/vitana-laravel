@extends('./layout/layout')

@section('content')

<div class="container-fluid mt-3">
	<div class="card">
		<div class="card-header">
			<a class="btn btn-primary" href="{{url($action.'/create')}}">
			  New Patient {{@$singular}}
			</a>
			<div class="card-body">
				<table class="table">
					<thead>
						<tr>
							<th>#</th>
							<th>Serial No.</th>
							<th>Patient Name</th>							
							<th>Patient Contact</th>							
							<th>Patient Gender</th>							
							<th>Program</th>							
							<th>Submitted date</th>							
							<th width="7%">Actions</th>
						</tr>
					</thead>
					<tbody class="list">
						@foreach ($list as $key => $val)
						<tr class="list_{{$val['id']}}">
							<th>{{++$key}}</th>
							<td>{{$val['serial_no']}}</td>
							<td>{{$val['patient']['name'] ?? ''}}</td>
							<td>{{$val['patient']['contact_no'] ?? ''}}</td>
							<td>{{$val['patient']['gender'] ?? ''}}</td>
							<td>{{$val['program']['name'] ?? ''}} ({{$val['program']['short_name'] ?? ''}})</td>
							<td>{{date('Y-m-d',strtotime($val['created_at']))}}</td>
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
									<a 
								    	class="dropdown-item" 
								    	href="{{url('/dashboard/reports/analytics-report/'.$val['serial_no'])}}"
								    	target="_blank" 
								    >
										<i class="bi bi-file-earmark-pdf"></i> Print Report
									</a>
									<a 
								    	class="dropdown-item" 
								    	href="{{url('/dashboard/reports/download-analyticalreport-pdf/'.$val['serial_no'])}}"
								    >
										<i class="bi bi-download"></i> Downlaod Analytics Report
									</a>
									<a 
								    	class="dropdown-item" 
								    	href="{{url('/dashboard/reports/core-report/'.$val['serial_no'])}}"
								    	target="_blank" 
								    >
										<i class="bi bi-file-earmark-pdf"></i> Print Full Report
									</a>
									<a 
								    	class="dropdown-item" 
								    	href="{{url('/dashboard/reports/download-fullreport-pdf/'.$val['serial_no'])}}"
								    >
										<i class="bi bi-download"></i> Downlaod Patient Survey Report
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
