@if (session()->exists('employee_login'))   
  @extends('mainEmployee')
  @section('title', 'Committee Assignment')
  @section('content')
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
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="border-radius: 0px;border: none;">
        <div class="modal-body" style=" background-color: #272b30;color: white;">
          <h5 class="modal-title text-center">Add Committee Member</h5>
          <hr>
          <div class="col-sm-12">
            <form id="memberadd">
              {{csrf_field()}}
                <div class="container pl-5">
                  <div class="row mb-2">
                    <div class="col-sm">
                      Member Name:
                    </div>
                    <div class="col-sm-11">
                      <select name="uid" id="uidadd" class="form-control" required>
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
                    <select name="pos" id="pos" class="form-control" required>
                    	<option value="C">Chief</option>
                    	<option value="MO">MO</option>
                      	<option value="LO">Licensing Officer</option>
                    </select>
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
      if($("#pos").val() != '' || $("#uidadd").val() != ''){
        $.ajax({
          method: "post",
          data: $(this).serialize(),
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
