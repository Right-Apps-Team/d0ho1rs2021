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
					<div class="col-md-3 hide-div">
						<img src="{{asset('ra-idlis/public/img/doh2.png')}}" style="float: right; max-height: 118px; padding-left: 20px;">
					</div>
					<div class="col-md-6">
						<h6 class="card-title text-center font-weight-bold">Republic of the Philippines</h6>
						<h5 class="card-title text-center font-weight-bold">Department of Health</h5>
						<!-- <h5 class="card-title text-center font-weight-bold">{{((isset($director->certificateName)) ? $director->certificateName : 'REGION')}}</h5> -->
						<h5 class="card-title text-center font-weight-bold" >
							<?=(isset($retTable[0]->office) && !empty($retTable[0]->office)? $retTable[0]->office : '')?><br />
							<?=(isset($retTable[0]->address) && !empty($retTable[0]->address)? $retTable[0]->address : '')?><br />
							<?=(isset($retTable[0]->iso_desc) && !empty($retTable[0]->iso_desc)? $retTable[0]->iso_desc : '')?>
						</h5>
					
						{{-- <h6 class="card-subtitle mb-2 text-center text-muted text-small">doholrs@gmail.com</h6> --}}
					</div>
					<div class="col-md-3 hide-div">
						<img src="{{asset('ra-idlis/public/img/doh2.png')}}" style="float: left; max-height: 118px; padding-left: 20px;">
					</div>
				</div>
			</div>
			<div class="card-body">
				<br>
				<p class="text-muted" style="float: left;">CON No. {{date('Y')}}-{{str_pad(((isset($retTable[0]->appid)) ? $retTable[0]->appid : '_1'), 3, '0', STR_PAD_LEFT)}}</p><br><br>
				<h1 class="text-center">CERTIFICATE OF NEED</h1><br>
				<h5 class="text-center">is herby granted to</h5><br>
				<h3 class="text-center text-uppercase"><strong>{{((isset($retTable[0]->facilityname)) ? $retTable[0]->facilityname : "CURRENT_FACILITY")}}</strong></h3><br>
				<h5 class="text-center">located at</h5><br>
				<h4 class="text-center"><strong>{{((isset($retTable[0])) ? ($retTable[0]->rgn_desc.', '.$retTable[0]->provname.', '.$retTable[0]->cmname.', '.$retTable[0]->brgyname.', '.$retTable[0]->street_name) : "CURRENT_LOCATION")}}</strong></h4><br>
				<br>
				<div class="row">
					<div class="col-md-6">
						<p style="float: right;">Level of Hospital :</p>
					</div>
					<div class="col-md-6">
						<p><strong>{{((isset($serviceId)) ? (str_replace(',', ', ', $serviceId)) : 'LEVEL_1')}}</strong></p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<p style="float: right;">Bed Capacity :</p>
					</div>
					<div class="col-md-6">
						<p><strong>{{((isset($otherDetails->ubn)) ? abs($otherDetails->ubn) : (isset($retTable[0]->noofbed) ? abs($retTable[0]->noofbed) : 0) )}} Bed(s)</strong></p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<p style="float: right;">Date Issued :</p>
					</div>
					<div class="col-md-6">
						<p><strong>{{((isset($retTable[0]->t_date)) ? date("F j, Y", strtotime($retTable[0]->t_date)) : 'DATE_ISSUED')}}</strong></p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<p style="float: right;">Validity Period :</p>
						<!-- <p style="float: right;">Validity Until :</p> -->
					</div>
					<div class="col-md-6">
						<p><strong>{{((isset($retTable[0]->t_date)) ? date("F j, Y", strtotime($retTable[0]->t_date)) : 'DATE_ISSUED')}} to {{((isset($retTable[0]->t_date)) ? date("F j, Y", ((strtotime($retTable[0]->t_date)-(86400*2))+15552000)) : 'DATE_ISSUED')}}</strong></p>
					</div>
				</div>
				<br><br>
				<h5 class="text-uppercase text-center text-muted">By Authority of the Secretary of Health:</h5>
				<br><br><br>
				<h6 class="text-uppercase text-center" style="font-size: 40px;"><strong>{{$director->directorInRegion}}{{-- CORAZON I. FLORES, MD, MPH, CESO IV,Director IV --}}{{-- {{((isset($sec_name)) ? $sec_name->sec_name : 'DIRECTOR')}} --}}</strong></h6>
				<p class="text-small text-center text-muted">{{$director->pos}}</p>
			</div>
			<div class="card-footer">
				<p class="text-muted text-small" style="float: left; padding: 0; margin: 0;">
					{{-- <iframe src="{{asset('ra-idlis/resources/views/client1/qrcode/index.php')}}?data={{asset('client1/certificates/view/external/')}}/{{$retTable[0]->appid}}" style="border: none !important; height: 150px; width: 150px;"></iframe> --}}
					<iframe src="{!!url('qrcode/'.$retTable[0]->appid )!!}" style="border: none !important; height: 230px; width: 260px;"></iframe>
				</p>
				<p class="text-muted text-small" style="float: right; padding: 0; margin: 0;">© All Rights Reserved {{date('Y')}}</p>
			</div>
		</div><br>
	</div>
</body>
@endsection