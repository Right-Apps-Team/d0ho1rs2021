@extends('main')
@section('content')
@include('client1.cmp.__issuance')

<body>
	<style>
		@font-face {
			font-family: NewGothicCenturySchoolBook;
			src: url({{ asset('ra-idlis/public/fonts/NewCenturySchoolbook.ttf') }});
		}
		@font-face {
			font-family: ArialUnicodeMs;
			src: url({{ asset('ra-idlis/public/fonts/ARIALUNI.TTF') }});
		}
	</style>

	<div class="container mt-5">
		<div class="card">
			<div class="card-header">
				<div class="row">
					<div class="col-md-3 col-sm-12 hide-div">
						<img src="{{asset('ra-idlis/public/img/doh2.png')}}" style="float: right; max-height: 118px; padding-left: 20px;">
					</div>
					<div class="col-md-6">
						<span class="card-title text-center" style="font-family: Arial;font-size: 12pt"><center><strong>Republic of the Philippines</strong></center></span>
						<span class="card-title text-center" style="font-family: Arial;font-size: 13pt"><center><strong>DEPARTMENT OF HEALTH</strong></center></span>
						<span class="card-title text-center" style="font-family: Arial;font-size: 14pt"><center><strong>HEALTH FACILITIES AND SERVICES REGULATORY BUREAU</strong></center></span>
					</div>
					<div class="col-md-3 hide-div">
					</div>
				</div>
			</div>
			<div class="card-body">
				<div class="container w-auto">
					<a class="card-title text-center text-uppercase" style="font-family: ArialUnicodeMs;font-size: 20pt">
						<center>
							<strong>{{$retTable[0]->hfser_desc}}</strong>
						</center>
					</a>
				</div>
				<br>
				<div class="row">	
						<div class="col-md-2" style="">&nbsp;</div>
						<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
							Application Code
						</div>
						<div class="col-md-1 hide-div">
							<center>:</center>
						</div>
						<div class="col-md-6 font-weight-bold" style="float:left;display: inline;font-family: Century Gothic; font-size: 13pt">
							{{((isset($retTable[0]->hfser_id)) ? ucwords($retTable[0]->hfser_id.'R'.$retTable[0]->rgnid.'-'.$retTable[0]->appid) : "CURRENT_OWNER")}}
						</div>	
				</div>
				<div class="row">	
						<div class="col-md-2" style="">&nbsp;</div>
						<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
							Owner
						</div>
						<div class="col-md-1 hide-div">
							<center>:</center>
						</div>
						<div class="col-md-6 font-weight-bold" style="float:left;display: inline;font-family: Century Gothic; font-size: 13pt">
							{{((isset($retTable[0]->owner)) ? ucwords($retTable[0]->owner) : "CURRENT_OWNER")}}
						</div>	
				</div>
				<div class="row">	
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Health Facility Name
					</div>
					<div class="col-md-1 hide-div">
						<center>:</center>
					</div>
					<div class="col-md-6 font-weight-bold" style="float:left;display: inline;font-family: Century Gothic; font-size: 13pt">
						{{((isset($retTable[0]->facilityname)) ? ucwords($retTable[0]->facilityname) : "NOT DEFINED")}}
					</div>	
				</div>
				<div class="row">	
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Health Facility Address
					</div>
					<div class="col-md-1 hide-div">
						<center>:</center>
					</div>
					<div class="col-md-6 font-weight-bold" style="float:left;display: inline;font-family: Century Gothic; font-size: 13pt">
						{{((isset($retTable[0]->cmname)) ? (ucwords($retTable[0]->street_number . ' ' . $retTable[0]->street_name . ' ' . $retTable[0]->cmname . ' ' . $retTable[0]->provname)) : "NOT DEFINED")}}
					</div>	
				</div>
				<div class="row">	
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Service Capabilities
					</div>
					<div class="col-md-1 hide-div">
						<center>:</center>
					</div>
					<div class="col-md-6 font-weight-bold" style="float:left;display: inline;font-family: Century Gothic; font-size: 13pt">
						{{((isset($servCap)) ? implode(', ',$servCap)  : "NOT DEFINED")}}
					</div>	
				</div>
				@if(strtolower($retTable[0]->hfser_id) == 'lto' && isset($retTable[0]->facmdesc))
				<div class="row">	
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Institutional Character
					</div>
					<div class="col-md-1 hide-div">
						<center>:</center>
					</div>
					<div class="col-md-6 font-weight-bold" style="float:left;display: inline;font-family: Century Gothic; font-size: 13pt">
						{{((isset($retTable[0]->facmdesc)) ? $retTable[0]->facmdesc : "NOT DEFINED")}}
					</div>	
				</div>
				@endif

				@if(strtolower($retTable[0]->hfser_id) == 'ptc')
				<div class="row">	
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Scope of Work
					</div>
					<div class="col-md-1 hide-div">
						<center>:</center>
					</div>
					<div class="col-md-6 font-weight-bold" style="float:left;display: inline;font-family: Century Gothic; font-size: 13pt">
						{{((isset($retTable[0]->HFERC_swork)) ? $retTable[0]->HFERC_swork : "NOT DEFINED")}}
					</div>	
				</div>
				@endif

				@if(strtolower($retTable[0]->hfser_id) == 'ptc')
				<div class="row">	
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Number of Beds
					</div>
					<div class="col-md-1 hide-div">
						<center>:</center>
					</div>
					<div class="col-md-6 font-weight-bold" style="float:left;display: inline;font-family: Century Gothic; font-size: 13pt">
						{{((isset($otherDetails->ubn)) ? $otherDetails->ubn : (isset($retTable[0]->noofbed) ? $retTable[0]->noofbed : ''))}}
					</div>	
				</div>
				@endif

				@isset($retTable[0]->funcdesc)
				<div class="row">	
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Classification
					</div>
					<div class="col-md-1 hide-div">
						<center>:</center>
					</div>
					<div class="col-md-6 font-weight-bold" style="float:left;display: inline;font-family: Century Gothic; font-size: 13pt">
						{{$retTable[0]->funcdesc}}
					</div>	
				</div>
				@endisset
				@if(strtolower($retTable[0]->hfser_id) == 'lto')
				<div class="row">	
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						License Validity
					</div>
					<div class="col-md-1 hide-div">
						<center>:</center>
					</div>
					<div class="col-md-6 font-weight-bold" style="float:left;display: inline;font-family: Century Gothic; font-size: 13pt">
						{{((isset($otherDetails->valto)) ? $otherDetails->valto : "NOT DEFINED")}}
					</div>	
				</div>
					@isset($addons)
					@if(count($addons) > 0)
					<div class="row">
						<div class="col-md-2" style="">&nbsp;</div>
						<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
							Other Services
						</div>
					</div>
					<div class="row">
						<div class="col-md-2" style="">&nbsp;</div>
						<div class="col-md-3 pl-5 mt-3 font-weight-bold" style="font-family: Century Gothic; font-size: 11pt">
							@foreach($addons as $add)
								{{$add}}
							@endforeach
						</div>
					</div>
					@endif
					@endisset

				@endif
			</div>
			<div class="card-footer">
				<p class="text-muted text-small" style="float: right; padding: 0; margin: 0;">© All Rights Reserved {{date('Y')}}</p>
			</div>
		</div>
	</div>


</body>

@endsection