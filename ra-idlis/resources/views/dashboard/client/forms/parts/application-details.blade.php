<div class="col-md-12"><b class="text-primary">APPLICATION</b></div>
<div class="form-group col-md-6">
<label for="approving_authority_pos">Application Type<span class="text-danger">*</span></label>
        <select class="form-control" id="aptidnew" name="aptidnew">
           
            <option value="IN" selected="selected" >Initial New</option>
            <option value="R">Renewal</option>
        </select>
</div>

<div class="form-group col-md-6">
    <label for="typeOfApplication">Type of Application <span class="text-danger">*</span></label>
    <?php
    $hfser_id = isset($appdata->hfser_id) ? $appdata->hfser_id : '';
    

    ?>


    <div style="display: none;">
        <select class="form-control selectpicker show-menu-arrow" id="typeOfApplication" name="hfser_id" required data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5">
            <option>Please select</option>
            <option value="CON" {{ 'CON' == $hfser ? 'selected' : '' }}>Certificate of Need</option>
            <option value="PTC" {{ 'PTC' == $hfser ? 'selected' : '' }}>Permit to Construct</option>
            <option value="ATO" {{ 'ATO' == $hfser ? 'selected' : '' }}>Authority to Operate</option>
            <option value="COA" {{ 'COA' == $hfser ? 'selected' : '' }}>Certificate of Accreditation</option>
            <option value="LTO" {{ 'LTO' == $hfser ? 'selected' : '' }}>License to Operate</option>
            <option value="COR" {{ 'COR' == $hfser ? 'selected' : '' }}>Certificate of Registration</option>
        </select>
    </div>
    <div style="display: none;">
        @if ($hfser == 'CON')
        <?php $value = 'Certificate of Need' ?>
        @elseif ($hfser == 'PTC') {
        <?php $value = 'Permit to Construct' ?>
        }
        @elseif ($hfser == 'ATO') {
        <?php $value = 'Authority to Operate' ?>
        }
        @elseif ($hfser == 'COA') {
        <?php $value = 'Certificate of Accreditation' ?>
        }
        @elseif ($hfser == 'LTO') {
        <?php $value = 'License to Operate' ?>
        }
        @else
        <?php $value = 'Certificate of Registration' ?>
        @endif
    </div>
    <div class="input-group">
        <input class="form-control" type="text" value="{{ $value }}" readonly>
    </div>
</div>

<div class="form-group col-md-12">
    <label for="facility_name">Facility Name  <span class="text-danger">*</span></label>
    <div class="input-group">
        <input type="text" name="facilityname" class="form-control" placeholder="FACILITY NAME" 
        value="{{isset($fAddress) && count($fAddress) > 0 ? $fAddress[0]->facilityname : null}}" 
        id="facility_name" onblur="checkFacilityNameNew(this.value)" required> 
        <!-- <input type="text" name="facilityname" class="form-control" placeholder="FACILITY NAME" 
        value="{{isset($fAddress) && count($fAddress) > 0 ? $fAddress[0]->facilityname : null}}" 
        id="facility_name" onChange="checkFacilityName(this)" required> -->
        <div class="input-group-append" data-toggle="tooltip" data-placement="top" title="" data-original-title="Manually search existing Facility">
            <button class="btn btn-dark" type="button" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-search"></i></button>
        </div>
        <small id="facility_name_feedback" class="feedback"></small>
    </div>
</div>

