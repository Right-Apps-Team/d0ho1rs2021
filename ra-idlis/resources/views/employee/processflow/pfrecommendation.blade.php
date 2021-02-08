@if (session()->exists('employee_login'))   
  @extends('mainEmployee')
  @section('title', 'Recommendation Process Flow')
  @section('content')
  <input type="text" id="CurrentPage" hidden="" value="PF006">
  <div class="content p-4">
      <div class="card">
          <div class="card-header bg-white font-weight-bold">
             Recommendation for Final Approval
          </div>
          <div class="card-body table-responsive">
              <table class="table table-hover" id="example" style="font-size:13px;">
                  <thead>
                  <tr>
                      <th scope="col" class="text-center">Type</th>
                      <th scope="col" class="text-center">Application Code</th>
                      <th scope="col" class="text-center">Name of Facility</th>
                      <th scope="col" class="text-center">Type of Facility</th>
                      <th scope="col" class="text-center">Date</th>
                      <th scope="col" class="text-center">&nbsp;</th>
                      <th scope="col" class="text-center">Current Status</th>
                      <th scope="col" class="text-center">Options</th>
                  </tr>
                  </thead>
                  <tbody id="FilterdBody">
                      @if (isset($BigData))
                        @foreach ($BigData as $data)
                          @if($data->isPayEval == 1 && $data->isrecommended == 1 && $data->isCashierApprove == 1 /*&& $data->isRecoForApproval == null*/)
                          @php
                            $status = '';
                            $paid = $data->appid_payment;
                            $reco = $data->isrecommended;
                            $ifdisabled = '';$color = '';

                            if(strtolower($data->hfser_id) == 'lto' && (AjaxController::canViewFDAOOP($data->appid)[0] || AjaxController::canViewFDAOOP($data->appid)[1]) ){
                              if( ($data->isCashierApproveFDA != 1 && AjaxController::canViewFDAOOP($data->appid)[0]) || ($data->isCashierApprovePharma != 1 && AjaxController::canViewFDAOOP($data->appid)[1]) ){
                                continue;
                              }
                            }
                            if(strtolower($data->hfser_id) == 'ptc' && !FunctionsClientController::existOnDB('hferc_evaluation',[['appid',$data->appid]]) ){
                              continue;
                            }

                            if($data->isApprove !== null){
                              continue;
                            }
                            
                            if($data->status == 'P' || $data->status == 'RA' || $data->status == 'RE' || $data->status == 'RI' ){
                              $ifdisabled = 'disabled';
                            }

                          @endphp
                          <tr>
                            <td class="text-center">{{$data->hfser_id}}</td>
                            <td class="text-center">{{$data->hfser_id}}R{{$data->rgnid}}-{{$data->appid}}</td>
                            <td class="text-center"><strong>{{$data->facilityname}}</strong></td>
                            <td class="text-center">{{(ajaxController::getFacilitytypeFromHighestApplicationFromX08FT($data->appid)->hgpdesc ?? 'NOT FOUND')}}</td>
                            <td class="text-center">{{$data->formattedDate}}</td>
                            <td class="text-center">{{$data->aptdesc}}</td>
                            <td class="text-center" style="font-weight:bold;">{{$data->trns_desc}}</td>
                              <td><center>
                                  <button type="button" title="Evaluate Payment for {{$data->facilityname}}" class="btn btn-outline-primary" onclick="window.location.href='{{asset('employee/dashboard/processflow/recommendation')}}/{{$data->appid}}'"  {{$ifdisabled}}><i class="fa fa-fw fa-clipboard-check" {{$ifdisabled}}></i></button>
                              </center></td>
                          </tr>
                          @endif
                        @endforeach
                      @endif
                  </tbody>
              </table>
          </div>
      </div>
  </div>
  <script type="text/javascript">
  	$(document).ready(function(){
  		$('#example').DataTable();
  	});
  </script>
  @endsection
@else
  <script type="text/javascript">window.location.href= "{{ asset('employee') }}";</script>
@endif

