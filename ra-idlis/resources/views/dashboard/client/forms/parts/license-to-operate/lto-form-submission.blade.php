<script>
    function getAllInputs() {

        [...document.forms["ltoForm"].getElementsByTagName("input")].map(input => {
            console.log(input.name)
            console.log(input.value)
        })
    }

    const savePartialLto = async (e) => {
    console.log("Saving Partial Form LTO");
    const appid         = searchParams.get("appid");
    const types         = $("input[name='type[]']");
    const locations     = $("input[name='location[]'");
    const population    = $("input[name='population[]'");
    const con_catch     = [];

    const facilitynames     = $("input[name='facilitynames[]']");
    const loc               = $("input[name='locations[]']");
    const bedcapacities     = $("input[name='bedcapacities[]']");
    const cat_hos           = $("select[name='cat_hos[]']");
    const license           = $("input[name='license[]']");
    const validity          = $("input[name='validity[]']");
    const date_operation    = $("input[name='date_operation[]']");
    const remarks           = $("textarea[name='remarks[]']");

    const con_hospital  = [];
    for(let i  = 0; i < facilitynames.length; i++ ) {
        const con_hosp_data = {
            appid:          appid,
            facilityname:   facilitynames[i].value,
            location1:      loc[i].value,
            cat_hos:        cat_hos[i].value,
            noofbed1:       bedcapacities[i].value,
            license:        license[i].value,
            validity:       validity[i].value,
            date_operation: date_operation[i].value,
            remarks:        remarks[i].value
        }
        con_hospital.push(con_hosp_data)
    }
    console.log(con_hospital);

    for(let i  = 0; i < types.length; i++ ) {
        const con_catch_data = {
            appid:          appid,
            type:           types[i].value,
            location:       locations[i].value,
            population:     population[i].value,
            isfrombackend:  null
        }
        con_catch.push(con_catch_data)
    }


    const facid = $('input[name="facid"]:checked').val();    
    const data = {
        appid:                  appid,
        hfser_id:               $('#typeOfApplication').val(),
        facilityname:           $('#facility_name').val(),
        rgnid:                  $('#region').val(),
        provid:                 $('#province').val(),
        cmid:                   $('#city_monicipality').val(),
        brgyid:                 $('#brgy').val(),
        street_number:          $('#street_num').val(),
        street_name:            $('#street_name').val(),
        zipcode:                $('#zip').val(),
        contact:                $('#fac_mobile_number').val(),
        areacode:               `["${$('#areacode').val()}", "${$('#faxareacode').val()}", "${$('#prop_landline_areacode').val()}"]`,
        landline:               $('#landline').val(),
        faxnumber:              $('#faxNumber').val(),
        email:                  $('#fac_email_address').val(),
        uid:                    $("#uid").val(),
        cap_inv:                $('#cap_inv').val(),
        lot_area:               $('#lot_area').val(),
        noofbed:                $('#noofbed').val(),
        ocid:                   $('#ocid').val(),
        classid:                $('#classification').val(),
        subClassid:             $('#subclass').val(),
        facmode:                $('#facmode').val(),
        funcid:                 $('#funcid').val(),
        facid:                  facid,
        owner:                  $('#owner').val(),
        ownerMobile:            $('#prop_mobile').val(),
        ownerLandline:          $('#prop_landline').val(),
        ownerEmail:             $('#prop_email').val(),
        mailingAddress:         $('#official_mail_address').val(),
        approvingauthoritypos:  $('#approving_authority_pos').val(),
        approvingauthority:     $('#approving_authority_name').val(),
        hfep_funded:            ($("#hfep_funded").is(":checked") ? 0 : null),
        draft:                  1,
        con_catch:              con_catch,
        con_hospital:           con_hospital,
        hgpid:                  $('input[name="hgpid"]:checked').val(),
    }
    console.log(data)
    // callApi('/api/application/save', data, 'POST').then(d => {
        // const id = d.data.id;
        // alert('Information now saved');
       // // window.location.replace(`${base_url}/client/dashboard/new-application?appid=${id}`);
    // }
    // ).then(error => {
        // console.log(error);
    // })
}
</script>