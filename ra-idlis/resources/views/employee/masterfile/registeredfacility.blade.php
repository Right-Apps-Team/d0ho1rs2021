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
							<td>{{$d->rgn_desc}} {{$d->provname}} {{$d->cmname}}</td>
							<td><button class="btn-primarys" onclick="showData(
								'{{$d->facilityname}}',
								'{{$d->owner}}',
								'{{$d->facid}}',
								'{{$d->rgnid}}',
								'{{$d->street_number}}',
								'{{$d->street_name}}',
								'{{$d->zipcode}}',
								'{{$d->ownerMobile}}',
								'{{$d->ownerEmail}}',
								'{{$d->ocid}}',
								'{{$d->mailingAddress}}',
								'{{$d->approvingauthority}}',
								'{{$d->approvingauthoritypos}}',
								'{{$d->facmode}}',
								'{{$d->funcid}}',

							)" data-toggle="modal" data-target="#myModal"><i class="fas fa-eye"></i></button></td>
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

	function showData(facname, owner, facid, rgind,
	street_num,
	street_name,
	zip,
	ownerMobile,
ownerEmail,
ocid,
mailingAddress,
approving_authority_name,
approving_authority_pos,
facmode,
funcid,
	){
		$("#facility_name").val(facname);
		$("#facilitytype").val(facid);
		$("#region").val(rgind);
		$("#owner").val(owner);
		$("#street_num").val(street_num);
		$("#street_name").val(street_name);
		$("#zip").val(zip);
		$("#fac_mobile_number").val(ownerMobile);
		$("#fac_email_address").val(ownerEmail);
		$("#ocid").val(ocid);
		$("#official_mail_address").val(mailingAddress);
		$("#approving_authority_name").val(approving_authority_name);
		$("#approving_authority_pos").val(approving_authority_pos);
		$("#facmode").val(facmode);
		$("#funcid").val(funcid);

		
		$("#mainbtn").val(funcid);
}



</script>



@endsection
@else
<script type="text/javascript">
	window.location.href = "{{ asset('employee') }}";
</script>
@endif