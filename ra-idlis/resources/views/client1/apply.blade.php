@extends('main')
@section('content')
@include('client1.cmp.__apply')
<style type="text/css">
	.legend {
	  background-color: #fff;
	  left: 80px;
	  padding: 20px;
	  border: 1px solid;
	}
	.legend h4 {
	  text-transform: uppercase;
	  font-family: sans-serif;
	  text-align: center;
	}
	.legend ul {
	  list-style-type: none;
	  margin: 0;
	  padding: 0;
	}
	.legend li { padding-bottom: 5px; }
	.legend span {
	  display: inline-block;
	  width: 12px;
	  height: 12px;
	  margin-right: 6px;
	}
	.ddi{
		color: #fff;
	}

</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>
	<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<body>
	@include('client1.cmp.nav')
	@include('client1.cmp.breadcrumb')
	@include('client1.cmp.msg')
	<div class="container mb-5">
			<div class="row mb-5">
				<div class="col-sm-4">
					<!-- <a class="btn btn-success btn-block" href="{{asset('client1/apply/new')}}" style="text-decoration: none;color:#fff; margin-top: 20%">Add new Application</a> -->
					<!-- <a 
						class="btn btn-info btn-block" 
						href="{{asset('client/dashboard/new-application')}}" 
						style="text-decoration: none;color:#fff; margin-top: 20%"
					>
						Add new Application (New Form)
					</a> -->
					<button
						class="btn btn-info btn-block"
						style="text-decoration: none;color:#fff; margin-top: 20%"
						data-toggle="modal" 
						data-target="#applicationTypeModal"
					>
						Add new Application (New Form)
					</button>
				</div>
				<div class="col-sm-4">
					<!-- <div style="background: #fff; border-radius: 10px; box-shadow: none;    border: 1px solid rgba(97, 125, 255, 0.2);padding: 25px 25px 15px 8%;">
						<label class="badge">Legend:</label>
						<label class="badge badge-success">Application pending.</label>
						<label class="badge badge-warning">Application pending for revision.</label>
						<label class="badge badge-danger">Application denied.</label>
						<label class="badge badge-info">Application's transaction history.</label>
					</div> -->
					@isset($legends)
					<div class="legend">
					    <h4>Legend</h4>
					    <ul>
					    	@foreach($legends as $legend)
					        <li><span style="background-color: {{$legend->color}}"></span>{{$legend->trns_desc}}</li>
					        @endforeach
					    </ul>
					 </div>
					@endisset
				</div>
				<div class="col-md-4">
					
				</div>
				<div class="col-md-8"></div>
			</div>
			@include('dashboard.client.modal.type-of-application')
	</div>
	<div  style="background: #fff;padding: 25px;">
	<!-- <div  style="background: #fff;padding: 25px;"> -->
		<div style="overflow-x: scroll; min-height: 50%" >
			<table class="table table-bordered" id="tAppCl" style="border-bottom: none;border-collapse: collapse;">
				<thead class="thead-dark">
					<tr>
						<th style="white-space: nowrap;" class="text-center">Application <br/> Code</th>
						<th style="white-space: nowrap;" class="text-center">Facility Name</th>
						<th style="white-space: nowrap;" class="text-center">Type of <br/> Application</th>
						<th style="white-space: nowrap;" class="text-center">Owner</th>
						<th style="white-space: nowrap;" class="text-center">App Type</th>
						<th style="white-space: nowrap;" class="text-center">Date <br/> applied</th>
						<th style="white-space: nowrap;" class="text-center">DOH Status</th>
						<th style="white-space: nowrap;" class="text-center">FDA Radiation Facility </br> Status</th>
						<th style="white-space: nowrap;" class="text-center">FDA Pharmacy </br> Status</th>
						<!-- <th style="white-space: nowrap;" class="text-center">DOH Status</th> -->
					{{--	<th style="white-space: nowrap;" class="text-center">FDA Status</th>--}}
						{{-- <th>Self-Assement Complied (%)</th> --}}
						<th style="white-space: nowrap;" class="text-center">Document <br/> Received On</th>
						<th style="white-space: nowrap;" class="text-center">DOH/FDA <br/>Requirements</th>
						<th style="white-space: nowrap;" class="text-center">Options</th>
					</tr>
				</thead>
				<tbody id="homeTbl">
					@if(count($appDet) > 0) @foreach($appDet AS $each) @if($each[0]->canapply == $each[0]->canapply) <?php $_payment = "bg-info"; if(count($each[1]) > 0) { $_payment = "bg-info"; } $_percentage = ""; if(intval($each[2][0]) < 100) { if(intval($each[2][0]) > 0) { $_percentage = "warning"; } else { $_percentage = "danger"; } } else { $_percentage = "success"; } ?> {{-- 2 --}}
					<tr>
						<?php $_tColor = (($each[0]->canapply == 0) ? "success" : (($each[0]->canapply == 1) ? "warning" : "primary")); ?>
						<td>{{$each[0]->hfser_id}}R{{$each[0]->rgnid}}-{{$each[0]->appid}}</td>
						<td style="width: 10%; height: auto;">{{$each[0]->facilityname}}</td>
						<td>{{$each[0]->hfser_desc}}</td>
						<td>{{$each[0]->owner}}</td>
						<td>{{$each[0]->aptid == 'IN' ? 'Initial New' : 'Renewal'}}</td>
						<td>{{$each[0]->t_date}}</td>
						<td style="background-color : {{$each[0]->dohcolor}}">{{$each[0]->trns_desc}}</td>
						
						
						<td>{!! $each[0]->hfser_id == 'LTO' || $each[0]->hfser_id == 'COA' ? (isset($each[0]->FDAStatMach) ? $each[0]->FDAStatMach : 'Evaluation In Process') : 'Not Applicable'!!}</td>
						<td>{!! $each[0]->hfser_id == 'LTO' || $each[0]->hfser_id == 'COA' ? (isset($each[0]->FDAStatPhar) ? $each[0]->FDAStatPhar : 'Evaluation In Process') : 'Not Applicable'!!}</td>
						
						<!-- <td>{!!($each[0]->noofsatellite > 0 ? (isset($each[0]->FDAStatMach) ? $each[0]->FDAStatMach : 'Evaluation In Process') : '<span class="font-weight-bold">Not Applicable</span>')!!}</td>
						<td>{!!($each[0]->noofsatellite > 0 ? (isset($each[0]->FDAStatPhar) ? $each[0]->FDAStatPhar : 'Evaluation In Process') : '<span class="font-weight-bold">Not Applicable</span>')!!}</td>
						
						 -->
						{{-- <td>

							{!!strtolower($each[0]->hfser_id) == 'lto' ? '<label class="badge badge-'.$_percentage.'">'.$each[2][0].'%</label>' : '<span class="font-weight-bold">Not Applicable</span>'!!}
						</td> --}}
						<td>{{$each[0]->documentSent}}</td>
						<td class="text-center">
							@if(in_array(strtolower($each[0]->hfser_id), ['lto','coa']))
							<div class="btn-group mb-1">
							  <button class="btn btn-block btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							    Requirements
							  </button>
							  <div class="dropdown-menu">
							  @if($each[0]->noofmain > 0)
							 			<div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item " style="border-radius: 3px;" href="{{asset('client1/apply/app/'.$each[0]->hfser_id.'/')}}/{{$each[0]->appid}}/fda">FDA Requirements</a>
									    </div>									    
									    <div class="dropdown-divider"></div>
									
							  @endif
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item  " style="border-radius: 3px;"  href="{{asset('client1/apply/app/'.$each[0]->hfser_id.'/')}}/{{$each[0]->appid}}/hfsrb">DOH Requirements</a>
									    <!-- <a class="dropdown-item  " style="border-radius: 3px;"  href="{{asset('client1/apply/app/'.$each[0]->hfser_id.'/')}}/{{$each[0]->appid}}/hfsrb">HFSRB Requirements</a> -->
									    </div>	
							  </div>
							</div>
							<!-- <div class="container">
								<div class="row">
									@if($each[0]->noofmain > 0)
									<div class="col">
										<a href="{{asset('client1/apply/app/'.$each[0]->hfser_id.'/')}}/{{$each[0]->appid}}/fda">
											<button class="btn btn-sm btn-dark" data-toggle="tooltip" data-placement="top" title="FDA Requirements">
												FDA Requirements
											</button>
										</a>
									</div>
									@endif
									<div class="col mt-2">
										<a href="{{asset('client1/apply/app/'.$each[0]->hfser_id.'/')}}/{{$each[0]->appid}}/hfsrb">
											<button class="btn btn-sm btn-dark" data-toggle="tooltip" data-placement="top" title="HFSRB Requirements">
												HFSRB Requirements
											</button>
										</a>
									</div>
								</div>
							</div> -->
							@else
								@if($each[0]->isRecommended == 1)
								<span class="font-weight-bold">Documents Accepted</span>
								@elseif($each[0]->isRecommended == 2)
									<span class="font-weight-bold">Documents for Resubmission</span>
								@else
									@if($each[0]->submittedReq ==  1)
									<span class="font-weight-bold">Documents Submitted</span>
									@else
									<span class="font-weight-bold">No Submission</span>
									@endif
								
								@endif
							@endif
						</td>
						<td class="text-center" style="height: auto;">
						<!-- <td class="text-center" style="width: 15%; height: auto;"> -->
							{{-- <a href="{{asset('client1/certificates')}}/{{strtoupper($each->hfser_id)}}/{{$each->appid}}"><button class="btn btn-light" data-toggle="tooltip" data-placement="top" title="Print"><i class="fa fa-print"></i></button></a> --}}
							{{-- <a href="{{asset('client1/apply/edit')}}/{{$each->appid}}"><button class="btn btn-light" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fa fa-pencil-square-o"></i></button></a> --}}
							{{-- @if($each[0]->isPayEval == 1) --}}
							<div class="btn-group mb-1 dropup" >
							  <button class="btn btn-block btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							    Operations
							  </button>
							  <div class="dropdown-menu" style=" position: relative; z-index: 1000">
							  	@switch($each[0]->hfser_id)
									@case('PTC')
									
									@if($each[0]->isApprove === 0 && $each[0]->requestReeval === null)
									
											<div style="margin-left: 10px;margin-right: 10px;">
											<a class="dropdown-item ddi bg-warning" style="border-radius: 3px;" onclick="requestReEval('{!! $each[0]->appid !!}')"  href="#">Request for re-evaluation</a>
											</div>
											<div class="dropdown-divider"></div>
									@endif
									
									  	<div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/app')}}/{{$each[0]->hfser_id}}/{{$each[0]->appid}}?grp=c">Permit to Construct Details</a>
									    @if($_percentage == "success")@endif
									    </div>
										@if($each[0]->savingStat == "final")
									    <div class="dropdown-divider"></div>
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/attachment')}}/{{$each[0]->hfser_id}}/{{$each[0]->appid}}">Attachments</a>
									    </div>
										@endif
									
									
										@if($each[0]->isRecommended && AjaxController::checkExitPay($each[0]->appid) == "no")
											<div class="dropdown-divider"></div>
											<div style="margin-left: 10px;margin-right: 10px;">
											<a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;"  href="{{url('client1/payment/'.FunctionsClientController::getToken().'/'.$each[0]->appid)}}">Select Payment Method</a>
											</div>
										@endif


									@break
									@case('CON')
									
										<div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/app')}}/{{$each[0]->hfser_id}}/{{$each[0]->appid}}?grp=c">Certificate of Need Details</a>
									    </div>	
										@if($each[0]->savingStat == "final")
									    <div class="dropdown-divider"></div>
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/attachment')}}/{{$each[0]->hfser_id}}/{{$each[0]->appid}}">Attachments</a>
									    </div>	
										@endif
										
										@if($each[0]->isRecommended && AjaxController::checkExitPay($each[0]->appid) == "no")
										<div class="dropdown-divider"></div>
										<div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{url('client1/payment/'.FunctionsClientController::getToken().'/'.$each[0]->appid)}}">Select Payment Method</a>
									    </div>
										@endif								    
									@break
									@case('LTO')
										<div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi {{$_payment}}" style="border-radius: 3px;" href="{{asset('client1/printPaymentFDA')}}/{{FunctionsClientController::getToken()}}/{{$each[0]->appid}}">Order of Payment (FDA X-Ray)</a>
									    </div>									    
									    <div class="dropdown-divider"></div>
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi {{$_payment}}" style="border-radius: 3px;" href="{{asset('client1/printPaymentFDACDRR')}}/{{FunctionsClientController::getToken()}}/{{$each[0]->appid}}">Order of Payment (FDA Pharmacy)</a>
									    </div>									    
									    <div class="dropdown-divider"></div>
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi {{$_payment}}" style="border-radius: 3px;" href="{{asset('client1/apply/app')}}/{{$each[0]->hfser_id}}/{{$each[0]->appid}}?grp=c">License to Operate Details</a>
									    </div>									    
									    <div class="dropdown-divider"></div>
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/assessmentReady/')}}/{{$each[0]->appid}}/">Self Assessment</a>
									    </div>
											<div class="dropdown-divider"></div>
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a  data-toggle="modal" data-target="#chgfilupload-{{$each[0]->appid}}" class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;"  href="#">Upload Proof of payment here</a>
										</div>					
									@break
									@case('COA')
										<div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/app')}}/{{$each[0]->hfser_id}}/{{$each[0]->appid}}?grp=c">Continue Application</a>
									    </div>	
										@if($each[0]->savingStat == "final")
									    <div class="dropdown-divider"></div>
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/attachment')}}/{{$each[0]->hfser_id}}/{{$each[0]->appid}}">Attachments</a>
									    </div>	
										@endif
									    <div class="dropdown-divider"></div>
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/assessmentReady/')}}/{{$each[0]->appid}}/">Self Assessment</a>
									    </div>	
										<div class="dropdown-divider"></div>
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a  data-toggle="modal" data-target="#chgfilupload-{{$each[0]->appid}}" class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;"  href="#">Upload Proof of payment here</a>
										</div>							
							    	@break
									@case('COR')
										<div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/app')}}/{{$each[0]->hfser_id}}/{{$each[0]->appid}}?grp=c">Continue Application</a>
									    </div>	
										@if($each[0]->savingStat == "final")								    
									    <div class="dropdown-divider"></div>
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/attachment')}}/{{$each[0]->hfser_id}}/{{$each[0]->appid}}">Attachments</a>
									    </div>
										@endif
										@if($each[0]->isRecommended)
										<div class="dropdown-divider"></div>
										<div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{url('client1/payment/'.FunctionsClientController::getToken().'/'.$each[0]->appid)}}">Select Payment Method</a>
									    </div>
										@endif

							    	@break
									@case('ATO')
										<div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/app')}}/{{$each[0]->hfser_id}}/{{$each[0]->appid}}?grp=c">Continue Application</a>
									    </div>		
										@if($each[0]->savingStat == "final")							    
									    <div class="dropdown-divider"></div>
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/attachment')}}/{{$each[0]->hfser_id}}/{{$each[0]->appid}}">Attachments</a>
									    </div>
										@endif
										@if($each[0]->isRecommended)
										<div class="dropdown-divider"></div>		
										<div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{url('client1/payment/'.FunctionsClientController::getToken().'/'.$each[0]->appid)}}">Select Payment Method</a>
									    </div>	
										@endif	

							    	@break
									@default
										<div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/app')}}/{{$each[0]->hfser_id}}/{{$each[0]->appid}}?grp=c">Continue Application</a>
									    </div>	
										@if($each[0]->savingStat == "final")
									    <div class="dropdown-divider"></div>
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{asset('client1/apply/attachment')}}/{{$each[0]->hfser_id}}/{{$each[0]->appid}}">Attachments</a>
									    </div>	
										@endif
										@if($each[0]->isRecommended)
										<div class="dropdown-divider"></div>
										<div style="margin-left: 10px;margin-right: 10px;">
									    <a class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" href="{{url('client1/payment/'.FunctionsClientController::getToken().'/'.$each[0]->appid)}}">Select Payment Method</a>
									    </div>	
										@endif					
							    	@break
								@endswitch
								    @if($each[0]->isRecommended || $each[0]->hfser_id == 'LTO'|| $each[0]->hfser_id == 'COA')
										<div class="dropdown-divider"></div>
									    <div style="margin-left: 10px;margin-right: 10px;">
									    <a  href="{{asset('client1/printPayment')}}/{{FunctionsClientController::getToken()}}/{{$each[0]->appid}}" class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" onclick="remAppHiddenId('chgfil{{$each[0]->appid}}')" href="#">View Order of Payment on DOH</a>
									    <!-- <a  data-toggle="modal" data-target="#chgfil-{{$each[0]->appid}}" class="dropdown-item ddi bg-{{$_tColor}}" style="border-radius: 3px;" onclick="remAppHiddenId('chgfil{{$each[0]->appid}}')" href="#">View Order of Payment on DOH</a> -->
									  </div>
									@endif 
							</div>
							<!-- <button style="color: #fff;" class="btn btn-sm {{$_payment}} mb-1" data-toggle="tooltip" data-placement="top" title="View Order of Payment" onclick="remAppHiddenId('chgfil{{$each[0]->appid}}')">{{-- <i class="fa fa-money" aria-hidden="true"></i> --}}<small>View Order of Payment on DOH</small>
							</button> -->
							
							<div class="modal fade" id="chgfil-{{$each[0]->appid}}" role="dialog"  tabindex="-1">
										<div class="modal-dialog modal-lg ">
										
										<!-- Modal content-->
										<div class="modal-content">
											<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal">&times;</button>
											
											</div>
											<div class="modal-body">
											<center>
												<table>
												<tr id="chgfil{{$each[0]->appid}}" hidden>
													<td colspan="11">
													@if(count($each[1]) > 0) <?php $isDone = false; ?>
														<table class="table">
															<thead class="thead-dark">
																<tr>
																	<th>Date</th>
																	<th>Reference</th>
																	<th>Amount</th>
																	<th>Options</th>
																</tr>
															</thead>
															<tbody>
																@foreach($each[1] AS $anEach)
																@if(strtolower($anEach->reference) != 'payment')
																<tr>
																	<td>{{date("F j, Y", strtotime($anEach->t_date))}}</td>
																	<td>{{$anEach->reference}}</td>
																	<td>&#8369;&nbsp;{{number_format($anEach->amount, 2)}}</td>
																	@if(! $isDone)
																		<td class="text-center" rowspan="{{count($each[1])}}" style="vertical-align: middle;">
																			<a href="{{asset('client1/payment')}}/{{FunctionsClientController::getToken()}}/{{$each[0]->appid}}"><button class="btn btn-light" data-toggle="tooltip" data-placement="top" title="Select Payment Method"><i class="fas fa-money-check-alt"></i></button></a>
																			<a href="{{asset('client1/printPayment')}}/{{FunctionsClientController::getToken()}}/{{$each[0]->appid}}"><button class="btn btn-light" data-toggle="tooltip" data-placement="top" title="Print"><i class="fa fa-print"></i></button></a>
																		</td>
																		<?php $isDone = true; ?>
																	@endif
																</tr>
																@endif

																@endforeach
															</tbody>
														</table>
													@else
														<center class="text-primary">Order of Payment has not been finalized by the Process Owner. We will notify you as soon as we finish the verification. Thank you for your patience.</center>
													@endif
													</td> <td hidden></td><td hidden></td><td hidden></td><td hidden></td><td hidden></td><td hidden></td><td hidden></td><td hidden></td><td hidden></td><td hidden></td> </tr> 
											
												</table>
											</center>




											</div>
											<div class="modal-footer">
											<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
											</div>
										</div>
										
										</div>
									</div>

									<div class="modal fade" id="chgfilupload-{{$each[0]->appid}}" role="dialog"  tabindex="-1">
										<div class="modal-dialog modal-lg ">
										
										<!-- Modal content-->
										<div class="modal-content">
											<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal">&times;</button>
											
											</div>
											<div class="modal-body">
											
											<!-- if($each[0]->proofpaystat == 'posted'  )
												
												PROOF OF PAYMENT ALREADY EVALUATED
											
										
											else	 -->
											@if( $each[0]->hfser_desc == "License to Operate")
											<form id="uppp-{{$each[0]->appid}}" method="post" enctype="multipart/form-data">
													Upload proof of payment 
													<br/>
													<!-- <label style="float: left;" for="file-{{$each[0]->appid}}">Cashiering</label>
													<input id="file-{{$each[0]->appid}}" class="form-control" type="file"  required name="upproof"> -->
												
													<label style="float: left;" for="filemach-{{$each[0]->appid}}">Machine</label>
													<input id="filemach-{{$each[0]->appid}}" class="form-control"  type="file" name="upmach">
													
													<label style="float: left;" for="filemach-{{$each[0]->appid}}">Pharmacy</label>
													<input id="filephar-{{$each[0]->appid}}" class="form-control"  type="file" name="upphar">
													
													
													
													<input id="appi-{{$each[0]->appid}}" class="form-control" type="hidden" name="appid">
													
													<br/>
													<button  style="width: 30%; float: right;"	class="btn btn-info btn-block " type="submit"  >Submit</button>
													<!-- <button type="submit" onclick="subProofPay('{{$each[0]->appid}}')"  >Submit</button> -->
											</form>
											@else
												PROOF OF PAYMENT NOT APPLICABLE
											@endif
											<!-- endif -->
											</div>
											<div class="modal-footer">
											<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
											</div>
										</div>
										
										</div>
									</div>
									
