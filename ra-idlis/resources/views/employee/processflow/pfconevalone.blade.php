@if (session()->exists('employee_login'))   
  @extends('mainEmployee')
  @section('title', 'Committee Evaluation Tool')
  @section('content')
  <style>
    .table, td, th, tr{
      border: 2px solid black!important;
    }
  </style>
  <input type="text" id="CurrentPage" hidden="" value="PF015">
  <div class="content p-4">
    <form id="evalSave" method="POST" action="{{asset('employee/dashboard/processflow/conevalution/').'/'.$AppData->appid}}">
    <!-- <form id="evalSave"> -->
      {{csrf_field()}}
    <div class="card">
      <div class="card-header bg-white font-weight-bold">
         Committee Evaluation Tool 
         <button class="btn btn-primary" onclick="window.history.back();">Back</button>
      </div>
      <div class="card-body">
        <div class="col-sm-12">
          <h2>@isset($AppData) {{$AppData->facilityname}} @endisset</h2>
          <h5>@isset($AppData) {{strtoupper($AppData->streetname)}}, {{strtoupper($AppData->street_number)}},  {{strtoupper($AppData->brgyname)}}, {{$AppData->cmname}}, {{$AppData->provname}} @endisset</h5>    
        </div>
      </div>
      {{-- 1 --}}
      <div class="container-fluid border mb-3 pt-3">
        <p class="font-weight-bold lead pt-2">1. BED TO POPULATION RATIO</p>
        <table class="table table-bordered mt-3">
          <thead>
            <tr>
              <th colspan="4">Determination of Projected Primary and Secondary Catchment Population (P)</th>
            </tr>
            <tr>
              <td>Action</td>
              <td>Type</td>
              <td>Barangay/Municipality/District/Province/Region</td>
              <td>Projected Population (5<sup>th</sup> year) of Catchment Area</td>
              <td>Projected Population recommendation</td>
            </tr>
          </thead>
          <tbody id="mainCatch">
            @php $total = 0;$totalinpt = 0; @endphp
            
            <tr>
              <td colspan="3" class="text-right font-weight-bold">Projected Primary and Secondary Catchment Population(P) = </td>
              <td class="font-weight-bold totalEditInpt">{{number_format($totalinpt)}}</td>
              <td class="font-weight-bold totalEdit">{{number_format($total)}}</td>
            </tr>
          </tbody>
        </table>

        <div class="row">
          <div class="pl-4 col-md-2">
            <button type="button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" onclick="createBPR();"><i class="fa fa-plus"></i></button>
          </div>
          
        </div>

      </div>

      {{-- 2 --}}
      <div class="container-fluid border mb-3 mt-5 font-weight-bold pt-3">
        Determination of Inventory Hospital Beds (IHB) in Primary and Secondary Catchment Areas:
        <table class="table table-bordered mt-3">
          <thead id="mainEl">
            <tr>
              <td colspan="5" class="font-weight-bold">A. Existing Hospital Beds:</td>
            </tr>
            <tr>
              <td></td>
              <td>Existing Hospitals</td>
              <td>Location</td>
              <td>ABC</td>
              <td>Level of Hospital</td>
            </tr>
            <tr>
             
            </tr>
          @isset($edit)
            @php $count = 1; @endphp
              @foreach($hospitals as $hosp)
                <tr class="trd100{{$count}}">
                  <td class="toRemoveaddTT toRemove">
                    <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Remove" onclick="deleteRow('trd100{{$count}}');"><i class="fa fa-times"></i></button>
                  </td>
                  <td>
                    <input class="form-control toChange" type="text" name="existHospabc[]" required="" value="{{$hosp->facilityname}}">
                  </td>
                  <td class="toRemove">
                    @if(count($brp[1]) > 0)
                      <select class="form-control" name="locabc[]" required>
                        <option value hidden disabled selected></option>
                        @if(count($brp[1]) > 0)
                          @for($i = 0; $i < count($brp[1]); $i++)
                          <option value="{{$brp[1][$i]}}">{{$brp[1][$i]}}</option>
                          @endfor
                        @endif
                      </select>
                      <script type="text/javascript">
                        $('select[name="locabc[]"]:eq('+ ($("select[name='locabc[]']").length - 1) +')').val('{{$hosp->location}}');
                      </script>
                    @endif
                  </td>
                  <td style="width:200px" class="toRemoveaddTT">
                    <input type="number" class="toRemoveaddTT form-control toTotal toChange" name="abc[]" required="" value="{{$hosp->noofbed}}">
                  </td>
                  <td class="toRemove toRemoveaddTT">
                    <select class="form-control" name="typeabc[]" required="">
                      @if(count($brp[2]) > 0) 
                        @foreach($brp[2] AS $each) 
                          <option value="{{$each->facid}}">{{$each->facname}}</option> 
                        @endforeach 
                      @endif
                      <option value="NA">N/A</option>
                    </select>
                    <script type="text/javascript">
                     $('select[name="typeabc[]"]:eq('+ ($("select[name='typeabc[]']").length - 1) +')').val('{{$hosp->cat_hos}}');
                    </script>
                  </td>
                </tr>
                @php $count++; @endphp
              @endforeach
            @endisset
          </thead>
          {{-- @isset($edit)
            @php $existCount = 1; @endphp
              @foreach($hospitals as $hosp)
                <tr class="trd100{{$existCount}}">
                  <td class="toRemoveaddTT toRemove">
                    <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Remove" onclick="deleteRow('trd100{{$existCount}}');"><i class="fa fa-times"></i></button>
                  </td>
                  <td>
                    <input class="form-control toChange" type="text" name="existHospabc[]" required="" value="{{$hosp->facilityname}}">
                  </td>
                  <td class="toRemove">
                    @if(count($brp[1]) > 0)
                      <select class="form-control" name="locabc[]" required>
                        <option value hidden disabled selected></option>
                        @if(count($brp[1]) > 0)
                          @for($i = 0; $i < count($brp[1]); $i++)
                          <option value="{{$brp[1][$i]}}">{{$brp[1][$i]}}</option>
                          @endfor
                        @endif
                      </select>
                      <script type="text/javascript">
                        $('select[name="locabc[]"]:eq('+ ($("select[name='locabc[]']").length - 1) +')').val('{{$hosp->location}}');
                      </script>
                    @endif
                  </td>
                  <td style="width:200px" class="toRemoveaddTT">
                    <input type="number" class="toRemoveaddTT form-control toTotal toChange" name="abc[]" required="" readonly value="{{$hosp->noofbed}}">
                  </td>
                  <td class="toRemove toRemoveaddTT">
                    <select class="form-control" name="typeabc[]" required="">
                      @if(count($brp[2]) > 0) 
                        @foreach($brp[2] AS $each) 
                          <option value="{{$each->facid}}">{{$each->facname}}</option> 
                        @endforeach 
                      @endif
                      <option value="NA">N/A</option>
                    </select>
                    <script type="text/javascript">
                     $('select[name="typeabc[]"]:eq('+ ($("select[name='typeabc[]']").length - 1) +')').val('{{$hosp->cat_hos}}');
                    </script>
                  </td>
                </tr>
                @php $existCount++; @endphp
              @endforeach
            @endisset --}}
          <tbody id="addNewRow1">
            
            <tr>
              <td colspan="5" class="text-center font-weight-bold">
                -----END OF LIST-----
              </td>
            </tr>
            {{-- <tr>
              <td colspan="2" class="font-weight-bold bg-secondary text-white text-right"><span>SubTotal ABC (1)</span></td>
              <td colspan="2" id="abc1val"></td>
            </tr> --}}
          </tbody>
        </table>
        <div class="row">
          <div class="pl-4 col-md-2">
            <button type="button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" onclick="addNewRowA('addNewRow1', 0, 'abc');" data-original-title="" title=""><i class="fa fa-plus"></i></button>
          </div>
          
        </div>
      </div>

      {{-- 3 --}}

      <div class="container-fluid border mb-3 mt-5 font-weight-bold pt-3">
        Determination of Inventory Hospital Beds (DIB) in Primary and Secondary Catchment Areas:
        <table class="table table-bordered mt-3">
          <thead>
            <tr>
              <td colspan="5" class="font-weight-bold">B. Hospitals Currently Applying for License to Operate</td>
            </tr>
            <tr>
              <td></td>
              <td>Hospitals</td>
              <td>Location</td>
              <td>ABC</td>
              <td>Level of Hospital</td>
            </tr>
            <tr>
             
            </tr>
          </thead>
          <tbody id="addNewRow2">
            
            <tr>
              <td colspan="5" class="text-center font-weight-bold">
                -----END OF LIST-----
              </td>
            </tr>
            {{-- <tr>
              <td colspan="2" class="font-weight-bold bg-secondary text-white text-right"><span>SubTotal ABC (2)</span></td>
              <td colspan="2" id="abc2val"></td>
            </tr> --}}
          </tbody>
        </table>
        <div class="row">
          <div class="pl-4 col-md-2">
            <button type="button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" onclick="addNewRowA('addNewRow2', 0, 'cde');" data-original-title="" title=""><i class="fa fa-plus"></i></button>
          </div>
          
        </div>
      </div>
      <div class="offset-2 col-md-6 font-weight-bold mr-5">
        IHB = TOTAL ABC = [ABC (1) + ABC (2) ] = <span class="IHBTotal">0</span>
      </div>
      <input type="hidden" name="ihbval">
      <input type="hidden" name="bprval">
      <input type="hidden" name="pbnval">
      <input type="hidden" name="pscaval">
      
      <div class="container-fluid font-weight-bold pt-3">
        **May attach additional sheets as needed<br>
        **Authorized Bed Capacity
      </div>

      {{-- IHB --}}

      <div class="container mb-3 mt-5 font-weight-bold pt-3">
        Determination of Bed-to-Population Ratio (BPR) = IHB / P x 1,000
        <table class="table table-bordered mt-3">
          <tbody>
            <tr>
              <td class="font-weight-bold text-right ihb">IHB</td>
              <td class="text-center IHBTotal">0</td>
            </tr>
            <tr>
              <td class="font-weight-bold text-right">P</td>
              <td class="text-center totalEdit">{{number_format($total)}}</td>
            </tr>
            <tr>
              <td class="font-weight-bold text-right">BPR</td>
              <td class="text-center" id="bpr">0</td>
            </tr>
          </tbody>
        </table>
      </div>

      {{-- PBN --}}

       <div class="container mb-3 mt-5 font-weight-bold pt-3">
        Determination Projected Bed Need (PBN) = P x 1/1,000
        <table class="table table-bordered mt-3">
          <tbody>
            <tr>
              <td class="font-weight-bold text-right">P</td>
              <td class="text-center totalEdit">{{number_format($total)}}</td>
            </tr>
            <tr>
              <td class="font-weight-bold text-right">PBN</td>
              <td class="text-center" id="pbn">0</td>
            </tr>
          </tbody>
        </table>
      </div>

      {{-- UNMET --}}

      <div class="container mb-3 mt-5 font-weight-bold pt-3">
        Determination of Unmet Bed Need (UBN) = PBN - IHB
        <table class="table table-bordered mt-3">
          <tbody>
            <tr>
              <td class="font-weight-bold text-right ">PBN</td>
              <td class="text-center pbn">0</td>
            </tr>
            <tr>
              <td class="font-weight-bold text-right ">IHB</td>
              <td class="text-center IHBTotal"></td>
            </tr>
            <tr>
              <td class="font-weight-bold text-right">UBN</td>
              <td class="text-center" id="ubn">0</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="container-fluid border mb-3 mt-5 font-weight-bold pt-3">
        Determination of Occupancy Rates of Existing Hospitals in Primary and Secondary Catchment Areas
        <table class="table table-bordered mt-3">
          <thead id="pscaHead">
            <tr>
              <td>Existing Hospitals</td>
              <td>ABC</td>
              <td colspan="3" class="text-center">Occupancy Rate</td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td>2 Years Ago</td>
              <td>A year ago</td>
              <td>Average for the past 2 yrs</td>
            </tr>
            <tr>
             
            </tr>
          </thead>
          @isset($edit)
            @php $count = 1; @endphp
              @foreach($hospitals as $hosp)
                <tr class="trd100{{$count}}">
                {{-- <td class="toRemoveaddTT toRemove">
                  <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Remove" onclick="deleteRow('trd100{{$count}}');"><i class="fa fa-times"></i></button>
                </td> --}}
                  <td>
                    <input class="form-control toChange" type="text" name="existHospabc[]" required="" value="{{$hosp->facilityname}}">
                  </td>
                  <td style="width:200px" class="toRemoveaddTT">
                    <input type="number" class="toRemoveaddTT form-control toTotal toChange" name="abc[]" required="" value="{{$hosp->noofbed}}">
                  </td>
                  <td style="width:200px" class="toRemoveaddTT">
                    <input type="number" class="toRemoveaddTT form-control toTotal toChange" name="abc[]" required="" value="{{$hosp->tya}}">
                  </td>
                  <td style="width:200px" class="toRemoveaddTT">
                    <input type="number" class="toRemoveaddTT form-control toTotal toChange" name="abc[]" required="" value="{{$hosp->aya}}">
                  </td>
                  <td style="width:200px" class="toRemoveaddTT">
                    <input type="number" class="toRemoveaddTT form-control toTotal toChange" name="abc[]" required="" value="{{$hosp->apty}}">
                  </td>
                </tr>
                @php $count++; @endphp
              @endforeach
            @endisset
          <tbody id="addNewRow3">
            {{-- @isset($edit)
            @php $exCount = 1; @endphp
              @foreach($hospitals as $hosp)
              <tr class="trd100{{$exCount}}">
                <td>
                  <input class="form-control toChange" type="text" required="" disabled="disabled" value="{{$hosp->facilityname}}">
                </td>
                <td style="width:200px" class="toRemoveaddTT">
                  <input type="number" class="toRemoveaddTT form-control toChange" required="" min="0" disabled="disabled" value="{{$hosp->noofbed}}">
                </td>
                <td>
                  <div class="input-group mb-3">
                    <input class="form-control" type="number" name="tya[]" min="1" max="100" required="" value="{{$hosp->tya}}">
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="input-group mb-3">
                    <input class="form-control" type="number" name="aya[]" min="1" max="100" required="" value="{{$hosp->aya}}">
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                </td>
                <td class="avepsca">
                  <div class="input-group mb-3">
                    <input type="text" class="form-control" name="apty[]" required="" readonly="" value="{{$hosp->apty}}">
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                </td>
              </tr>
              @endforeach
            @endisset --}}
          </tbody>
        </table>
        <table class="table table-bordered mt-3">
          <tbody>
             <tr>
              <td colspan="4" class="text-right font-weight-bold">
                Overall Average Occuancy Rate of Existing Hospitals for Past 2 Yrs.:
              </td>
              <td id="pscaAve" class="font-weight-bold"></td>
            </tr>
            <tr>
              <td colspan="6" class="text-center font-weight-bold">
                -----END OF LIST-----
              </td>
            </tr>
          </tbody>
        </table>
      </div>

       <div class="container-fluid border mb-3 mt-5 font-weight-bold pt-3 hideOnLess" hidden>
        2. Determination of Travel Time from Proposed Hospital To Existing Hospitals in Primary and Secondary Catchment Area
        <table class="table table-bordered mt-3">
          <thead id="pheh">
            <tr>
              <td>Existing Hospitals</td>
              <td>Location</td>
              <td class="text-center">Travel Time to Proposed Hospital</td>
            </tr>
          </thead>
          <tbody id="addNewRow4">

          </tbody>
        </table>
        <table class="table table-bordered mt-3">
          <thead>
            <tr>
              <td colspan="1" class="text-center font-weight-bold">
                -----END OF LIST-----
              </td>
            </tr>
          </thead>
        </table>
      </div>

      <div class="container-fluid border mb-3 mt-5 font-weight-bold pt-3 hideOnLess" hidden>
        3. ACCEESSIBILITY AND STRATEGIC LOCATION
        <table class="table table-bordered mt-3">
          <thead id="pheh">
            <tr>
              <td></td>
              <td colspan="2" class="text-center">Yes/NO</td>
              <td class="text-center">Remarks</td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                Accessibility (Accessible by the usual means of transportation during most part of the year)
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="yesa" name="acc" value="1">
                  <label class="custom-control-label" for="yesa">Yes</label>
                </div>
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="noa" name="acc" value="0">
                  <label class="custom-control-label" for="noa">No</label>
                </div> 
              </td>
              <td>
                <textarea name="remarksacc" cols="20" rows="3" class="form-control"></textarea>
              </td>
            </tr>
            <tr>
              <td>
                Strategic Location
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="yesst" name="st" value="1">
                  <label class="custom-control-label" for="yesst">Yes</label>
                </div>
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="nost" name="st" value="0">
                  <label class="custom-control-label" for="nost">No</label>
                </div> 
              </td>
              <td>
                <textarea name="remarksst" cols="20" rows="3" class="form-control"></textarea>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="container-fluid border mb-3 mt-5 font-weight-bold pt-3 hideOnLess" hidden>
        4. INTEGRATION WITH PROVINCIAL HOSPITAL DEVELOPMENT PLAN
        <table class="table table-bordered mt-3">
          <thead id="pheh">
            <tr>
              <td></td>
              <td colspan="2" class="text-center">Yes/NO</td>
              <td class="text-center">Documentary Proof/Remarks</td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                There is a Local (Provincial) Hospital Development Plan that is Approved by the Department of Health.
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="yeshdp" name="hdp" value="1">
                  <label class="custom-control-label" for="yeshdp">Yes</label>
                </div>
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="nohdp" name="hdp" value="0">
                  <label class="custom-control-label" for="nohdp">No</label>
                </div> 
              </td>
              <td>
                <textarea name="remarkshdp" cols="20" rows="3" class="form-control"></textarea>
              </td>
            </tr>
            <tr>
              <td>
                The Proposed hospital is integrated with the Local (Provincial) Hospital Development Plan
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="yestph" name="tph" value="1">
                  <label class="custom-control-label" for="yestph">Yes</label>
                </div>
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="notph" name="tph" value="0">
                  <label class="custom-control-label" for="notph">No</label>
                </div> 
              </td>
              <td>
                <textarea name="remarkstph" cols="20" rows="3" class="form-control"></textarea>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="container-fluid border mb-3 mt-5 font-weight-bold pt-3 hideOnLess" id="trackRecord" hidden>
        5. TRACK RECORD
        <table class="table table-bordered mt-3">
          <thead id="pheh">
            <tr>
              <td>Name of Existing Hospital Currently Being Operated/ Managed by Proponent, if any. *</td>
              <td>Location</td>
              <td class="text-center" colspan="2">Good Compliance to licensing Requirement</td>
              <td class="text-center" colspan="2">Few Verified Complaints</td>
              <td class="text-center">Remarks</td>
            </tr>
          </thead>
          <tbody id="addNewRow5">
            @foreach($track as $tracks)
            <tr>
              <input type="hidden" name="id[]" value="{{$tracks->id}}">
              <td>{{$tracks->facilityname}}</td>
              <td>{{$tracks->location1}}</td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="gclryes{{$tracks->id}}" name="gclr{{$tracks->id}}" value="1">
                  <label class="custom-control-label" for="gclryes{{$tracks->id}}">Yes</label>
                </div>
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="gclrno{{$tracks->id}}" name="gclr{{$tracks->id}}" value="0">
                  <label class="custom-control-label" for="gclrno{{$tracks->id}}">No</label>
                </div> 
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="fvcyes{{$tracks->id}}" name="fvc{{$tracks->id}}" value="1">
                  <label class="custom-control-label" for="fvcyes{{$tracks->id}}">Yes</label>
                </div>
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="fvcno{{$tracks->id}}" name="fvc{{$tracks->id}}" value="0">
                  <label class="custom-control-label" for="fvcno{{$tracks->id}}">No</label>
                </div> 
              </td>
              <td>
                <textarea name="remarks{{$tracks->id}}" cols="20" rows="3" class="form-control"></textarea>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        <table class="table table-bordered mt-3">
          <thead>
            <tr>
              <td colspan="1" class="text-center font-weight-bold">
                -----END OF LIST-----
              </td>
            </tr>
          </thead>
        </table>
      </div>

      <div class="container-fluid border mb-3 mt-5 font-weight-bold pt-3 hideOnLess" hidden>
        SUMMARY
        <table class="table table-bordered mt-3">
          <thead id="pheh">
            <tr>
              <td>Criteria</td>
              <td colspan="2" class="text-center">Satisfied</td>
              <td class="text-center">Remarks</td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                1. Bed-to-Population Ratio
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="yesbpp" name="bpp" value="1">
                  <label class="custom-control-label" for="yesbpp">Yes</label>
                </div>
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="nobpp" name="bpp" value="0">
                  <label class="custom-control-label" for="nobpp">No</label>
                </div> 
              </td>
              <td>
                <textarea name="remarksbpp" cols="20" rows="3" class="form-control"></textarea>
              </td>
            </tr>
            <tr>
              <td>
                2. Travel Time
                    At least one hour away by the usual means of transportation during the most part of the year from the nearest existing hospital
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="yestt" name="tt" value="1">
                  <label class="custom-control-label" for="yestt">Yes</label>
                </div>
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="nott" name="tt" value="0">
                  <label class="custom-control-label" for="nott">No</label>
                </div> 
              </td>
              <td>
                <textarea name="remarkstt" cols="20" rows="3" class="form-control"></textarea>
              </td>
            </tr>
            <tr>
              <td>
                3. Accessibility and Strategic Location
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="yesasl" name="asl" value="1">
                  <label class="custom-control-label" for="yesasl">Yes</label>
                </div>
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="noasl" name="asl" value="0">
                  <label class="custom-control-label" for="noasl">No</label>
                </div> 
              </td>
              <td>
                <textarea name="remarksasl" cols="20" rows="3" class="form-control"></textarea>
              </td>
            </tr>
            <tr>
              <td>
                4. Integration with local (provincial) hospital development plan, if available
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="yesilh" name="ilh" value="1">
                  <label class="custom-control-label" for="yesilh">Yes</label>
                </div>
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="noilh" name="ilh" value="0">
                  <label class="custom-control-label" for="noilh">No</label>
                </div> 
              </td>
              <td>
                <textarea name="remarksilh" cols="20" rows="3" class="form-control"></textarea>
              </td>
            </tr>
            <tr>
              <td>
                5. Acceptable Track Record
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="yesatr" name="atr" value="1">
                  <label class="custom-control-label" for="yesatr">Yes</label>
                </div>
              </td>
              <td>
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="noatr" name="atr" value="0">
                  <label class="custom-control-label" for="noatr">No</label>
                </div> 
              </td>
              <td>
                <textarea name="remarksatr" cols="20" rows="3" class="form-control"></textarea>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="container-fluid mt-1 mb-2">
        <span class="lead font-weight-bold">Comments</span>:
        <textarea name="comments" id="" cols="30" rows="10" class="form-control" required></textarea>
      </div>
      <div class="container-fluid mt-1 mb-2">
        <span class="lead font-weight-bold mt-3">Recommendation:</span><br>
          <a>To <span><select style="width:150px;" name="verd" class="form-control d-inline"><option value="1">Grant</option><option value="0">Disapprove</option></select></span> the certificate of Need to <strong>{{$AppData->facilityname}}</strong></a>
      </div>
      <div class="container-fluid mt-1 mb-2">
        With Approved bed capacity of:
        <input type="text" class="form-control" name="ubnval">
      </div>

      <div class="container-fluid mt-1 mb-2">
        <span class="lead font-weight-bold mt-3">Committee Members:</span><br>
          <table class="table table-stripped">
            <thead>
              <tr>
                <td class="font-weight-bold">PRINTED NAME</td>
                <td class="font-weight-bold">POSITION</td>
                <td class="font-weight-bold">Participated</td>
              </tr>
            </thead>
            <tbody>
              @foreach($members as $member)
              <tr>
                <td>{{ucfirst($member->fname.' '. (!empty($member->mname) ? $member->mname.',' :'').$member->lname)}}</td>
                <td>
                  @switch($member->pos)
                    @case('LO')
                      Licensing Officer
                    @break
                    @case('MO')
                      MO
                    @break
                    @case('C')
                      Chief
                    @break
                  @endswitch
                </td>
                <td>
                  <div class="custom-control custom-checkbox">
                    <input checked="" type="checkbox" class="custom-control-input" id="member{{$member->committee}}" name="membersPart[]" value="{{$member->committee}}">
                    <label class="custom-control-label" for="member{{$member->committee}}">Yes</label>
                  </div> 
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
      </div>
    </div>
      <div class="container mt-5">
        <div class="row">
          <div class="col-md"> <button class="btn btn-primary float-right" style="padding: 10px;" type="submit">Submit</button></div>
          <div class="col-md "> <button class="btn btn-primary " style="padding: 10px;" type="button" id="draft">Save for draft</button> </div>
        </div>
      </div>
    </form>
  </div>

  <script>
    let population = processPopulationCount();
    let counterForDom = 0;
    let counterForMain = 0;  
    $( document ).ready(function() {
      processPopulationCountInpt()
});  
   
    $(document).on('keyup keypress change keydown paste',$('[name="est[]"]'),function(){
    // $(document).on('keyup keypress change keydown paste',$('[name="catchment[]"]'),function(){
      processPopulationCount();
      processPopulationCountInpt()
      getBPR();
      getPBN();
      getUBN();
      avepsca();
    })
    $(function(){
      $(".hideOnLess").removeAttr('hidden');
      // $("select[name=verd]").val('0').attr('disabled',true);
      // $("#qwe").submit(function(event) {
      //   event.preventDefault();
      //   console.log($(this).serialize());
      // });
      $("#draft").click(function(event) {
        let data = $("#evalSave").serialize()+'&draft=true';
       console.log(data)
        $.ajax({
          method: 'POST',
          data: data,
          success: function(a){
            console.log(a)
            if(a == 'DONE'){
              alert('Saved Successfully!');
            }
          }
        })
      });
      $(document).on('ready',".toTotal",function(){
        $(".toTotal").trigger('click');
      });
      $(document).on('keyup',".toTotal",function(){
        getABCCount();
      });
      $(document).on('keyup',"input[name='tya[]'],input[name='aya[]'],input[name='apty[]']",function(){
        processPopulationCount();
        if(getBPR().toFixed(2) < 1){
          $("#psc").hide();
          $("input[name=pscaval]").val('');
        } else {
          $("#psc").show();
          $("#pscaAve").html(avepsca() + ' %');
          $("input[name=pscaval]").val(avepsca());
      }
      });
      $(document).on('change','select[name="loc[]"]',function(){
        $('select[name="loc[]"]').attr('value',$(this).val());
      })
    });
    function processPopulationCount(){
      let totalValue = 0;
      $('[name="est[]"]').map(function(index, elem) {
      // $('[name="catchment[]"]').map(function(index, elem) {
          totalValue += Number($(elem).val());
      })
      $('.totalEdit').text(totalValue);
      return totalValue;
    } 
    
    function processPopulationCountInpt(){
      let totalValue = 0;
      // $('[name="est[]"]').map(function(index, elem) {
      $('[name="catchment[]"]').map(function(index, elem) {
          totalValue += Number($(elem).val());
      })
      $('.totalEditInpt').text(totalValue);
      return totalValue;
    }
    function getABCCount(){
      addTT(1);
      forPSCA();
      let abcCount = 0;
      $('.toTotal').each(function(index, el) {
        abcCount += Number($(el).val());
      });
      $("input[name=ihbval]").val(abcCount);
      $(".IHBTotal").html(numberWithCommas(abcCount));
      $("#bpr").html(getBPR().toFixed(2) + '('+ getBPR() +')');
      $("#pbn").html(getPBN() + '&nbsp; say '+ getPBN().toFixed(0));
      $("#ubn").html(getUBN() + ' bed');
      if(getBPR().toFixed(2) < 1){
        $("#psc").hide();
        $("input[name=pscaval]").val('');
        // $("#pscaHead").next().html('');
      } else {
        $("#psc").show();
        $("#pscaAve").html(avepsca() + ' %');
        $("input[name=pscaval]").val(avepsca());
        forPSCA();
      }
    }
    function forPSCA(){
      let fromMain = $("#mainEl").nextUntil($("#addNewRow1")).clone();
      fromMain.append( 
        '<td>'+
          '<div class="input-group mb-3">'+
            '<input class="form-control" type="number" name=tya[] min="1" max="100" required>'+
            '<div class="input-group-append">'+
              '<span class="input-group-text">%</span>'+
            '</div>'+
          '</div>'+
        '</td>'+
        '<td>'+
          '<div class="input-group mb-3">'+
            '<input class="form-control" type="number" name=aya[] min="1" max="100" required>'+
            '<div class="input-group-append">'+
              '<span class="input-group-text">%</span>'+
            '</div>'+
          '</div>'+
        '</td>'+
        '<td class="avepsca">'+
          '<div class="input-group mb-3">'+
            '<input type="text" class="form-control" name=apty[] required readonly>'+
            '<div class="input-group-append">'+
              '<span class="input-group-text">%</span>'+
            '</div>'+
          '</div>'+
        '</td>');
      // fromMain.insertAfter($("#pscaHead"))
      $("#pscaHead").next().html(fromMain).find($('.toRemove')).remove();
      $("#pscaHead").next().find('.toChange').attr('disabled',true).removeAttr('name').removeClass('toTotal');
    }
    function getBPR(){
      let ihb = $("input[name=ihbval]").val();
      let ibr = Number(ihb / processPopulationCount()) * {{$bed}};
      $("input[name=bprval]").val(ibr.toFixed(2));
      return ibr;
    }
    function getPBN(){
      let pbn = Number(processPopulationCount()) / {{$bed}};
      $("input[name=pbnval]").val(pbn.toFixed(0));
      $(".pbn").text(pbn.toFixed(0));
      return pbn;
    }
    function getUBN(){
      let ubn = Number($("input[name=pbnval]").val()) - Number($("input[name=ihbval]").val());
      $("input[name=ubnval]").val(ubn);
      return ubn;
    }
    function getToTalCountOf(element,elToIns){
    let allcount = 0;
      $(element).each(function(index, el) {
        allcount += Number($(el).val());
      });
      $(elToIns).html(numberWithCommas(allcount));
    }
    function avepsca(){
      let psca, tya, aya, apty, totalT = 0;
      let lenghtpsca = $(".avepsca").length;
      if($(".avepsca").length > 0){
        $(".avepsca").each(function(index, el) {
          tya = $(this).parent().find('input[name="tya[]"]').val();
          aya = $(this).parent().find('input[name="aya[]"]').val();
          if(tya > 0 && aya > 0){
            apty = $(this).parent().find('input[name="apty[]"]').val((Number(tya)+ Number(aya)) / 2);
            if(tya.length > 0 && aya.length > 0 && apty.length > 0 && lenghtpsca > 0){
              totalT = (Number(totalT) + Number(apty.val()) / lenghtpsca);
              // totalT = totalT + (Number(tya) + Number(aya)) / Number(apty);
            }
          }
        });
        // if(isNaN(totalT) ? 'Please Input' : (totalT) >= 85 ? addTT(1) : addTT(2));
      }
      return (isNaN(totalT) ? 'Please Input' : totalT);
    }
    function addTT(selection){
      if(selection == 1){
        let fromMain = $("#mainEl").nextUntil($("#addNewRow1")).clone();
        fromMain.append( 
         '<td style="width:200px"><input type="text" class="form-control" name=ttph[] required></td>');
        $("#pheh").next().html(fromMain).find($('.toRemoveaddTT')).remove();
        $("#pheh").next().find('.toChange').attr('disabled',true).removeAttr('name').removeClass('toTotal');
        $("#pheh").next().find('select[name="locabc[]"]').replaceWith('<input class="form-control" disabled value="'+$('select[name="locabc[]"]').val()+'">');
        // $(".hideOnLess").removeAttr('hidden');
        $("select[name=verd]").val('1').removeAttr('disabled');
      } else {
        $("#pheh").next().html("");
        // $(".hideOnLess").attr('hidden',true);
        $("select[name=verd]").val('0').attr('disabled',true);
      }
    }
    function createBPR(dis = null){
      var tp = document.getElementsByName("type[]");
          var nonex = "non";
          if(tp){
            for(var i = 0; i < tp.length ; tp++){
              if(tp[i].value == "Primary"){
                nonex = "ex";
              }
            }
            
          }
      console.log(nonex)

      counterForMain++;
      let toInsert = 
        '<tr class="'+counterForMain+'">'+
          '<td>'+
          '<button class="btn btn-danger" type="button" data-toggle="tooltip" data-placement="top" onclick="deleteRow('+counterForMain+');" title="Remove"><i class="fa fa-times"></i></button>'+
          '</td>'+
          '<td>'+
            '<select name="type[]" class="form-control">'+
            (nonex == "non" ? '<option value="Primary" selected>Primary</option>' : '')
              
              +
              '<option value="Secondary">Secondary</option>'
              +
            '</select>'+
          '</td>'+
          '<td><input type="text" name="addr[]" class="form-control"></td>'+

          '<td><input type="text" '+ (dis?  'disabled' : ' ')  +' name="catchment[]" class="form-control"></td>' +

          '<td><input type="text" name="est[]" class="form-control"></td>'+
        '</tr>'
        $(toInsert).insertBefore($('#mainCatch'));    
        processPopulationCount();

       
    }

    function getAddress(){
      
      let option = $('[name="addr[]"]').map(function(){return $(this).val()}), toReturn = '';
      for (var i = 0; i < option.length; i++) {
        toReturn += '<option value="'+option[i]+'">'+option[i]+'</option>';
      }
      return toReturn;
    }

    function addNewRowA(elName, ind, elToInsName, removeX = false) {
      counterForDom +=1; 
      let idom = document.getElementById(elName);
      let toInsertForX = '<td class = "toRemoveaddTT toRemove"><button class="btn btn-danger" data-toggle="tooltip" data-placement="top" type="button" title="Remove" onclick="deleteRow(\'trd'+(counterForDom + 1001)+'\');"><i class="fa fa-times"></i></button></td>';
      if(idom != undefined || idom != null) {
        if(removeX){
          toInsertForX = '<td class="toRemoveaddTT toRemove"></td>';
        }
        whatToInsert = [
        '<tr class="trd'+(!removeX ? (counterForDom + 1001) : '')+'">'+
          toInsertForX+
          '<td><input class="form-control toChange" type="text" name=existHosp'+elToInsName+'[] required></td>'+
          '<td class = "toRemove"><select class="form-control" name="loc'+elToInsName+'[]" required><option value hidden disabled selected></option>'+
            getAddress()+
          '</select></td>'+
          '<td style="width:200px" class="toRemoveaddTT"><input type="number" class="toRemoveaddTT form-control toTotal toChange" name="'+elToInsName+'[]" required></td>'+
          '<td class = "toRemove toRemoveaddTT"><select class="form-control" name="type'+elToInsName+'[]" required><option value hidden disabled selected></option>@if(count($brp[2]) > 0) @foreach($brp[2] AS $each) <option value="{{$each->facid}}">{{$each->facname}}</option> @endforeach @endif<option value="NA">N/A</option></select></td>'+
        '</tr>'
        //+ , '<tr id="trd'+(counterForDom + 1001)+'">'+
        //   '<td><button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Remove" onclick="deleteRow(\'trd'+(counterForDom + 1001)+'\');"><i class="fa fa-times"></i></button></td>'+
        //   '<td><input type="text" name=abcefg[] class="form-control"></td>'+
        //   '<td><input class="form-control" type="number" name=qwe[]></td>'+
        //   '<td><input class="form-control" type="number" name=tya[]></td>'+
        //   '<td><input class="form-control" type="number" name=aya[]></td>'+
        //   '<td class="avepsca"><input type="text" class="form-control" name=apty[]></td>',
        //   '<tr id="trd'+(counterForDom + 1001)+'">'+
        //   '<td><button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Remove" onclick="deleteRow(\'trd'+(counterForDom + 1001)+'\');"><i class="fa fa-times"></i></button></td>'+
        //   '<td><input type="text" name=abcefg[] class="form-control"></td>'+
        //   '<td><select class="form-control" name="type[]"><option value hidden disabled selected></option>'+
        //     @if(count($brp[1]) > 0)
        //       @for($i = 0; $i < count($brp[1]); $i++)
        //       '<option value="{{$brp[1][$i]}}">{{$brp[1][$i]}}</option>'+
        //       @endfor
        //     @endif
        //   '</select></td>'+
        //   '<td style="width:200px"><input type="text" class="form-control" value="Less Than 1 Hour" name=apty[]></td>'+
        // '</tr>'
        ];
        // addTT(1);
        if(whatToInsert.indexOf(ind) < 0) {
          $(whatToInsert[ind]).insertBefore(idom);    
          // idom.outerHTML += whatToInsert[ind];
        }
      }
      addTT(1);
      forPSCA();
      return counterForDom+1;
    }
    function numberWithCommas(number) {
        var parts = number.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    }
    function deleteRow(elName) {
      let idom = $("."+elName);
      processPopulationCount();
      getBPR();
      getPBN();
      getUBN();
      if(idom != undefined || idom != null) {
        $(idom).remove();
        avepsca();
        $("#pscaAve").html(avepsca() + ' %');
        $("input[name=pscaval]").val((avepsca() == 'Please Input' ? 0 : avepsca()));
      }
    }
    $(document).on('keyup',"input[name='tya[]'], input[name='aya[]']",function(){
      if($(this).val() > 99){
        alert('Please provide percentage less than 100!');
        $(this).val('').focus();
      }
    });
    $(document).on('change keyup',"input[name='existHospabc[]'], input[name='locabc[]'], input[name='typeabc[]']",function(){
      addTT(1);
      forPSCA();
      // forPSCA();
    });

      @if(!empty($brp[0]))
        @foreach($brp[0] as $b)
        @php $total += $b->population; @endphp
        createBPR(1);
        $('.'+counterForMain).find('[name="type[]"]').val('{{($b->type == 1 ? 'Secondary' : 'Primary')}}');
        $('.'+counterForMain).find('[name="addr[]"]').val('{{$b->location}}');
        $('.'+counterForMain).find('[name="catchment[]"]').val('{{$b->population}}');
        $('.'+counterForMain).find('[name="est[]"]').val('{{$b->eval_est}}');
        @endforeach
        processPopulationCount();
      @endif

    /*@isset($edit)
      let rowID,counterExist = 0;
      let existHpt = {'data' : 'hospitals','existHospabc[]' : 'facilityname','locabc[]' : 'location','abc[]' : 'noofbed' ,'typeabc[]' : 'cat_hos', 'ttph[]' : 'ttph'};
      let occupancy = {'data' : 'hospitals', 'tya[]' : 'tya', 'aya[]' : 'aya'};
      let ltoHpt = {'data' : 'hospitals','existHospcde[]' : 'facilityname','loccde[]' : 'location','cde[]' : 'noofbed' ,'typecde[]' : 'cat_hos'};      
    @endisset*/
    @isset($savedData)
    $(document).ready(function(){
      let draftData = JSON.parse('{!!$savedData!!}');
      if(draftData.length){
        alert('Your saved data is being re-inputted');
        let name, loc, abc, lvl = '';
        draftData.forEach(function(el,key){
          switch (el['fromWhere']) {
            case "dib":
             name = 'existHospabc[]';
             loc = 'locabc[]';
             abc = 'abc[]';
             lvl =  'typeabc[]';
             addNewRowA('addNewRow1', 0, 'abc');
              break;
              case "ihb":
               name = 'existHospcde[]';
               loc = 'loccde[]';
               abc = 'cde[]';
               lvl =  'typecde[]';
              addNewRowA('addNewRow2', 0, 'cde');
              break;
          }
          $(".trd"+(counterForDom + 1001)+":eq(0)").find('[name="'+name+'"]').val(el['facilityname']).trigger('keyup').trigger('change');
          $(".trd"+(counterForDom + 1001)+":eq(0)").find('[name="'+loc+'"]').val(el['location']).trigger('keyup').trigger('change');
          $(".trd"+(counterForDom + 1001)+":eq(0)").find('[name="'+abc+'"]').val(el['noofbed']).trigger('keyup').trigger('change');
          $(".trd"+(counterForDom + 1001)+":eq(0)").find('[name="'+lvl+'"]').val(el['cat_hos']).trigger('keyup').trigger('change');
        })
      } else {
        $(document).ready(function(){
          setTimeout(function() {
          addNewRowA('addNewRow1', 0, 'abc', true);
          }, 100);
        })
      }
    })   
    @endisset
  </script>
  @endsection
@else
  <script type="text/javascript">window.location.href= "{{ asset('employee') }}";</script>
@endif