@if (session()->exists('employee_login'))  
  @extends('mainEmployee')
  @section('title', 'Pre-Assessment')
  @section('content')
  {{-- <input type="text" id="CurrentPage" hidden="" value="FD002"> --}}
  <div class="content p-4">
    <div class="card">
      <div class="card-header bg-white font-weight-bold">
             Pre-Assessment
          </div>
          <div class="card-body table-responsive">
            <table class="table table-hover" style="font-size:13px;" id="example">
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
                          @php
                          $preassess = (strtolower($request) == 'machines' ? $data->ispreassessed : $data->ispreassessedpharma);
                          $oop = (strtolower($request) == 'machines' ? $data->isPayEvalFDA : $data->isPayEvalFDAPharma);
                          // $eval = (strtolower($request) == 'machines' ? $data->isrecommendedFDA : $data->isrecommendedFDAPharma);
                          // $cashier = (strtolower($request) == 'machines' ? $data->isCashierApproveFDA : $data->isCashierApprovePharma);
                          @endphp
                        @if($preassess == null || $preassess == 2)
                          @if(in_array(strtolower($data->hfser_id), ['lto','coa']) && $data->isReadyForInspecFDA == 1 && $oop == 1/* && $cashier == 1*/)
                            @php
                              $toCheck = ($request == 'machines' ? 'cdrrhr' : 'cdrr');
                            @endphp
                            @if(!FunctionsClientController::hasRequirementsFor($toCheck,$data->appid))
                            @php continue; @endphp
                            @endif
                            @php
                              $status = '';
                              $paid = $data->appid_payment;
                              $reco = $data->isrecommended;
                              $ifdisabled = '';$color = '';
                              // if($data->status == 'P'){
                              //   $ifdisabled = 'disabled';
                              // }
                            @endphp
                            <tr>
                              <td class="text-center">{{$data->hfser_id}}</td>
                              <td class="text-center">{{$data->hfser_id}}R{{$data->rgnid}}-{{$data->appid}}</td>
                              <td class="text-center"><strong>{{$data->facilityname}}</strong></td>
                              <td class="text-center">{{(ajaxController::getFacilitytypeFromHighestApplicationFromX08FT($data->appid)->hgpdesc ?? 'NOT FOUND')}}</td>
                              <td class="text-center">{{$data->formattedDate}}</td>
                              <td class="text-center">{{$data->aptdesc}}</td>
                              <td class="text-center" style="font-weight:bold;">{{(AjaxController::getTransStatusById($data->FDAstatus)[0]->trns_desc ?? '')}}</td>
                                <td>
                                  <center>
                                    {{-- @if(!isset($data->documentSent)) --}}
                                      {{-- <button type="button" title="Pre-assess {{$data->facilityname}}" class="btn btn-outline-primary"  {{$ifdisabled}}><i class="fa fa-fw fa-clipboard-check" {{$ifdisabled}}></i></button>&nbsp; --}}
                                      {{-- <button type="button" title="Edit {{$data->facilityname}}" class="btn btn-outline-warning" onclick="window.location.href = '{{ asset('/employee/dashboard/processflow/Pre-assess') }}/{{$data->appid}}/edit'"  {{$ifdisabled}}><i class="fa fa-fw fa-edit" {{$ifdisabled}}></i></button> --}}
                                    {{-- @else --}}
                                      <button type="button" title="Pre-assess {{$data->facilityname}}" class="btn btn-outline-primary" onclick="window.location.href = '{{ url('employee/dashboard/processflow/evaluate') }}/{{$data->appid}}/{{($request == 'machines' ? 'xray' : $request)}}'"  {{$ifdisabled}}><i class="fa fa-fw fa-clipboard-check" {{$ifdisabled}}></i></button>&nbsp;
                                    {{-- <button type="button" title="Edit {{$data->facilityname}}" class="btn btn-outline-warning" onclick="window.location.href = '{{ asset('/employee/dashboard/processflow/evaluate') }}/{{$data->appid}}/edit'"  {{$ifdisabled}}><i class="fa fa-fw fa-edit" {{$ifdisabled}}></i></button> --}}
                                    {{-- @endif --}}
                                </center>
                              </td>
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
    $(document).ready(function() {$('#example').DataTable();});
  </script>
  @endsection
@else
  <script type="text/javascript">window.location.href= "{{ asset('employee') }}";</script>
@endif
