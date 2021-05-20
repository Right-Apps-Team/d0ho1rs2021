<script>
    var mserv_cap = JSON.parse('{!!addslashes($serv_cap)!!}')
    console.log('mserv_cap')
    console.log(mserv_cap)


 // INITIAL ONLY FOR COA
 var servFacArray =JSON.parse('{!!((count($fAddress) > 0) ? $servfac: "")!!}');
 console.log("servFacArray")
       console.log(servFacArray)
 if(servFacArray[0].length > 0){
    var getHGPID = servFacArray[0];
              var dbhgpid = getHGPID[0].hgpid;

              var getFACID = servFacArray[1];
              var theFACID = getFACID[0].facid;
                      
               setTimeout(function(){ 
                 type_of_fac(dbhgpid) //display facilities

                 getFCID(getFACID)
                 getGoAncillary()

                    setTimeout(function(){ 
                         getFCID(getFACID)
                    }, 1000);
               }, 1000);
 }

 function getFCID(arr){
    arr.map((h) => {
                            // console.log(h.facid)
                            var getFacidField = document.getElementById(h.facid);
                            if(getFacidField){
                            document.getElementById(h.facid).checked= true
                            }
                            
                     });
 }

 var appid ='{!!((count($fAddress) > 0) ? $fAddress[0]->appid: "")!!}';
 document.getElementById("appid").value = appid;

 console.log("appid")
 console.log(appid)
 // INITIAL ONLY FOR COA





    function type_of_fac(selected) {
        // console.log("New func")
        const data = ["servCap", "coaAddon"];
        data.map((h) => {
            document.getElementById(h).setAttribute("hidden", "hidden")
        });
        // initila selection from db
        document.getElementById(selected).checked = true;



        document.getElementById('serv_chg').innerHTML = '<tr><td colspan="2">No Services Selected.</td></tr>';
        
        removeOtherServCont()
        removeOtherServContAdd()
        // selected == '16' ? ifSCL("show") : " ";
        getGoAncillary()
        getCapabilities(selected)
        // getChargesPerApplication()

    }

    function getCapabilities(id) {
        const data = ["servCap", "coaAddon"];
        data.map((h) => {
            document.getElementById(h).removeAttribute("hidden")
        });

        // getChargesPerApplication()

        removeOtherServCont()
        // console.log(id)

        mserv_cap.map((it) => {
            if (it.hgpid == id) {
                var newDiv = document.createElement("div");
                newDiv.setAttribute("class", "custom-control custom-radio mr-sm-2");
                newDiv.setAttribute("id", "otherServe-" + it.facid);
                document.getElementById("otherServCont").appendChild(newDiv);

                var x = document.createElement("INPUT");
                x.setAttribute("id", it.facid);
                // x.setAttribute("onclick", "getFacServCharge()");
                x.setAttribute("onclick", "getGoAncillary()");
                x.setAttribute("type", "radio");
                x.setAttribute("value", it.facid);
                x.setAttribute("name", "facid");
                x.setAttribute("class", "custom-control-input");
                document.getElementById("otherServe-" + it.facid).appendChild(x);

                var label = document.createElement("Label");
                label.setAttribute("for", it.facid);
                label.setAttribute("class", "custom-control-label");
                label.innerHTML = it.facname;

                var newInput = document.getElementById(it.facid)
                insertAfter(newInput, label);

            }
        })
    }

    function removeOtherServCont() {
        var myobj = document.getElementById("otherServCont");
        if (myobj) {
            myobj.remove();
        }

        var newDiv = document.createElement("div");
        newDiv.setAttribute("id", "otherServCont");
        document.getElementById("maincap").appendChild(newDiv);
    }

function removeOtherServContAdd() {
        var myobj = document.getElementById("otherServContAdd");
        if (myobj) {
            myobj.remove();
        }

        var newDiv = document.createElement("div");
        newDiv.setAttribute("id", "otherServContAdd");
        document.getElementById("maincapAdd").appendChild(newDiv);
    }

    function insertAfter(referenceNode, newNode) {
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    }

    function getGoAncillary() {
				let arrAddon = [];
				$.ajax({
					url: '{{asset('client1/request/customQuery/getAncillary')}}',
					dataType: "json", 
					method: 'POST',
					data: {_token:$("input[name=_token]").val(),id: $('[name=facid]:checked').val(), selected: $('[name=facid]:checked').val(), from: 6},
					success: function(arr){
                        console.log("arr")
                        console.log(arr)
						// let Anc = document.getElementById('oAnc'), theuseless = {  };
						// let apString = '<div class="row">'
						// if(arr.length > 0) { 
						
                            // console.log(data)
						// 	// apString += '<div class="row">';
						// 	for(let j = 0; j < data.length; j++) {
						// 		apString += '<div class="col-md-3"><div class="custom-control custom-checkbox mr-sm-2">'+
						// 				        '<input type="checkbox" class="custom-control-input" id="'+data[j]['facid']+'" name="addon" value="'+data[j]['facid']+'">'+
						// 				        '<label class="custom-control-label" for="'+data[j]['facid']+'">'+data[j]['facname']+'</label>'+
						// 				    '</div></div>';
						// 	}
						// }
						// apString += '</div>';
						// Anc.innerHTML = apString;
						// processAddon();

                        removeOtherServContAdd()
        // console.log(id)

                            if(arr.length > 0 ){
                                let data = JSON.parse(arr);
                                 data.map((it) => {
                                // if (it.hgpid == id) {
                                    var newDiv = document.createElement("div");
                                    newDiv.setAttribute("class", "custom-control mr-sm-2");
                                    newDiv.setAttribute("id", "otherServe-" + it.facid);
                                    document.getElementById("otherServContAdd").appendChild(newDiv);

                                    var x = document.createElement("INPUT");
                                    x.setAttribute("id", it.facid);
                                    // x.setAttribute("onclick", "getFacServCharge()");
                                    // x.setAttribute("onclick", "getGoAncillary()");
                                    x.setAttribute("type", "checkbox");
                                    x.setAttribute("value", it.facid);
                                    x.setAttribute("name", "facid");
                                    x.setAttribute("class", "custom-control-input");
                                    document.getElementById("otherServe-" + it.facid).appendChild(x);

                                    var label = document.createElement("Label");
                                    label.setAttribute("for", it.facid);
                                    label.setAttribute("class", "custom-control-label");
                                    label.innerHTML = it.facname;

                                    var newInput = document.getElementById(it.facid)
                                    insertAfter(newInput, label);

                                // }
                            })
                        }else{
                            document.getElementById("otherServContAdd").innerHTML = "No add-ons available"
                        }
					}
				});
			}
    function sendRequestRetArr(arr_data, loc, type, bolRet, objFunction) {
			try {
				type = type.toUpperCase();
				var xhttp = new XMLHttpRequest();
				if(bolRet == true) {
					xhttp.onreadystatechange = function() {
					    if (this.readyState == 4 && this.status == 200) {
				    		objFunction.functionProcess(JSON.parse(this.responseText));
					    }
					};
				}
				xhttp.open(type, loc, bolRet);
				if(type != "GET") {
					xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					xhttp.send(arr_data.join('&'));
				} else {
					xhttp.send();
				}
				if(bolRet == false) {
					objFunction.functionProcess(JSON.parse(xhttp.responseText));
					_hasReturned = 1;
				}
			} catch(errMsg) {
				console.log(errMsg);
	    	}
		}
</script>