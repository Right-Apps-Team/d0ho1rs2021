@if (session()->exists('employee_login'))   
  @extends('mainEmployee')
  @section('title', 'Approval/Issuance Process Flow')
  @section('content')
  <input type="text" id="CurrentPage" hidden="" value="FD008">
  <div class="content p-4">
      <div class="card">
          <div class="card-header bg-white font-weight-bold">
             Approval/Issuance Certificate (FDA)
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
                      <th scope="col" class="text-center">Application Status</th>
                      <th scope="col" class="text-center">Current Status</th>
                      <th scope="col" class="text-center">Options</th>
                  </tr>
                  </thead>
                  <tbody id="FilterdBody">
                      @if (isset($BigData))
                        @foreach ($BigData as $data)
                         @php
                          $oop = (strtolower($request) == 'machines' ? $data->isPayEvalFDA : $data->isPayEvalFDAPharma);
                          $eval = (strtolower($request) == 'machines' ? $data->isrecommendedFDA : $data->isrecommendedFDAPharma);
                          $cashier = (strtolower($request) == 'machines' ? $data->isCashierApproveFDA : $data->isCashierApprovePharma);
                          $reco = (strtolower($request) == 'machines' ? $data->isRecoFDA : 1);
                          @endphp
                          @if($oop == 1 && $eval == 1 && $cashier == 1 && $reco == 1 && ($request == 'machines' ? $data->isApproveFDA : $data->isApproveFDAPharma) == null)
                          @php
                              $toCheck = ($request == 'machines' ? 'cdrrhr' : 'cdrr');
                            @endphp
                            @if(!FunctionsClientController::hasRequirementsFor($toCheck,$data->appid) ||  ($request != 'machines' &&  FunctionsClientController::existOnDB('cdrrpersonnel',[['appid',$data->appid],['isTag',1]]))   )
                            @php continue; @endphp
                            @endif
                          @php
                            $status = '';
                            $paid = $data->appid_payment;
                            $reco = $eval;
                            $ifdisabled = '';$color = '';
                            
                            // if($data->status == 'P' || $data->status == 'RA' || $data->status == 'RE' || $data->status == 'RI' ){
                            //   $ifdisabled = 'disabled';
                            // }

                          @endphp
                          @if(in_array(strtolower($data->hfser_id), ['lto','coa']))
                          <tr>
                            <td class="text-center">{{$data->hfser_id}}</td>
                            <td class="text-center">{{$data->hfser_id}}R{{$data->rgnid}}-{{$data->appid}}</td>
                            <td class="text-center"><strong>{{$data->facilityname}}</strong></td>
                            <td class="text-center">{{(ajaxController::getFacilitytypeFromHighestApplicationFromX08FT($data->appid)->hgpdesc ?? 'NOT FOUND')}}</td>
                            <td class="text-center">{{$data->formattedDate}}</td>
                            <td class="text-center">{{$data->aptdesc}}</td>
                            <td class="text-center" style="font-weight:bold;">{{(AjaxController::getTransStatusById($data->FDAstatus)[0]->trns_desc ?? '')}}</td>
                              <td><center>
                                  <button type="button" title="Evaluate Payment for {{$data->facilityname}}" class="btn btn-outline-primary" onclick="window.location.href='{{asset('employee/dashboard/processflow/FDA/approval')}}/{{$data->appid}}/{{$request}}'"  {{$ifdisabled}}><i class="fa fa-fw fa-clipboard-check" {{$ifdisabled}}></i></button>
                              </center></td>
                          </tr>
                          @endif
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
