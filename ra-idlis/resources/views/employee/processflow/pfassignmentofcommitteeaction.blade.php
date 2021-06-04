



@if (session()->exists('employee_login'))   
  @extends('mainEmployee')
  @section('title', 'Committee Assignment')
  @section('content')

  
<!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script> -->
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>

<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/i18n/defaults-*.min.js"></script>

  <input type="text" id="CurrentPage" hidden="" value="PF014">
  <div class="content p-4">
    <div class="card">
      <div class="card-header bg-white font-weight-bold">
         Committee Assignment 
         <button class="btn btn-primary" onclick="window.history.back();">Back</button>
      </div>
      <div class="card-body">
        <div class="col-sm-12">
          <h2>@isset($AppData) {{$AppData->facilityname}} @endisset</h2>
          <h5>@isset($AppData) {{strtoupper($AppData->streetname)}}, {{strtoupper($AppData->brgyname)}}, {{$AppData->cmname}}, {{$AppData->provname}} @endisset</h5>    
        </div>
        <hr>
        @if(count($free) > 0)
          <div class="container-fluid">
            <button class="btn btn-primary p-3" data-toggle="modal" data-target="#viewModal"><i class="fa fa-plus-circle"></i> Add</button>
          </div>
        @endif
        @if($canEval)
          {{-- <div class="container-fluid">
            <button class="btn btn-primary p-2" data-toggle="modal" data-target="#evaluate"><i class="fa fa-file"></i> Evaluate Application</button>
          </div> --}}
        @endif
        {{-- @if($hferc_data->HFERC_eval == 1) --}}
        <br>
          <div class="container-fluid">
            <button class="btn btn-primary p-2" onclick="window.location.href='{{asset('employee/dashboard/processflow/view/conevalution/'.$AppData->appid)}}'"><i class="fa fa-file"> </i> View Evaluation</button>
          </div>
        {{-- @endif --}}
        {{csrf_field()}}
        <div class="table-responsive mt-5">
          <table class="table table-stripped" id="example">
            <thead>
              <tr>
                <th>Member Name</th>
                <th>Committee Position</th>
                <th>Options</th>
              </tr>
            </thead>
            <tbody>
            @foreach($hferc as $members)
              <tr>
                <td style="font-size: 20px;">
                  {{ucfirst($members->fname.' '. (!empty($members->mname) ? $members->mname.',' :'').$members->lname)}}
                </td>
                <td>
                  @switch($members->pos)
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
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#viewModalEdit" onclick="showData('{{$members->committee}}','{{ucfirst($members->fname.' '. (!empty($members->mname) ? $members->mname.',' :'').$members->lname)}}','{{$members->pos}}')">
                      <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-danger" onclick="showDelete('{{$members->committee}}');">
                      <i class="fa fa-ban" aria-hidden="true"></i>
                    </button>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  @if(count($free) > 0)
  <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog  modal-lg" role="document">
      <div class="modal-content" style="border-radius: 0px;border: none;">
        <div class="modal-body" style=" background-color: #272b30;color: white;">
          <h5 class="modal-title text-center">Add Committee Member</h5>
          <hr>
          <div class="col-sm-12">
            <form id="memberadd">
              {{csrf_field()}}
                <div class="container pl-5">
                  <div class="row mb-2">
                    <!-- <div class="col-sm">
                      Member Name:
                    </div> -->
                    <div class="col-sm-11">








                    <!-- <select onchange="selpon()" id="selp" class="selectpicker form-control" multiple data-live-search="true">
                    @if(count($free) > 0)
                          @foreach($free as $f)
                            <option value="{{$f->uid}}">{{ucfirst($f->fname.' '. (!empty($f->mname) ? $f->mname.',' :'').$f->lname)}}</option>
                          @endforeach
                        @else
                          <option value="">No Available Employee on this Region</option>
                        @endif
                    </select> -->


                  <!-- <select name="uid" id="uidadd" class="form-control " required>
                        @if(count($free) > 0)
                          @foreach($free as $f)
                            <option value="{{$f->uid}}">{{ucfirst($f->fname.' '. (!empty($f->mname) ? $f->mname.',' :'').$f->lname)}}</option>
                          @endforeach
                        @else
                          <option value="">No Available Employee on this Region</option>
                        @endif
                      </select>
                    </div>
                  </div>
                <div class="row mb-2 mt-3">
                  <div class="col-sm">
                    Commitee Position:
                  </div>
                  <div class="col-sm-11">
                    <select  onchange="selpon()" name="pos" id="pos" class="form-control" required>
                    	<option value="C">Chief</option>
                    	<option value="MO">MO</option>
                      	<option value="LO">Licensing Officer</option>
                    </select>
                  </div>
                </div> -->




                <div style="width:95%; padding-left: 35px">

                  <div class="row col-border-right showAmb">

                      <table class="table table-bordered">
                          <thead>
                              <tr>
                                  <td> <button class="btn btn-success" id="buttonIdAos"><i class="fa fa-plus-circle"></i></button> </td>
                                  <th>
                                      <center>Member Name</center>
                                  </th>
                                  <th>
                                      <center> Commitee Position:</center>
                                  </th>
                              </tr>
                          </thead>
                          <tbody id="body_addOn">
                              <tr id="tr_addOn">
                                  <!-- preventDef -->
                                  <!-- onclick="if(! this.parentNode.parentNode.hasAttribute('id')) { this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode); }" -->
                                  <!-- onClick="$(this).closest('tr').remove();" -->
                                  <td onclick="return preventDef()"> <button class="btn btn-danger "  onclick="if(! this.parentNode.parentNode.hasAttribute('id')) { this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode); }"><i class="fa fa-minus-circle" onclick="return preventDef()"></i></button> </td>
                                  <td>
                                  <select name="uid" id="uidadd" class="form-control " required>
                                @if(count($free) > 0)
                                  @foreach($free as $f)
                                    <option value="{{$f->uid}}">{{ucfirst($f->fname.' '. (!empty($f->mname) ? $f->mname.',' :'').$f->lname)}}</option>
                                  @endforeach
                                @else
                                  <option value="">No Available Employee on this Region</option>
                                @endif
                              </select>
                                  </td>
                                  <td>
                                  <select  onchange="selpon()" name="pos" id="pos" class="form-control" required>
                                    <option value="C">Chief</option>
                                    <option value="MO">MO</option>
                                      <option value="LO">Licensing Officer</option>
                                  </select>
                                  </td>
                              </tr>
                          </tbody>
                      </table>
                  </div>
                  </div>








             
                  
                  <input type="hidden" name="action" value="add">
                  <button class="btn btn-primary pt-2" type="submit">Submit</button>
              </div>

              
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  <div class="modal fade" id="viewModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="border-radius: 0px;border: none;">
        <div class="modal-body" style=" background-color: #272b30;color: white;">
          <h5 class="modal-title text-center">Edit Committee Member</h5>
          <hr>
          <div class="col-sm-12">
            <form id="memberedit">
              {{csrf_field()}}
                <input type="hidden" name="action" value="edit">
                <span id="editBody">
                  
                </span>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if($canEval)
  <div class="modal fade" id="evaluate" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="border-radius: 0px;border: none;">
        <div class="modal-body" style=" background-color: #272b30;color: white;">
          <h5 class="modal-title text-center">Evalute Appication</h5>
          <hr>
          <div class="col-sm-12">
            <form id="evaluateSend">
              <div class="row">
                {{csrf_field()}}
                <div class="col-12 text-uppercase font-weight-bold text-center">
                  Committee
                </div>
              </div>
              <div class="offset-1 d-flex justify-content-center mt-4">
                <input type="hidden" name="action" value="evaluate">
                <div class="custom-control custom-radio custom-control-inline">
                  <input type="radio" required value="1" class="custom-control-input" id="customRadio" name="evaluation" value="customEx">
                  <label class="custom-control-label" for="customRadio">Approve</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                  <input type="radio" required value="2" class="custom-control-input" id="customRadio2" name="evaluation" value="customEx">
                  <label class="custom-control-label" for="customRadio2">Disapprove</label>
                </div>
              </div>
              <div class="col-md-12 mt-4 text-center" style="font-size: 20px;">
                Comments
              </div>
              <div class="col-md-12 mt-3">
                <textarea name="comments" class="form-control" cols="40" rows="10"></textarea>
              </div>
              <div class="d-flex justify-content-center mt-4">
                <div class="custom-control">
                  <button class="btn btn-primary btn-block p-2">Save</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif



  <script type="text/javascript">

