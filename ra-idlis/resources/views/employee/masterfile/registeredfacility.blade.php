@if (session()->exists('employee_login'))
@extends('mainEmployee')
@section('title', 'Registered Facility')
@section('content')
<div class="content p-4">
	<div class="card">
		<div class="card-header bg-white font-weight-bold">
			Registered Falities
			<span class="AP001_add" style="float: right;"><a href="#" title="Add New Registered Facility" data-toggle="modal" data-target="#myModal"><button class="btn-primarys"><i class="fa fa-plus-circle"></i>&nbsp;Add new</button></a></span>

		</div>
		<div class="card-body">
			<table class="table display" id="example" style="overflow-x: scroll;">
				<thead>
					<tr>
						<th style="width:  auto">NHF CODE</th>
						<th style="width:  auto">Facility Name</th>
						<th style="width:  auto">Facility Type</th>
						<th style="width: auto;text-align: center">Owner</th>
						<th style="width: auto;text-align: center">Region</th>
						<th style="width:  auto">
							<center>Options</center>
						</th>
					</tr>
				</thead>
				<tbody>
				@if (isset($data))
					@foreach ($data as $d)
						<tr>
							<td scope="row"> {{$d->nhfcode}}</td>
							<td>{{$d->facilityname}}</td>
							<td>{{$d->hgpdesc}}</td>
							<td>{{$d->owner}}</td>
							<td>{{$d->rgn_desc}}</td>
							<td></td>
						</tr>
					@endforeach	
				@endif
				</tbody>
			</table>
		</div>
	</div>
</div>
@include('employee.masterfile.registeredfacility_form')
<script>
	$(document).ready(function() {
		$('#example').DataTable();
	});

	const base_url = '{{URL::to('/')}}';
</script>



@endsection
@else
<script type="text/javascript">
	window.location.href = "{{ asset('employee') }}";
</script>
@endif