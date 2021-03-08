<p>&nbsp;</p>
<div class="row col-md-12">
    <div class="col-md-7"><b class="text-primary">CLASSIFICATION ACCORDING TO</b></div>
</div>
<div class="col-md-4">
    <label for="ownership">Ownership <span class="text-danger">*</span></label>
    
    <select 
        class="form-control selectpicker show-menu-arrow" 
        id="ocid" 
        name="ocid"
        data-live-search="true" 
        data-style="text-dark form-control custom-selectpicker"
        data-size="5"
        required
        onChange="fetchClassification(this)"
    >
        <option>Please select</option>
        <option value="G">Government</option>
        <option value="P">Private</option>
    </select>
</div>
<div class="col-md-4">
    <label for="classification">Classification <span class="text-danger">*</span></label>
    <select 
        class="form-control selectpicker show-menu-arrow" 
        id="classification"
        disabled
        name="classid"
        data-live-search="true" 
        data-style="text-dark form-control custom-selectpicker"
        data-size="5"
        required
        onChange="fetchSubClass(this)"
        >
        <option>Please select</option>
    </select>
</div>
<div class="col-md-4">
    <label for="subclass">Sub Classification <span class="text-danger">*</span></label>
    <select 
        class="form-control selectpicker show-menu-arrow" 
        id="subclass"
        disabled
        name="subClassid"
        data-live-search="true" 
        data-style="text-dark form-control custom-selectpicker"
        data-size="5"
        required
        >
        <option>Please select</option>
    </select>
</div>
<div class="mb-2 col-md-12">&nbsp;</div>
<div class="col-md-6">
    
    <label for="facmode">Institutional Character <span class="text-danger">*</span></label>
    <div class="row">
        <div class="col-lg-10 col-md-10 col-xs-10">
            <select 
                class="form-control selectpicker show-menu-arrow" 
                id="facmode" 
                name="facmode"
                data-live-search="true" 
                data-style="text-dark form-control custom-selectpicker"
                data-size="5"
                required
            >
                <option>Please select</option>
                <option value="2">Free Standing</option>
                <option value="4">Institution Based (Hospital)</option>
                <option value="5">Institution Based (Non-Hospital)</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-2 col-xs-2">
            <i  class="fa fa-question-circle" 
                id="institution_helper"
                aria-hidden="true" 
                style="font-size: 34px; cursor: pointer"
                data-toggle="tooltip" 
                data-html="true" 
                title="
                <div class='text-left'>
                    <h3>Institution based</h3>
                    <ul>
                        <li>A health Facility that is located within the premises and operates as part of an institution</li>
                    </ul>
                    <h3>Free Standing</h3>
                    <ul>
                        <li>A health Facility that is not attached to an insitution and operates independently</li>
                    </ul>
                    <h3>Hospital Based</h3>
                    <ul>
                        <li>A health Facility that is located in a Hospital</li>
                    </ul>
                </div>
                "
                ></i>
        </div>
    </div>
</div>
<div class="col-md-6">
    <label for="funcid">Function <span class="text-danger">*</span></label>
    <select 
        class="form-control selectpicker show-menu-arrow" 
        id="funcid" 
        name="funcid"
        data-live-search="true" 
        data-style="text-dark form-control custom-selectpicker"
        data-size="5"
        required
        >
        <option>Please select</option>
        <option value="1">General</option>
        <option value="2">Specialty</option>
        <option value="3">Not Applicable</option>
    </select>
</div>