<script>
	function requestReEval(appid){
		console.log(appid)
		if(confirm('Are you sure you want to request for re-evaluation?')){
			$.ajax({
							url: '{{asset('/api/request/reeval')}}',
							type: 'POST',
							data:{appid: appid},

							success: function(a){
								
								if(a == 'succ'){
									alert("Request for Re-evaluation sent")
									location.reload();
								}else{
									alert("Request for Re-evaluation failed")
								}

							}
				})
		}
	}




	$(document).on('submit','#uppp-{{$each[0]->appid}}',function(event){
		event.preventDefault();
if(confirm('Are you sure you want to upload proof of payment?')){
						
						let data = new FormData(this);
						// data.append('upproof', document.getElementById("file-{{$each[0]->appid}}").value);
						data.append('appid', '{{$each[0]->appid}}');
						console.log("data")
						console.log(data.values())
						console.log('{{$each[0]->appid}}')
						$.ajax({
							url: '{{asset('/api/upload/proofpayment')}}',
							type: 'POST',
							contentType: false,
							processData: false,
							data:data,

							success: function(a){
								console.log("a")
								console.log(a.msg)
								console.log(a.id)

								if(a.msg == "success"){
									alert("Payment upload successful")
								}else{
									alert("Payment upload failed")
								}
								
								// if(a == 'DONE'){
								// 	alert('Successfully Edited Personnel');
								// 	location.reload();
								// } else {
								// 	console.log(a);
								// }
							},
							fail: function(a,b,c){
								console.log([a,b,c]);
							}
						})
}
					})
	</script>
									
							
						</td>
					</tr>
					
					
					@endif @endforeach @else
					<tr>
						<td colspan="6">No application applied yet.</td>
					</tr>
					@endif
					
				</tbody>
			</table>
			</div>

	</div>


	<script src="{{asset('ra-idlis/public/js/forall.js')}}"></script>
	<script type="text/javascript">
		"use strict";
		var ___div = document.getElementById('__applyBread');
		if(___div != null || ___div != undefined) {
			___div.classList.remove('active');
			___div.classList.add('text-primary');
		}
		(function() {
		})();
		$(function () {
		  	$('[data-toggle="tooltip"]').tooltip()
		});
		$(document).ready( function () {
		    $('#tApp').DataTable({
		    	"ordering": false,
		    	"lengthMenu": [10, 20, 50, 100]
		    });
		});
		function remAppHiddenId(elId) {
			let idom = document.getElementById(elId);
			if(idom != undefined || idom != null) {
				if(idom.hasAttribute('hidden')) {
					idom.removeAttribute('hidden');
				} else {
					idom.setAttribute('hidden', true);
				}
			}
		}
	</script>

