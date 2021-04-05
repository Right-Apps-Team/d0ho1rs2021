<div class="col-md-12"><b class="text-primary">APPLICATION</b></div>
<div class="form-group col-md-6">
    <label for="typeOfApplication">Type of Application <span class="text-danger">*</span></label>
    <?php
        $hfser_id = isset($appdata->hfser_id) ? $appdata->hfser_id : '';
        
    ?>
    <select 
        class="form-control selectpicker show-menu-arrow"
        id="typeOfApplication"
        name="hfser_id"
        required
        disabled
        data-live-search="true"
        data-style="text-dark form-control custom-selectpicker" 
        data-size="5">
        <option>Please select</option>
        <option value="CON">Certificate of Need</option>
        <option value="PTC">Permit to Construct</option>
        <option value="ATO">Authority to Operate</option>
        <option value="COA">Certificate of Accreditation</option>
        <option value="LTO">License to Operate</option>
        <option value="COR">Certificate of Registration</option>
    </select>
</div>
<div class="form-group col-md-6">
    <label for="facility_name">Facility Name <span class="text-danger">*</span></label>
    <div class="input-group">
        <input 
            type="text" 
            name="facilityname" 
            class="form-control" 
            placeholder="FACILITY NAME" 
            id="facility_name" 
            onChange="checkFacilityName(this)" 
            required
        >
        <div class="input-group-append" data-toggle="tooltip" data-placement="top" title="" data-original-title="Manually search existing Facility">
            <button class="btn btn-dark" type="button" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-info"></i></button>
        </div>
        <small id="facility_name_feedback" class="feedback"></small>
    </div>
</div>
