<div class="mb-2 col-md-12">&nbsp;</div>
<div class="col-md-12"><b class="text-primary">Type of Health Facility: <span class="text-danger">*</span></b></div>

@foreach($hfaci_service_type as $h)
<div class="col-md-3">
    <label><input type="radio" name="hgpid" value="{{$h->hgpid}}"/> {{$h->hgpdesc}}</label>
</div>
@endforeach