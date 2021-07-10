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
		.watermarked {
			position: relative;
		}
		.watermarked:after {
			content: "";
			display: block;
			width: 90%;
			height: 100%;
			position: absolute;
			top: 0px;
			left: 0px;
			background-image: url("{{asset('ra-idlis/public/img/watermark/hfsrb.png')}}");
			background-position: center;
			background-repeat: no-repeat;
			background-size: cover;
			opacity: 0.25;
			z-index: 0;
			-webkit-print-color-adjust: exact;
		}
	</style>
	<div class="container mt-5">
		<div class="card">
			<div class="card-header">
				<div class="row">
					<div class="col-md-3 hide-div">
						<img src="{{asset('ra-idlis/public/img/doh2.png')}}" style="float: right; max-height: 118px; padding-left: 20px;">
					</div>
					<div class="col-md-6">
						<span class="card-title text-center font-weight-bold" style="font-family: Arial;font-size: 12pt"><center><strong>Republic of the Philippines</strong></center></span>
						<span class="card-title text-center font-weight-bold" style="font-family: Arial;font-size: 13pt"><center><strong>DEPARTMENT OF HEALTH</strong></center></span>
						<span class="card-title text-center font-weight-bold" style="font-family: Arial;font-size: 14pt"><center><strong>{{((isset($director->certificateName)) ? $director->certificateName : "CURRENT_OWNER")}}</strong></center></span>
						{{-- <h5 class="card-title text-center">((isset($subUserTbl)) ? $subUserTbl[0]->rgn_desc : 'REGION')</h5> --}}
						{{-- <h6 class="card-subtitle mb-2 text-center text-muted text-small">doholrs@gmail.com</h6> --}}
					</div>
					<div class="col-md-3 hide-div">
						&nbsp;
						{{-- <img src="{{asset('ra-idlis/public/img/doh2.png')}}" style="float: left; max-height: 118px; padding-left: 20px;"> --}}
					</div>
				</div>
			</div>
			<div class="card-body watermarked">
				<br>
				<span class="card-title text-center" style="font-family: ArialUnicodeMs;font-size: 42pt"><center><strong>LICENSE TO OPERATE</strong></center></span><br>
				<br>
				<br>
				<div class="row">	
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Owner
					</div>
					<div class="col-md-1" style="display: inline">
						:
					</div>
					<div class="col-md-6" style="float:left;display: inline;font-family: Century Gothic; font-size: 13pt">
						{{((isset($retTable[0]->owner)) ? $retTable[0]->owner : "CURRENT_OWNER")}}
					</div>	
				</div>
				{{-- <div class="row">
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Operated/Managed <br>
						   by (if applicable)
					</div>
					<div class="col-md-1" style="display: inline">
						<br><center>:</center></div>
					<div class="col-md-6" style="float:left;display: inline;font-family: Century Gothic; font-size: 13">
						&nbsp;
					</div>
				</div> --}}
				<div class="row">
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Name of Facility
					</div>
					<div class="col-md-1" style="display: inline">
						:</div>
					<div class="col-md-5" style="float:left;display: inline;font-family: Century Gothic; font-size: 13">
						<strong>{{((isset($retTable[0]->facilityname)) ? $retTable[0]->facilityname : "CURRENT_FACILITY")}}</strong>
					</div>
					<div class="col-md-1" style="display: inline">
						&nbsp;</div>
				</div>
				<div class="row">
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Type of Health Facility
					</div>
					<div class="col-md-1" style="display: inline">
						:</div>
					<div class="col-md-5" style="float:left;display: inline;font-family: Century Gothic; font-size: 13">
						{{((isset($facname)) ? $facname : "No Health Service")}}
						<!-- {{((isset($facilityTypeId)) ? $facilityTypeId : "No Health Service")}} -->
					</div>
					<div class="col-md-1" style="display: inline">
						&nbsp;</div>
				</div>
				<div class="row">
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Service Capabilities
					</div>
					<div class="col-md-1" style="display: inline">
						:</div>
					<div class="col-md-5" style="float:left;display: inline;font-family: Century Gothic; font-size: 13">
						{{((isset($services->facname)) ? $services->facname : "No Health Service")}}
					</div>
					<div class="col-md-1" style="display: inline">
						&nbsp;</div>
				</div>
				<div class="row">
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Classification
					</div>
					<div class="col-md-1" style="display: inline">
						:</div>
					<div class="col-md-5" style="float:left;display: inline;font-family: Century Gothic; font-size: 13">
						{{((isset($retTable[0]->classname)) ? $retTable[0]->classname : "NOT DEFINED")}}
					</div>
					<div class="col-md-1" style="display: inline">
						&nbsp;</div>
				</div>
				<div class="row">
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Location
					</div>
					<div class="col-md-1" style="display: inline">
						:</div>
					<div class="col-md-5" style="float:left;display: inline;font-family: Century Gothic; font-size: 13">
					{{((isset($retTable[0])) ? ($retTable[0]->street_name.', '.$retTable[0]->street_number.', '.$retTable[0]->brgyname.', '.$retTable[0]->cmname.', '.$retTable[0]->provname.' '.$retTable[0]->rgn_desc) : 'No Location.')}}
						<!-- {{ucwords(((isset($retTable[0])) ? ($retTable[0]->rgn_desc.', '.$retTable[0]->provname.', '.$retTable[0]->cmname.', '.$retTable[0]->brgyname.', '. $retTable[0]->street_number. $retTable[0]->street_name.' '.$retTable[0]->street_number) : "CURRENT_LOCATION"))}} -->
					</div>
					<div class="col-md-1" style="display: inline">
						&nbsp;</div>
				</div>
				@isset($retTable[0]->facmdesc)
				{{-- <div class="row">
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Institutional Character
					</div>
					<div class="col-md-1" style="display: inline;float: left">
						:</div>
					<div class="col-md-5" style="float:left;display: inline;font-family: Century Gothic; font-size: 13">
						<strong>{{((isset($retTable[0]->facmdesc)) ? $retTable[0]->facmdesc : "CURRENT_FACILITY")}}</strong>
					</div>
					<div class="col-md-1" style="display: inline">
						&nbsp;</div>
				</div>	 --}}
				{{-- last update: 11/20/2019 by atty. Flores --}}
				@endisset
				
				@if(isset($otherDetails[1]) && $otherDetails[1])
				<div class="row">
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Authorized Bed Capacity
					</div>
					<div class="col-md-1" style="display: inline;float: left">
						:</div>
					<div class="col-md-5" style="float:left;display: inline;font-family: Century Gothic; font-size: 13">
						<strong>{{((isset($otherDetails[0]->noofbed)) ? $otherDetails[0]->noofbed : "CURRENT_FACILITY")}}</strong>
					</div>
					<div class="col-md-1" style="display: inline">
						&nbsp;</div>
				</div>
				@endif

				{{-- <div class="row">
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Authorized Ambulance Units
					</div>
					<div class="col-md-1" style="display: inline;float: left">
						:</div>
					<div class="col-md-5" style="float:left;display: inline;font-family: Century Gothic; font-size: 13">
						@if(isset($retTable[0]->plate_number) && isset($retTable[0]->ambtyp))
						@php
							$ambType = json_decode($retTable[0]->ambtyp);
							$plateNum = json_decode($retTable[0]->plate_number);
							if(count($ambType) == count($plateNum)){
								$amb = array_combine($ambType, $plateNum);
								foreach($amb as $ambServ => $val){
									if($ambServ == 1){
										echo '1, '.$val . ' ' . $ambServ;
									}
								}
							}
						@endphp
						@endif
					</div>
					<div class="col-md-1" style="display: inline">
						&nbsp;</div>
				</div>	 --}}
				<div class="row">
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						License Number
					</div>
					<div class="col-md-1" style="display: inline;float: left">
						:</div>
					<div class="col-md-5" style="float:left;display: inline;font-family: Century Gothic; font-size: 13">
						{{$retTable[0]->licenseNo}}
					</div>
					<div class="col-md-1" style="display: inline">
						&nbsp;</div>
				</div>
				<div class="row">
					<div class="col-md-2" style="">&nbsp;</div>
					<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
						Validity of License
					</div>
					<div class="col-md-1" style="display: inline;float: left">
						:</div>
					<div class="col-md-5" style="float:left;display: inline;font-family: Century Gothic; font-size: 13">
						@if(isset($otherDetails[0]) && isset($otherDetails[0]->valto))
						{{Date('F j, Y',strtotime($otherDetails[0]->valfrom))}} - {{Date('F j, Y',strtotime($otherDetails[0]->valto))}}
						@endif
					</div>
					<div class="col-md-1" style="display: inline">
						&nbsp;</div>
				</div>

				{{-- @if($retTable[0]->noofsatellite > 0)
					<div class="row">
						<div class="col-md-2" style="">&nbsp;</div>
						<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
							FDA Status
						</div>
						<div class="col-md-1" style="display: inline;float: left">
							:</div>
						<div class="col-md-5" style="float:left;display: inline;font-family: Century Gothic; font-size: 13">
							{{($retTable[0]->FDAstatus == null ? 'Not Yet Evaluated' :($retTable[0]->FDAstatus == 'A') ? 'FDA Certified' : 'FDA Certification Rejected')}}
						</div>
						<div class="col-md-1" style="display: inline">
							&nbsp;</div>
					</div>
					@if($retTable[0]->FDAstatus == 'A' && isset($retTable[0]->fdacoc))
					<div class="row">
						<div class="col-md-2" style="">&nbsp;</div>
						<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
							COC Number
						</div>
						<div class="col-md-1" style="display: inline;float: left">
							:</div>
						<div class="col-md-5" style="float:left;display: inline;font-family: Century Gothic; font-size: 13">
							{{$retTable[0]->fdacoc}}
						</div>
						<div class="col-md-1" style="display: inline">
							&nbsp;</div>
					</div>
					@endif
				@endif --}}
					@if(count($addons) > 0)
					<div class="row mt-3">
						<div class="col-md-2" style="">&nbsp;</div>
						<div class="col-md-3" style="font-family: Century Gothic; font-size: 11pt">
							Other Services Offered
						</div>
					</div>
					<div class="row">
						<div class="col-md-2" style="">&nbsp;</div>
						<div class="col-md-3 pl-5 mt-3" style="font-family: Century Gothic; font-size: 11pt">
							@foreach($addons as $add)
								{{$add}}
							@endforeach
						</div>
					</div>
					@endif


				<div class="row">
					<div class="col-md-5">
						<p class="text-muted text-small" style="float: left; margin-top: 80px;">
							{{-- <iframe src="{{asset('ra-idlis/resources/views/client1/qrcode/index.php')}}?data={{asset('client1/certificates/view/external/')}}/{{$retTable[0]->appid}}" style="border: none !important; height: 150px; width: 150px;"></iframe> --}}
							<iframe src="{!!url('qrcode/'.$retTable[0]->appid )!!}" style="border: none !important; height: 230px; width: 260px;"></iframe>
						</p>
					</div>
					<div class="col-md-7">
						<br><br><br>
						<div class="row">
							{{-- <div class="col-md-5">&nbsp;</div> --}}
							<div class="col-md-12" style="font-family: ArialUnicodeMs; font-size:11pt;">
								<strong>By Authority of the Secretary of Health:</strong>
							</div>
						</div><br><br>
						<div class="row">
							{{-- <div class="col-md-4">&nbsp;</div> --}}
							<div class="col-md-12" style="font-family: NewGothicCenturySchoolBook; font-size:23pt;">
								<strong ><center>{{ucwords($director->directorInRegion)}}</center></strong>
							</div>
						</div>
						<div class="row">
							{{-- <div class="col-md-4">&nbsp;</div> --}}
							<div class="col-md-12" style="font-family: NewGothicCenturySchoolBook; font-size:14pt;">
								<center><b>{{$director->pos}}</b></center>
							</div>
						</div>
						<br><br><br>
					</div>
				</div>
				<!-- <h5 class="text-uppercase text-center text-muted">By Authority of the Secretary of Health:</h5>
				<br><br><br>
				<h6 class="text-uppercase text-center"><strong>{{--((isset($sec_name)) ? $sec_name->sec_name : 'DIRECTOR')--}}</strong></h6>
				<p class="text-small text-center text-muted">Director IV</p> -->

			</div>
			<div class="card-footer">
			
			<center><i><b>This license is renewable annually and subject to suspension or revocation if the hospital is found violating RA 4226 and related
issuances.</b></i></center>
<br/><br/>
				<p class="text-muted text-small" style="float: right; padding: 0; margin: 0;">© All Rights Reserved {{date('Y')}}</p>
			</div>
		</div><br>
	</div>
</body>
@endsection