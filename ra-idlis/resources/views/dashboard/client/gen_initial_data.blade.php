<script>
  
    if ('{!!isset($fAddress)&&(count($fAddress) > 0)!!}') {
        var mserv_cap = JSON.parse('{!!addslashes($serv_cap)!!}')
        var areacode = JSON.parse('{!!((count($fAddress) > 0) ? $fAddress[0]->areacode: "")!!}');

        if (areacode.length > 0) {

            var arc = areacode[0];
            var farc = areacode[1];
            var proparc = areacode[2];
            var arcode = document.getElementById('areacode');
            arcode.value = arc

            var farcode = document.getElementById('faxareacode');
            farcode.value = farc

            var propcode = document.getElementById('prop_landline_areacode');
            propcode.value = proparc
        }

        var ocid ='{!!((count($fAddress) > 0) ? $fAddress[0]->ocid: "")!!}';
        var subclassid ='{!!((count($fAddress) > 0) ? $fAddress[0]->subClassid: "")!!}';
        var classid ='{!!((count($fAddress) > 0) ? $fAddress[0]->classid: "")!!}';
        var facmode ='{!!((count($fAddress) > 0) ? $fAddress[0]->facmode: "")!!}';
        var funcid ='{!!((count($fAddress) > 0) ? $fAddress[0]->funcid: "")!!}';
        var owner ='{!!((count($fAddress) > 0) ? $fAddress[0]->owner: "")!!}';
        var ownerMobile ='{!!((count($fAddress) > 0) ? $fAddress[0]->ownerMobile: "")!!}';
        var ownerLandline ='{!!((count($fAddress) > 0) ? $fAddress[0]->ownerLandline: "")!!}';
        var ownerEmail ='{!!((count($fAddress) > 0) ? $fAddress[0]->ownerEmail: "")!!}';
        var mailingAddress ='{!!((count($fAddress) > 0) ? $fAddress[0]->mailingAddress: "")!!}';
        var approvingauthoritypos ='{!!((count($fAddress) > 0) ? $fAddress[0]->approvingauthoritypos: "")!!}';
        var approvingauthority ='{!!((count($fAddress) > 0) ? $fAddress[0]->approvingauthority: "")!!}';

        document.getElementsByName('funcid')[0].value = funcid;
        document.getElementById("facmode").value = facmode;
        document.getElementById("owner").value = owner;
        document.getElementById("prop_mobile").value = ownerMobile;
        document.getElementById("prop_landline").value = ownerLandline;
        document.getElementById("prop_email").value = ownerEmail;
        document.getElementById("official_mail_address").value = mailingAddress;
        document.getElementById("approving_authority_pos").value = approvingauthoritypos;
        document.getElementById("approving_authority_name").value = approvingauthority;


        var ocidInpt = document.getElementById("ocid");
        ocidInpt.value = ocid;
        ocidInpt.setAttribute("disabled", "disabled")

        console.log("subclassid")
        console.log(subclassid)

        const data = { 'ocid' : ocid, 'classid' : classid }
        if(subclassid != ""){
        $.ajax({
						url: '{{asset('api/classification/fetch')}}',
						dataType: "json", 
	    				async: false,
						method: 'POST',
						data: data,
						success: function(a){
                          
                            var result = a.filter(function(v) {
                                    return v.classid == subclassid;
                            })
                            document.getElementById("subclass").placeholder = result[0].classname;
                            
						}
					});
        }
    }
    function checkEmailValidity(email) 
{
 if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))
  {
    return (true)
  }
    return (false)
}

function checkNumberlValidity(number) 
{
 if (/^(09|\+639)\d{9}$/.test(number))
  {
    return (true)
  }
    return (false)
}

    
</script>