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