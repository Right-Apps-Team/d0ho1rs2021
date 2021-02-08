@if (session()->exists('employee_login'))   
  @extends('mainEmployee')
  @section('title', 'Assessment Process Flow')
  @section('content')
  <input type="text" id="CurrentPage" hidden="" value="PF009">
  <div class="content p-4">
      <div class="card">
          <div class="card-header bg-white font-weight-bold">
             Assess Applicants
          </div>
          <div class="card-body table-responsive">
              <table class="table table-hover" id="example" style="font-size:13px;">
                  <thead>
                  <tr>
                      <th scope="col" class="text-center">Type</th>
                      <th scope="col" class="text-center">Application Code</th>
                      <th scope="col" class="text-center">Name of Health Facility</th>
                      <th scope="col" class="text-center">Type of Health Facility</th>
                      <th scope="col" class="text-center">Date</th>
                      <th scope="col" class="text-center">{{-- &nbsp; --}}Application Status</th>
                      <th scope="col" class="text-center">Current Status</th>
                      <th scope="col" class="text-center">Options</th>
                  </tr>
                  </thead>
                  <tbody id="FilterdBody">
                      @if (isset($BigData))
                        @foreach ($BigData as $data)
                          @if($data->isPayEval == 1 && $data->isrecommended == 1 && $data->isCashierApprove == 1 && $data->isInspected == null && in_array($data->hfser_id, ['LTO','COA']) && $data->proposedWeek != null && AjaxController::canProcessNextStepFDA($data->appid,'isCashierApproveFDA','isCashierApprovePharma'))
                          @php
                            $status = '';
                            $paid = $data->appid_payment;
                            $reco = $data->isrecommended;
                            $ifdisabled = '';$color = '';
                            if($currentuser['cur_user'] != 'ADMIN' && !FunctionsClientController::existOnDB('app_team',[['appid',$data->appid],['uid',$currentuser['cur_user']]])){
                              continue;
                            }
                            // if ($data->isInspected == null ) {
                            //       $OptBtn = "<button type=\"button\" title=\"Assess ".$data->facilityname."\" class=\"btn-defaults\" onclick=\"window.location.href=\"".asset('employee/dashboard/lps/assess')."/".$data->appid."/inspect\"  ".$ifdisabled."><i class=\"fa fa-fw fa-clipboard-check\"></i></button>";
                            //   } else {
                            //       $OptBtn = "<button type\"button\" title=\"View ".$data->facilityname."\" class=\"btn-defaults\" onclick=\"window.location.href=\'".asset('employee/dashboard/lps/assess')."/".$data->appid."/view\"  ".$ifdisabled."><i class=\"fa fa-fw fa-clipboard-check\"></i></button>";
                            //   }
                          @endphp
                          <tr>
                            <td class="text-center">{{$data->hfser_id}}</td>
                            <td class="text-center">{{$data->hfser_id}}R{{$data->rgnid}}-{{$data->appid}}</td>
                            <td class="text-center"><strong>{{$data->facilityname}}</strong></td>
                            <td class="text-center">{{(ajaxController::getFacilitytypeFromHighestApplicationFromX08FT($data->appid)->hgpdesc ?? 'NOT FOUND')}}</td>
                            <td class="text-center">{{$data->formattedDate}}</td>
                            <td class="text-center">{{$data->aptdesc}}</td>
                            <td style="color:{{$color}};font-weight:bold;" class="text-center">{{$data->trns_desc}}</td>
                              <td>
                              	<center>
                                  <button type="button" title="Assess {{$data->facilityname}}" class="btn btn-outline-primary" onclick="window.location.href='{{asset('employee/dashboard/processflow/parts')}}/{{$data->appid}}'"><i class="fa fa-fw fa-clipboard-check"></i></button>
                            	</center>
                              </td>
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