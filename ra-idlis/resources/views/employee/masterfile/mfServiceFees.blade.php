@if (session()->exists('employee_login'))


@extends('mainEmployee')
@section('title', 'Service Charges Master File')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">


<style>
    .modal-xl {
        width: 98%;
        max-width: 2000px;
    }

    td {
        padding-left: 5px;
        padding-right: 5px;
    }
</style>
<div class="content p-4">
    <a href="#" title="Add New Service Charge" data-toggle="modal" data-target="#myModal"><button class="btn-primarys"><i class="fa fa-plus-circle"></i>&nbsp;Add new</button></a>
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" style="border-radius: 0px;border: none;">
                <div class="modal-body text-justify" style="color: black;">
                    <h5 class="modal-title text-center"><strong>Add New Service Fee</strong></h5>
                    <hr>
                    <div>
                        <table style="width: 100%;">
                            <tr>
                                <td width="400">
                                    <label for="servetype">Service Type</label>
                                    <select data-live-search="true" data-style="text-dark form-control custom-selectpicker" class="form-control selectpicker show-menu-arrow " id="servetype" name="ocid" data-style="text-dark form-control custom-selectpicker" data-size="5" required>
                                        <option>Please select</option>
                                        @foreach($factypes as $key => $value)
                                        <option value="{{$value->facid}}">{{$value->facname}} {{$value->spec ? '('.$value->spec.')' : '('.$value->hgpdesc.'-'.$value->anc_name.')' }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td width="120">
                                    <label for="ocid">Ownership type</label>
                                    <select class="form-control show-menu-arrow" id="ocid" name="ocid" data-style="text-dark form-control custom-selectpicker" data-size="5" required>
                                        <option>Please select</option>
                                        <option value="G">Government</option>
                                        <option value="P">Private</option>
                                    </select>
                                </td>
                                <td width="200">
                                    <label for="facmode">Instituitional Character</label>
                                    <select class="form-control  show-menu-arrow" id="facmode" name="facmode" data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" required>
                                        <option>Please select</option>
                                        <option value="2">Free Standing</option>
                                        <option value="4">Institution Based (Hospital)</option>
                                        <option value="5">Institution Based (Non-Hospital)</option>
                                    </select>
                                </td>
                                <td width="120">
                                    <label for="funcid">Function </label>
                                    <select class="form-control  show-menu-arrow" data-funcid="main" id="funcid" name="funcid" data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" required>
                                        <option>Please select</option>
                                        <option value="1">General</option>
                                        <option value="2">Specialty</option>
                                        <option value="3">Not Applicable</option>
                                    </select>
                                </td>
                                <td width="130">
                                    <label for="funcid">Initial Amount </label>
                                    <input type="number" class="form-control  show-menu-arrow" id="inamount" name="inamount">
                                <td width="130">
                                    <label for="reamount">Renewal Amount </label>
                                    <input type="number" class="form-control  show-menu-arrow" id="reamount" name="reamount">
                                </td>
                                <td width="120">
                                    <label for="reperiod">Renewal Period</label>
                                    <input type="number" class="form-control  show-menu-arrow" id="reperiod" name="reperiod">
                                </td>
                                <td width="220">
                                    <label for="reperiod">Remarks</label>
                                    <input type="text" class="form-control  show-menu-arrow" id="remarks" name="remarks">
                                </td>
                            </tr>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js" integrity="sha512-bZS47S7sPOxkjU/4Bt0zrhEtWx0y0CRkhEp8IckzK+ltifIIE9EMIMTuT/mEzoIMewUINruDBIR/jJnbguonqQ==" crossorigin="anonymous"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
   
   

    @endsection
    @else
    <script type="text/javascript">
        window.location.href = "{{ asset('employee') }}";
    </script>
    @endif