document.getElementById("buttonIdAos").addEventListener("click", function(event) {
        event.preventDefault()
        var itm = document.getElementById("tr_addOn");
        var cln = itm.cloneNode(true);
        cln.removeAttribute("id");
        cln.setAttribute("class", "tr_addOn");
        document.getElementById("body_addOn").appendChild(cln);
    });

   document.getElementById("tr_addOn").addEventListener("click", function(event) {
        event.preventDefault()
       
    });

    function getAddedmem(){
   var uid = document.getElementsByName('uid');
   var pos = document.getElementsByName('pos');

    var alladdmem =[];
    if(uid[0].options.length > 0){
    for(var i = 0 ; i < uid.length ; i++){
            const subs = {
                uid: uid[i].value,
                pos: pos[i].value,
            }

            alladdmem.push(subs);
    }

    console.log("alladdmem")
    console.log(alladdmem)
    }
   return alladdmem
}
    
 $(document).ready(function() {$('#selp').selectpicker();});
    
function selpon(){

  var selp = $("#selp").val();
  var pos =$("#pos").val();
  console.log("selp");
  console.log(selp);
   console.log("pos");
  console.log(pos);
}


    $(document).ready(function() {$('#example').DataTable();});
    // var ToBeAddedMembers = [];
    $(function () {
      $('[data-toggle="popover"]').popover();
    })

    function showData(id, name, pos){
      $("#editBody").empty().append(
        '<input name="id" value="'+id+'" type="hidden">'+
        '<div class="container pl-5">'+
        '<div class="row mb-2 mt-3">'+
          '<div class="col-sm">'+
           ' Commitee Position:'+
          '</div>'+
         ' <div class="col-sm-11">'+
            '<select name="pos" id="posqwe" class="form-control" required>'+
              '<option value="C">Chief</option>'+
              '<option value="MO">MO</option>'+
              '<option value="LO">Licensing Officer</option>'+
            '</select>'+
         ' </div>'+
        '</div>'+
          '<button class="btn btn-primary pt-2" type="submit">Submit</button>'+
      '</div>'
      )
      $("#posqwe").val(pos);
    }


    @if($canEval)
      $("#evaluateSend").submit(function(e){
        e.preventDefault();
        $.ajax({
          method: "POST",
          data: $(this).serialize(),
          success: function(a){
            console.log(a);
          }
        })
      })
    @endif

    @if(count($free) > 0)
    $("#memberadd").submit(function(e){
      e.preventDefault();
    console.log($(this).serialize())  
      if($("#pos").val() != '' || $("#uidadd").val() != ''){
        // getAddedmem()
       var sArr = {
        _token: $("input[name=_token]").val(), 
        action:'add',
        type: 'CON',
        members: JSON.stringify(getAddedmem())
       }

       console.log("sArr")
       console.log(sArr)
        // $.ajax({
        //   method: "post",
        //   data: $(this).serialize(),
        //   success:function(a){
        //     if(a == 'done'){
        //       alert('Added Successfully');
        //       location.reload();
        //     }
        //   }
        // })

         $.ajax({
          method: "post",
          data: sArr,
          success:function(a){
            if(a == 'done'){
              alert('Added Successfully');
              location.reload();
            }
          }
        })


      } else {
        alert('All fields are required. Please select from the options');
      }
    })
    @endif

    $("#memberedit").submit(function(e){
      e.preventDefault();
      if($("#posqwe").val() != '' || $("#uid").val() != ''){
        $.ajax({
          method: "post",
          data: $(this).serialize(),
          success:function(a){
            if(a == 'done'){
              alert('Edited Successfully');
              location.reload();
            }
          }
        })


      } else {
        alert('All fields are required. Please select from the options');
      }
    })

    function showDelete(id){
      var r = confirm("Are you want to remove this Committee Member on this Evaluation?");
      if (r == true) {
         $.ajax({
            method: 'POST',
            data: {_token: $("input[name=_token]").val(), 'id': id, 'action':'delete'},
            success: function(data){
              if(data == 'done'){
                alert('Action Completed Successfully');
                location.reload();
              }
            }
         });
      } else {
          txt = "You pressed Cancel!";
      }
    }
  </script>
  @endsection
@else
  <script type="text/javascript">window.location.href= "{{ asset('employee') }}";</script>
@endif

<!-- 
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" /> -->
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script> -->

