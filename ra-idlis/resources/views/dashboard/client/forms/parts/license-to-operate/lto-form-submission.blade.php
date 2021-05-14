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

   
    var allFacids = getAllFacids();
    var allambdet = getallAmbDetails();
    var alladdondesc = getAddonDesc();
    console.log("allFacids")
    console.log(allFacids)
    console.log("allambdet")
    console.log(allambdet) 
    console.log("alladdondesc")
    console.log(alladdondesc)

    const facid = $('input[name="facid"]:checked').val();    
    const data = {
        // appid:                  appid,
        appid:                  $('#appid').val(),
        hfser_id:               $('#typeOfApplication').val(),
        facilityname:           $('#facility_name').val(),
        rgnid:                  $('#region').val() == undefined ? '{!!((count($fAddress) > 0) ? $fAddress[0]->rgnid: "")!!}' : $('#region').val(),
        provid:                 $('#province').val() == undefined ? '{!!((count($fAddress) > 0) ? $fAddress[0]->provid: "")!!}' : $('#province').val(),
        cmid:                   $('#city_monicipality').val() == undefined ? '{!!((count($fAddress) > 0) ? $fAddress[0]->cmid: "")!!}' : $('#city_monicipality').val(),
        brgyid:                 $('#brgy').val() == undefined ? '{!!((count($fAddress) > 0) ? $fAddress[0]->brgyid: "")!!}' : $('#brgy').val(),
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
        noofmain:               $('#noofmain').val(),
        noofsatellite:          $('#noofsatellite').val(),
        ocid:                   $('#ocid').val(),
        classid:                $('#classification').val()  == undefined ? '{!!((count($fAddress) > 0) ? $fAddress[0]->classid: "")!!}' : $('#classification').val() ,
        subClassid:             $('#subclass').val()  == "" ||  $('#subclass').val() == undefined ? '{!!((count($fAddress) > 0) ? $fAddress[0]->subClassid: "")!!}' : $('#subclass').val(),
        facmode:                $('#facmode').val(),
        funcid:                 $('#funcid').val(),

        typeamb:                JSON.stringify(allambdet[0]),
        ambtyp:                 JSON.stringify(allambdet[1]),
        plate_number:           JSON.stringify(allambdet[2]),
        ambOwner:               JSON.stringify(allambdet[3]),
        addonDesc:              JSON.stringify(alladdondesc),
        // facid:                  "listfacids",
        facid:                  JSON.stringify(allFacids),
        owner:                  $('#owner').val(),
        ptcCode:                $('#ptcCode').val(),
        ownerMobile:            $('#prop_mobile').val(),
        ownerLandline:          $('#prop_landline').val(),
        ownerEmail:             $('#prop_email').val(),
        mailingAddress:         $('#official_mail_address').val(),
        approvingauthoritypos:  $('#approving_authority_pos').val(),
        approvingauthority:     $('#approving_authority_name').val(),
        // hfep_funded:            ($("#hfep_funded").is(":checked") ? 0 : null),
        hfep_funded:            ($('#hfep').prop('checked') ? 0 : null),
        draft:                  1,
        con_catch:              con_catch,
        con_hospital:           con_hospital,
        hgpid:                  $('input[name="hgpid"]:checked').val(),
    }
    console.log(data)
    if(confirm("Are you sure you want to porceed?")){

        callApi('/api/application/lto/save', data, 'POST').then(d => {
            const id = d.data.id;
            alert('Information now saved');
        // window.location.replace(`${base_url}/client/dashboard/new-application?appid=${id}`);
        }
        ).then(error => {
            console.log(error);
        })

}
}


function getAllFacids (){
    var addons =  getaddonsValues()
    var listAncs = getCheckedValue('anxsel')

    var listfacids = getCheckedValue('facid') 

    let thisFacid = []

    if(listfacids.length > 0){
        if(Array.isArray(listfacids)) {
						for(let i = 0; i < listfacids.length; i++) {
					  		// sArr.push('facid[]='+listfacids[i]); 
                            if(listfacids[i] != ""){
					  		thisFacid.push(listfacids[i]);
                            }
						} 
					}
    }
    if(listAncs.length > 0){
            if(Array.isArray(listAncs)) {
                for(let i = 0; i < listAncs.length; i++) {
                    // sArr.push('facid[]='+listAncs[i]); 
                    if(listAncs[i] != ""){
                    thisFacid.push(listAncs[i]);}
                } 
            }
    }

    if(addons.length > 0){
        if(Array.isArray(addons)) {
                for(let i = 0; i < addons.length; i++) {
                    // sArr.push('facid[]='+addons[i]); 
                    if(addons[i] != ""){
                    thisFacid.push(addons[i]);
                   }
                } 
            }
    }

    return thisFacid
   
}

function getallAmbDetails(){
   var ta = document.getElementsByName('typeamb');
   var at = document.getElementsByName('ambtyp');
   var pn = document.getElementsByName('plate_number');
   var ao = document.getElementsByName('ambOwner');

   var typeamb = [];
   var ambtyp = [];
   var plate_number = [];
   var ambOwner = [];

   for(var i =0 ; i < ta.length ; i++){
       typeamb.push(ta[i].value);
   }
   
   for(var i =0 ; i < at.length ; i++){
    ambtyp.push(at[i].value);
   }

   for(var i =0 ; i < pn.length ; i++){
    plate_number.push(pn[i].value);
   }

   for(var i =0 ; i < ao.length ; i++){
    ambOwner.push(ao[i].value);
   }

   var all = [];

   all.push(typeamb)
   all.push(ambtyp)
   all.push(plate_number)
   all.push(ambOwner)

   return all;


}

function getAddonDesc(){
   var ao = document.getElementsByName('addOnServ');
   var as = document.getElementsByName('aoservtyp');
   var aso = document.getElementsByName('aoservOwner');

    var alladdondesc =[];
   for(var i = 0 ; i < ao.length ; i++){
        const subs = {
            facid: ao[i].value,
            facid_name: ao[i].options[ao[i].selectedIndex].text,
            servtyp: as[i].value,
            servowner: aso[i].value
        }

        alladdondesc.push(subs);
   }

   return alladdondesc
}
</script>