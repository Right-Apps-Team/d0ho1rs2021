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
                <select 
                    class="form-control selectpicker show-menu-arrow" 
                    id="typeOfApplication" 
                    name="hfser_id" 
                    required 
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



            <p>&nbsp;</p>
            <div class="col-md-12"><b class="text-primary">FACILTY ADDRESS</b></div>
            <div class="col-md-3">
                <label for="region">Region <span class="text-danger">*</span></label>
                <select 
                    class="form-control selectpicker show-menu-arrow" 
                    id="region" 
                    name="rgnid" 
                    required 
                    data-live-search="true" 
                    data-style="text-dark form-control custom-selectpicker"
                    data-size="5"
                    onChange="fetchProvince(this)" 
                >
                    <option>Please select</option>
                    @foreach( $regions as $region)
                        <option value="{{$region->rgnid}}" >{{$region->rgn_desc}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="province">Province/District <span class="text-danger">*</span></label>
                <select 
                    class="form-control selectpicker show-menu-arrow" 
                    id="province"
                    disabled 
                    name="provid"
                    required 
                    data-live-search="true" 
                    data-style="text-dark form-control custom-selectpicker"
                    data-size="5"
                    onChange="fetchMonicipality(this)" 
                    >
                    <option>Please select</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="city_monicipality">City/Municipality <span class="text-danger">*</span></label>
                <select 
                    class="form-control  selectpicker show-menu-arrow" 
                    id="city_monicipality" 
                    disabled
                    name="cmid"
                    required 
                    data-live-search="true" 
                    data-style="text-dark form-control custom-selectpicker"
                    data-size="5"
                    onChange="fetchBaranggay(this)" 
                >
                    <option>Please select</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="brgy">Baranggay <span class="text-danger">*</span></label>
                <select 
                    class="form-control selectpicker show-menu-arrow" 
                    id="brgy" 
                    disabled
                    name="brgyid"
                    data-live-search="true" 
                    data-style="text-dark form-control custom-selectpicker"
                    data-size="5"
                    required
                    >
                    <option>Please select</option>
                </select>
            </div>
            <div class="mb-2 col-md-12">&nbsp;</div>
            <div class="col-md-4">
                <label for="street_num">Street Number </label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="street_num" 
                    name="street_number" 
                    placeholder="STREET NUMBER"
                    >
            </div>
            <div class="col-md-4">
                <label for="street_name">Street name <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="street_name"
                    name="street_name" 
                    placeholder="STREET NAME">
            </div>
            <div class="col-md-4">
                <label for="zip">Zip Code <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="zip"
                    name="zipcode" 
                    required
                    placeholder="ZIP CODE">
                <small><span class="text-danger">NOTE: </span>for reference, please follow this <a href="https://www.phlpost.gov.ph/zip-code-search.php" target=
                "_blank">link</a></small>
            </div>
            
            

            <p>&nbsp;</p>
            <div class="col-md-12"><b class="text-primary">FACILTY CONTACT DETAILS</b></div>
            <div class="col-md-3">
                <label for="fac_mobile_number">Facility Mobile No. <span class="text-danger">*</span></label>
                <input 
                    type="number" 
                    class="form-control" 
                    id="fac_mobile_number" 
                    name="contact"
                    placeholder="FACILITY MOBILE #">

            </div>
            <div class="col-md-3">
                <label for="facility_landline">Facility Landline <span class="text-danger">*</span></label>
                <div class='form-group row'>
                    <div class="col-xs-12 col-md-6 col-lg-5">
                        <input 
                            type="number"
                            placeholder="Area code"
                            name="areacode"
                            id="areacode"
                            required
                            class="form-control" />
                    </div>
                    <div class="col-xs-12 col-md-6 col-lg-7">
                        <input 
                            type="number"
                            name="landline" 
                            id="landline"
                            placeholder="FACILITY LANDLINE"
                            required
                            class="form-control" />
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <label for="fax">Fax Number <span class="text-danger">*</span></label>
                <div class="form-group row">
                    <div class="col-xs-12 col-md-6 col-lg-5">
                        <input 
                                type="number"
                                placeholder="Area code"
                                name="faxareacode"
                                id="faxareacode"
                                required
                                class="form-control" />
                    </div>
                    <div class="col-xs-12 col-md-6 col-lg-7">
                        <input 
                            type="number"
                            name="faxNumber" 
                            id="faxNumber"
                            placeholder="FACILITY FAX #"
                            required
                            class="form-control" />
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <label for="fac_email_address">Facility Email Address <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="fac_email_address" name="email" placeholder="EMAIL">
            </div>


            


            <p>&nbsp;</p>
            <div class="col-md-12"><b class="text-primary">CLASSIFICATION ACCORDING TO</b></div>
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
                    <div class="col-md-10">
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
                    <div class="col-md-2">
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


            <p>&nbsp;</p>
            <div class="col-md-12"><b class="text-primary">OWNER DETAILS</b></div>
            <div class="col-md-12">
                <label for="owner">OWNER <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="owner" 
                    name="owner"
                    placeholder="OWNER (Name/Company/Organization)"
                >
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
                <input 
                    type="text" 
                    class="form-control" 
                    id="prop_mobile" 
                    name="ownerMobile"
                    placeholder="Proponent/Owner Mobile No."/>
            </div>
            <div class="col-md-4">
                <label for="prop_landline">Proponent/Owner Landline <span class="text-danger">*</span></label>
                <div class="row">
                    <div class="col-xs-12 col-md-5 col-xs-5">
                        <input 
                            type="text" 
                            class="form-control" 
                            id="prop_landline_areacode" 
                            name="ownerLandlienAreacode"
                            placeholder="Proponent/Owner Landline"/>
                    </div>
                    <div class="col-xs-12 col-md-7 col-xs-7">
                        <input 
                            type="text" 
                            class="form-control" 
                            id="prop_landline" 
                            name="ownerLandline"
                            placeholder="Proponent/Owner Landline"/>
                    </div>
                </div>
                
            </div>
            <div class="col-md-4">
                <label for="prop_email">Proponent/Owner Email Address <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="prop_email" 
                    name="ownerEmail"
                    placeholder="Proponent/Owner Email Address"/>
            </div>

            <p>&nbsp;</p>
            <div class="col-md-12"><b class="text-primary">Official Mailing Address</b></div>
            <div class="col-md-12">
                <label for="official_mail_address">Official Mailing Address <span class="text-danger">*</span></label>
                <p>
                    <label>
                        <input 
                            type="checkbox" 
                            id="isSameAsFacilityAddress" 
                            value="1" 
                            onChange="setOfficialMailAddress(this)"
                        > Official Mailing address same as Facility Address? If no, please specify complete address</label>
                </p>
                <input 
                    type="text" 
                    class="form-control" 
                    id="official_mail_address"
                    placeholder="Official Mailing Address"/>
            </div>

            
            <p>&nbsp;</p>
            <div class="col-md-12"><b class="text-primary">Approving Authority Details</b></div>
            <div class="col-md-6">
                <label for="approving_authority_pos">Approving Authority Position/Designation <span class="text-danger">*</span></label>
                <select class="form-control" id="approving_authority_pos" name="approvingauthoritypos">
                    <option value="">Please select</option>
                    <option value="President">President</option>
                    <option value="Owner">Owner</option>
                    <option value="Head of Facility">Head of Facility</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="approving_authority_name">Approving Authority Full Name <span class="text-danger">*</span></label>
                <select class="form-control" id="approving_authority_name" name="approvingauthority">
                    <option>Please select</option>
                </select>
            </div>

            <div class="form-group row col-md-12 mt-5">
                <div class="col-lg-3 col-md-3 col-xs-12"></div>
                <div class="col-lg-3 col-md-3 col-xs-12 mb-5">
                    <a class="btn btn-danger btn-block" href="{{URL::to('/client1/apply')}}">
                        <i class="fa fa-times" aria-hidden="true"></i> Cancel
                    </a>
                </div>
                <div class="col-lg-3 col-md-3 col-xs-12 mb-5">
                    <button class="btn btn-info btn-block" type="submit" value="submit" name="submit">
                        <i class="fa fa-paper-plane" aria-hidden="true"></i>
                        Submit Form
                    </button>
                </div>
                <div class="col-lg-3 col-md-3 col-xs-12 mb-5">
                    <button class="btn btn-success btn-block" type="button" onClick="savePartial(this)">
                        <i class="fa fa-floppy-o" aria-hidden="true"></i>
                        Save as Draft
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</section>
<style>
    .feedback {
        width: 100%;
        display: block;
    }
    .custom-selectpicker {
        border: 1px solid #ced4da;
    }
</style>

