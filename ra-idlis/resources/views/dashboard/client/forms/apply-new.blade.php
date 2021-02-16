<section class="container">
<div class="card">
    <div class="card-header">
        <p class="lead text-center text-danger">Please note: Red asterisk (*) is a required field and may be encountered throughout the system </p>
    </div>
    <div class="card-body">
        <form class="row">
            <div class="col-md-12"><b class="text-primary">APPLICATION</b></div>
            <div class="form-group col-md-6">
                <label for="typeOfApplication">Type of Application <span class="text-danger">*</span></label>
                <select class="form-control" id="typeOfApplication">
                    <option>Please select</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="facility_name">Facility Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="FACILITY NAME" id="facility_name">
                    <div class="input-group-append" data-toggle="tooltip" data-placement="top" title="" data-original-title="Manually search existing Facility">
                        <button class="btn btn-dark" type="button" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-info"></i></button>
                    </div>
                </div>
            </div>



            <p>&nbsp;</p>
            <div class="col-md-12"><b class="text-primary">FACILTY ADDRESS</b></div>
            <div class="col-md-3">
                <label for="region">Region <span class="text-danger">*</span></label>
                <select class="form-control" id="region">
                    <option>Please select</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="province">Province/District <span class="text-danger">*</span></label>
                <select class="form-control" id="province">
                    <option>Please select</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="city_monicipality">City/Municipality <span class="text-danger">*</span></label>
                <select class="form-control" id="city_monicipality">
                    <option>Please select</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="brgy">Baranggay <span class="text-danger">*</span></label>
                <select class="form-control" id="brgy">
                    <option>Please select</option>
                </select>
            </div>
            <div class="mb-2 col-md-12">&nbsp;</div>
            <div class="col-md-4">
                <label for="street_num">Street Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="street_num" placeholder="STREET NUMBER">
            </div>
            <div class="col-md-4">
                <label for="street_name">Street name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="street_name" placeholder="STREET NAME">
            </div>
            <div class="col-md-4">
                <label for="zip">Zip Code <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="zip" placeholder="ZIP CODE">
                <small><span class="text-danger">NOTE: </span>for reference, please follow this <a href="https://www.phlpost.gov.ph/zip-code-search.php" target=
                "_blank">link</a></small>
            </div>
            
            

            <p>&nbsp;</p>
            <div class="col-md-12"><b class="text-primary">FACILTY CONTACT DETAILS</b></div>
            <div class="col-md-3">
                <label for="fac_mobile_number">Facility Mobile No. <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="fac_mobile_number" placeholder="FACILITY MOBILE #">
            </div>
            <div class="col-md-3">
                <label for="facility_landline">Facility Landline <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="facility_landline" placeholder="FACILITY LANDLINE">
            </div>
            <div class="col-md-3">
                <label for="fax">Fax Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="fax" placeholder="FACILITY FAX #">
            </div>
            <div class="col-md-3">
                <label for="fac_email_address">Facility Email Address <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="fac_email_address" placeholder="EMAIL">
            </div>


            


            <p>&nbsp;</p>
            <div class="col-md-12"><b class="text-primary">CLASSIFICATION ACCORDING TO</b></div>
            <div class="col-md-4">
                <label for="ownership">Ownership <span class="text-danger">*</span></label>
                <select class="form-control" id="ownership">
                    <option>Please select</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="classification">Classification <span class="text-danger">*</span></label>
                <select class="form-control" id="classification">
                    <option>Please select</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="subclass">Sub Classification <span class="text-danger">*</span></label>
                <select class="form-control" id="subclass">
                    <option>Please select</option>
                </select>
            </div>



            <p>&nbsp;</p>
            <div class="col-md-12"><b class="text-primary">OWNER DETAILS</b></div>
            <div class="col-md-12">
                <label for="owner">OWNER <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="owner" placeholder="OWNER (Name/Company/Organization)">
            </div>
            <div class="col-md-12">
                <div class="mb-3 mt-3 alert alert-warning">
                    For Sole-proprietorship,
                    <ul class="list-unstyled" style="font-size: small">
                        <li>
                            Name of the owner must be the same as your DTI-Business Name Registration
                        </li>
                    </ul>
                    For Partnership and Corporation,
                    <ul class="list-unstyled" style="font-size: small">
                        <li>
                            Name of the owner must be the same as your SEC Registration
                        </li>
                    </ul>
                    For Cooperative,
                    <ul class="list-unstyled" style="font-size: small">
                        <li>
                            Name of the owner must be the same as your Cooperative Development Authority Registration
                        </li>
                    </ul>
                    For Government Facilities,
                    <ul class="list-unstyled" style="font-size: small">
                        <li>
                            Please refer to your Enabling Act/Board Resolution
                        </li>
                    </ul>
                    
                </div>
            </div>

            <p>&nbsp;</p>
            <div class="col-md-12"><b class="text-primary">Proponent/Owner Contact Details</b></div>
            <div class="col-md-4">
                <label for="prop_mobile">Proponent/Owner Mobile No. <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="prop_mobile" placeholder="Proponent/Owner Mobile No."/>
            </div>
            <div class="col-md-4">
                <label for="prop_landline">Proponent/Owner Landline <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="prop_landline" placeholder="Proponent/Owner Landline"/>
            </div>
            <div class="col-md-4">
                <label for="prop_email">Proponent/Owner Email Address <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="prop_email" placeholder="Proponent/Owner Email Address"/>
            </div>

            <p>&nbsp;</p>
            <div class="col-md-12"><b class="text-primary">Official Mailing Address</b></div>
            <div class="col-md-12">
                <label for="official_mail_address">Official Mailing Address <span class="text-danger">*</span></label>
                <p>
                    <label><input type="checkbox" id="isSameAsFacilityAddress"> Official Mailing address same as Facility Address? If no, please specify complete address</label>
                </p>
                <input type="text" class="form-control" id="official_mail_address" placeholder="Official Mailing Address"/>
            </div>

            
            <p>&nbsp;</p>
            <div class="col-md-12"><b class="text-primary">Approving Authority Details</b></div>
            <div class="col-md-6">
                <label for="approving_authority_pos">Approving Authority Position/Designation <span class="text-danger">*</span></label>
                <select class="form-control" id="approving_authority_pos" >
                    <option>Please select</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="approving_authority_name">Approving Authority Full Name <span class="text-danger">*</span></label>
                <select class="form-control" id="approving_authority_name" >
                    <option>Please select</option>
                </select>
            </div>

            <div class="form-group col-md-12  mt-4 text-right">
                <ul id="submitFormBtns">
                    <li><button class="btn btn-danger" type="button">Cancel</button></li>
                    <li><button class="btn btn-info" type="button">Submit Form</button></li>
                    <li><button class="btn btn-success" type="button">Save as Draft</button></li>
                </ul>
            </div>
        </form>
    </div>
</div>
</section>
<style>
    #submitFormBtns li {
        display: inline;
    }
</style>