<script type="text/javascript">
		"use strict";
		var ___div = document.getElementById('__applyBread');
		if(___div != null || ___div != undefined) {
			___div.classList.remove('active');
			___div.classList.add('text-primary');
		}
		(function() {
		})();
		$(function () {
		  	$('[data-toggle="tooltip"]').tooltip()
		});
		$(document).ready( function () {
		    $('#tAppCl').DataTable({
		    	"ordering": false,
		    	"lengthMenu": [10, 20, 50, 100]
		    });
		});
		function remAppHiddenId(elId) {
			let idom = document.getElementById(elId);
			if(idom != undefined || idom != null) {
				if(idom.hasAttribute('hidden')) {
					idom.removeAttribute('hidden');
				} else {
					idom.setAttribute('hidden', true);
				}
			}
		}
	</script>
	@include('client1.cmp.footer')
</body>
@endsection

<script>

		function subProofPay(appid){

			
			document.getElementById("uppp-"+appid).addEventListener("submit", function(event){
			event.preventDefault()
			});

			
			var form =	document.forms["uppp-"+appid].getElementsByTagName("input");
			
			if(form[0].value != ""){
				if(confirm("Are you sure you want to send your proof of payment?")){
				
					$(document).on('submit','#uppp'+appid,function(event){
						event.preventDefault();
						let data = new FormData(this);
						console.log("data")
						console.log(data)
						$.ajax({
							url: '{{asset('client1/sendproofpay')}}',
							type: 'POST',
							contentType: false,
							processData: false,
							data:data,
							success: function(a){
								console.log("a")
								// console.log(a)
								// if(a == 'DONE'){
								// 	alert('Successfully Edited Personnel');
								// 	location.reload();
								// } else {
								// 	console.log(a);
								// }
							},
							fail: function(a,b,c){
								console.log([a,b,c]);
							}
						})
					})


				// $.ajax({
				// 		url: '{{asset('client1/sendproofpay')}}',
				// 		// dataType: "json", 
	    		// 		async: false,
				// 		type: 'POST',
				// 	data:subs,
				// 	cache: false,
			    //     contentType: false,
			    //     processData: false,
				// 		success: function(a){
				// 			console.log(a.msg)
                            
				// 		}
				// 	});


				}
			}
			
		}								
										

									</script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/datetime/1.0.3/css/dataTables.dateTime.min.css" />