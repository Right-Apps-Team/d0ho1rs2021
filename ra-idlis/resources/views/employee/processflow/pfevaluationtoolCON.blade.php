@if (session()->exists('employee_login'))   
  @extends('mainEmployee')
  @section('title', 'Evaluation Process Flow')
  @section('content')
  @php 
    $employeeData = session('employee_login');
   $grpid = isset($employeeData->grpid) ? $employeeData->grpid : 'NONE';
    @endphp
  <input type="text" id="CurrentPage" hidden="" value="PF015">
  <div class="content p-4">
      <div class="card">
          <div class="card-header bg-white font-weight-bold">
             Evaluate Applicants 
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
                        @php 
                          if($grpid != 'NA' && AjaxController::checkConmem($data->appid) == 'no'){
                            continue;
                          }
                          @endphp



                          @if($data->isPayEval == 1 && $data->isrecommended == 1 && $data->isCashierApprove == 1 && $data->isInspected == null && strtolower($data->hfser_id) == 'con')
                          @php
                            $status = ''; $link = '';
                            $paid = $data->appid_payment;
                            $reco = $data->isrecommended;
                            $ifdisabled = '';$color = '';
                          @endphp
                          @switch($data->hfser_id)
                            @case('PTC')
                              @php
                                $link = asset('employee/dashboard/processflow/evalution/'.$user['cur_user'].'/'.$data->appid.'/'.$data->hfser_id);
                              @endphp
                            @break
                            @case('CON')
                              @php
                                $link = asset('employee/dashboard/processflow/conevalution/'.$data->appid);

                               


                              @endphp
                            @break
                          @endswitch

                          @if($data->hfser_id == 'CON')

                          @php
                          if($data->concommittee_eval == 1){
                                  continue;
                                }
                          @endphp

                          @endif
                          <tr>
                            <td class="text-center">{{$data->hfser_id}}
                             
                            </td>
                            <td class="text-center">{{$data->hfser_id}}R{{$data->rgnid}}-{{$data->appid}}</td>
                            <td class="text-center"><strong>{{$data->facilityname}}</strong></td>
                            <td class="text-center">{{(ajaxController::getFacilitytypeFromHighestApplicationFromX08FT($data->appid)->hgpdesc ?? 'NOT FOUND')}}</td>
                            <td class="text-center">{{$data->formattedDate}}</td>
                            <td class="text-center">{{$data->aptdesc}}</td>
                            <td style="color:{{$color}};font-weight:bold;" class="text-center">{{$data->trns_desc}}</td>
                              <td>
                              	<center>
                                  <button type="button" title="Assess {{$data->facilityname}}" class="btn btn-outline-primary" onclick="window.location.href='{{$link}}'"><i class="fa fa-fw fa-clipboard-check"></i></button>
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