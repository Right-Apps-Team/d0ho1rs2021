<p>&nbsp;</p>
<div class="col-md-12"><b class="text-primary">FACILTY ADDRESS</b></div>
<div class="col-md-3">
    <label for="region">Region <span class="text-danger">*</span></label>
    @if(isset($fAddress) && count($fAddress) > 0)
    <input class="form-control " name="rgnid" id="region" value="{{$fAddress[0]->rgn_desc}}" disabled />
    @else

    <select class="form-control selectpicker show-menu-arrow" id="region" name="rgnid" required data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" onChange="fetchProvince(this)">
        <option value="">Please select</option>
        @foreach( $regions as $region)
        <option value="{{$region->rgnid}}">{{$region->rgn_desc}}</option>
        @endforeach
    </select>
    @endif
</div>
<div class="col-md-3">
    <label for="province">Province/District <span class="text-danger">*</span></label>
    @if(isset($fAddress) && count($fAddress) > 0)
    <input class="form-control " name="provid" id="province" value="{{$fAddress[0]->provname}}" disabled />
    @else

    <select class="form-control selectpicker show-menu-arrow" id="province" disabled name="provid" required data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" onChange="fetchMonicipality(this)">
        <option value="">Please select</option>
    </select>
    @endif
</div>
<div class="col-md-3">
    <label for="city_monicipality">City/Municipality <span class="text-danger">*</span></label>
    @if(isset($fAddress) && count($fAddress) > 0)
    <input class="form-control " name="cmid" id="city_monicipality" value="{{$fAddress[0]->cmname}}" disabled />
    @else
    <select class="form-control  selectpicker show-menu-arrow" id="city_monicipality" disabled name="cmid" required data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" onChange="fetchBaranggay(this)">
        <option value="">Please select</option>
    </select>
    @endif
</div>
<div class="col-md-3">
    <label for="brgy">Baranggay <span class="text-danger">*</span></label>
    @if(isset($fAddress) && count($fAddress) > 0)
    <input class="form-control " name="brgyid" id="brgy" value="{{$fAddress[0]->brgyname}}" disabled />
    @else
    <select class="form-control selectpicker show-menu-arrow" id="brgy" disabled name="brgyid" data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" required>
        <option value="">Please select</option>
    </select>
    @endif
</div>
<div class="mb-2 col-md-12">&nbsp;</div>
<div class="col-md-4">
    <label for="street_num">Street Number </label>
    <input type="text" class="form-control" id="street_num" name="street_number"placeholder="STREET NUMBER"
     value='{{((isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->street_number: null)}}'
     >
</div>
<div class="col-md-4">
    <label for="street_name">Street name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="street_name" name="street_name" placeholder="STREET NAME"
    value='{{(( isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->street_name: null)}}'
    >
</div>
<div class="col-md-4">
    <label for="zip">Zip Code <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="zip" name="zipcode" required placeholder="ZIP CODE"
    value='{{((isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->zipcode: null)}}'
    >
    <small><span class="text-danger">NOTE: </span>for reference, please follow this <a href="https://www.phlpost.gov.ph/zip-code-search.php" target="_blank">link</a></small>
</div>