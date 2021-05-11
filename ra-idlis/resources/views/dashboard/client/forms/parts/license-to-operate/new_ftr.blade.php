<script>
    mserv_cap = JSON.parse('{!!addslashes($serv_cap)!!}')
    // console.log(mserv_cap)
    var ghgpid = document.getElementsByName('hgpid')
    var curAppid = ""
    var mhfser_id = "LTO"
    var aptid = "IN"
    
      var  ghgpid = document.getElementsByName('hgpid');

      $(document).on('change','#same', function(event){
				if($(this).prop('checked') == true){
					if($("#street_name").val() != null && 
                    $("#cmid option:selected").val() != "" && 
                    $("#provid option:selected").val() != "" && 
                    $("#brgyid option:selected").val() != "" &&
                     $("#rgnid option:selected").val() != ""){

					$('#mailingAddress').val(($('#street_number').val() != "" ? 
                    $('#street_number').val() : "") + " " + 
                    $("#street_name").val() + " " +
                    $("#brgyid option:selected").text().toUpperCase() + " " + 
                    $("#cmid option:selected").text().toUpperCase() + " " +
                    $("#provid option:selected").text().toUpperCase() + " " + 
                     $("#rgnid option:selected").text().toUpperCase());
				
                } else {
						this.checked = false;
    					event.preventDefault();
						alert('Please select facility address first!');
					}
				} else {
					$('#mailingAddress').val('');
				}
			});
    function offMailDup (){
      var box =  document.getElementById('same');
      var street_name =  document.getElementById('street_name');
      var cmid =  document.getElementById('city_monicipality');
      var provid =  document.getElementById('province');
      var brgyid =  document.getElementById('brgy');
      var rgnid =  document.getElementById('region');
      var mailingAddress =  document.getElementById('official_mail_address');



            if(box.checked){
                if( street_name.value == "" && 
                cmid.value == "" && 
                provid.value == "" && 
                brgyid.value == "" && 
                rgnid.value == "" ){
                

                    box.checked = false;
                                event.preventDefault();
                                alert('Please select facility address first!');

                }else{
                    // console.log(street_name.value)
                    // console.log(cmid.value)
                    // console.log(provid.value)
                    // console.log(brgyid.value)
                    // console.log(rgnid.value) 

                    mailingAddress.value = street_name.value + " " +
                    cmid.options[cmid.selectedIndex].text + " " +
                    provid.options[provid.selectedIndex].text + " " +
                    brgyid.options[brgyid.selectedIndex].text + " " +
                    rgnid.options[rgnid.selectedIndex].text + " " ;

                }
            
            }else{
                mailingAddress.value = " "
            
            }


    }
   
    function type_of_fac(selected) {
        const data = ["hospClassif", "forHosp", "ambuDetails", "ancillary", "addOnServe", "ambulSurgCli", "clinicLab", "dialysisClinic", "otherClinicService"];
        data.map((h) => {
            document.getElementsByClassName(h)[0].setAttribute("hidden", "hidden")
        });
        
        removeAmbuRows()
        deselectOpts('anxsel')
        deselectOpts('facid')

        document.getElementById('serv_chg').innerHTML = '<tr><td colspan="2">No Services Selected.</td></tr>';
        removeOtherServCont();

        selected == '6' ? ifHospital("show") : " ";
        selected == '2' || selected == '7' || selected == '4' || selected == '28' ? clinicServAndLab("show", selected) : " ";
        selected == '17' ? clinicServAndLabAmbu("show", selected) : " ";
        selected == '18' ? clinicServAndLabAmbu("show", selected) : " ";
        selected == '1' ? ifAmbuSurg("show") : " ";
        selected == '5' ? ifHemoClinic("show") : " ";

        getChargesPerApplication()
      
    }


    function getChargesPerApplication() {
        let sArr = ['_token=' + document.getElementsByName('_token')[0].value, 'appid=' + curAppid, 'aptid=' + document.getElementById('aptid').value, 'hfser_id=' + mhfser_id],
            ghgpid = document.getElementsByName('hgpid');
        if (ghgpid != null || ghgpid != undefined) {
            for (let i = 0; i < ghgpid.length; i++) {
                if (ghgpid[i].checked) {
                    sArr.push('hgpid[]=' + ghgpid[i].value);
                }
            }
        }
        sendRequestRetArr(sArr, "{{asset('client1/request/customQuery/getChargesPerApplication')}}", "POST", true, {
            functionProcess: function(arr) {
                
                let not_serv_chg = document.getElementById('not_serv_chg');
                if (not_serv_chg != undefined || not_serv_chg != null) {
                    if (arr.length > 0) {
                        not_serv_chg.innerHTML = '';
                        for (let i = 0; i < arr.length; i++) {
                            not_serv_chg.innerHTML += '<tr><td>' + arr[i]['chg_desc'] + '</td><td>&#8369;&nbsp;<span>' + (parseInt(arr[i]['amt'])).toFixed(2) + '</span></td></tr>';
                        }
                    } else {
                        not_serv_chg.innerHTML = '<tr><td colspan="2">Chosen facility has no Registration fee Required.</td></tr>';
                    }
                }
            }
        });

    

    }
    function getaddonsValues(){
     var addons =   document.getElementsByName('addOnServ');
     var getAdd = [];

        for(var i = 0 ; i < addons.length; i++){
            getAdd.push(addons[i].value);
        }
        // console.log("getAdd")
        // console.log(getAdd)
       return getAdd

    }

    function deselectOpts (name){
        
                var ele;
        
                if(name == 'anxsel'){
                 ele=document.getElementsByClassName(name);  
                }else if(name == 'facid'){
                    ele=document.getElementsByName(name);
                }

                // console.log("checked")
                for(var i=0; i<ele.length; i++){  
                    if(ele[i].type=='radio') { 
                        ele[i].checked=false;
                    }
                    if(ele[i].checked){
                        // console.log(ele[i].value)
                        ele[i].checked=false;
                    }
                   
                      
                }  
    }

    function getFacServCharge (val = null){
        var addons= [];
        // console.log("val")
        // console.log(val)
        if(val == 2){
            // console.log("Received Add ons")
             addons = getaddonsValues();
            // console.log(addons)
        }

     var facids = getCheckedValue('facid') 
     var anxsel = getCheckedValue('anxsel') 
    
   
        
        var arrCol = facids;
        var arrCol2 = anxsel;

        let serv_chg = document.getElementById('serv_chg');
				if(arrCol.length > 0){
					let thisFacid = [], appendToPayment = ['groupThis'], hospitalFaci = ['H','H2','H3'];
					let sArr = ['_token='+document.getElementsByName('_token')[0].value, 'appid='+curAppid, 'hfser_id='+mhfser_id, 'aptid='+aptid];
					if(Array.isArray(arrCol)) {
						for(let i = 0; i < arrCol.length; i++) {
					  		sArr.push('facid[]='+arrCol[i]); 
					  		thisFacid.push(arrCol[i]);
						} 
					}

                    if(arrCol2.length > 0){
                            if(Array.isArray(arrCol2)) {
                                for(let i = 0; i < arrCol2.length; i++) {
                                    sArr.push('facid[]='+arrCol2[i]); 
                                    thisFacid.push(arrCol2[i]);
                                } 
                            }
                    }

                    if(addons.length > 0){
                        if(Array.isArray(addons)) {
                                for(let i = 0; i < addons.length; i++) {
                                    sArr.push('facid[]='+addons[i]); 
                                    thisFacid.push(addons[i]);
                                } 
                            }
                    }

                   
                

					if(!document.getElementById('6').checked){
						for(let j = 0; j < thisFacid.length; j++) {
							if($.inArray(thisFacid[j], hospitalFaci) < 0){
								if(document.getElementsByName('ambtyp').length){
									for(let k = 0; k < document.getElementsByName('ambtyp').length; k++) {
										document.getElementsByName('ambtyp')[k].value = "";
									} 
								}
							}
						} 
					}

                  
                    // console.log("thisFacid")
                    // console.log(thisFacid)
					sendRequestRetArr(sArr, "{{asset('client1/request/customQuery/getServiceCharge')}}", "POST", true, {
						functionProcess: function(arr) {

                           
                            // const distinctArr = [...new Set(arr.map(x => x.facname))];

                            // console.log("fees")
                            // console.log(arr)

                            const distinctArr = Array.from(new Set(arr.map(s => s.facname))).map(facname => {
                                return {
                                    facname : facname,
                                    amt: arr.find(s => 
                                    s.facname===facname).amt
                                }
                            })


                         
							if(serv_chg != undefined || serv_chg != null) {
								if(distinctArr.length > 0) {
									serv_chg.innerHTML = '';
									for(let i = 0; i < distinctArr.length; i++) {
                                        
										serv_chg.innerHTML += '<tr><td>'+distinctArr[i]['facname']+'</td><td>&#8369;&nbsp;<span>'+(parseInt(distinctArr[i]['amt'])).toFixed(2)+'</span></td></tr>';
									
                                    }
								} else {
									serv_chg.innerHTML = '<tr><td colspan="2">No Services selected.</td></tr>';
								}
							}
						}
					});

				} else {
					serv_chg.innerHTML = '<tr><td colspan="2">No Payment Necessary.</td></tr>';
				}
    }

    function getCheckedValue( groupName ) {
        var radios;
        if(groupName == "anxsel"){
             radios = document.getElementsByClassName( groupName );
        }else{
             radios = document.getElementsByName( groupName );
        }
        

        var rad = []
        for( i = 0; i < radios.length; i++ ) {
            if( radios[i].checked ) {
                rad.push(radios[i].value);
                
            }
        }
        return rad;
    }
   
    function ifHospital(specs) {

        if (specs == "show") {
            const show = ["hospClassif", "forHosp", "ambuDetails"];
            show.map((h) => {
                
                document.getElementsByClassName(h)[0].removeAttribute("hidden")
            });
        } else {
            const hide = ["hospClassif", "forHosp", "ambuDetails", "ancillary", "addOnServe"];
            hide.map((h) => {
                document.getElementsByClassName(h)[0].setAttribute("hidden", "hidden")
            });
        }

    }

    function ifAmbuSurg(specs) {
        const data = ["ambulSurgCli", "ambuDetails", "clinicLab"];
        if (specs == "show") {
            data.map((h) => {
                document.getElementsByClassName(h)[0].removeAttribute("hidden")
            });
        } else {
            data.map((h) => {
                document.getElementsByClassName(h)[0].setAttribute("hidden", "hidden")
            });
        }

    }

    function ifHemoClinic(specs) {
        // const data = ["dialysisClinic", "clinicLab"];
        const data = ["dialysisClinic", "addOnServe", "clinicLab"];
        //place also getting add on service data here
        $('#hgpid5').remove()
        removeAddOnRows()
        renewAddOnSelect("HDS")
        if (specs == "show") {
            data.map((h) => {
                document.getElementsByClassName(h)[0].removeAttribute("hidden")
            });

            var newDiv = document.createElement("div");
           
            newDiv.setAttribute("id", "hgpid5");
            document.getElementById("hgpid5-cont").appendChild(newDiv);

           var result = mserv_cap.filter(function(v) {
                return v.hgpid == 5;
            })

            result.map((it) => {
                var x = document.createElement("INPUT");
                x.setAttribute("id", it.facid);
                x.setAttribute("type", "radio");
                x.setAttribute("value", it.facid);
                x.setAttribute("name", "facid");
                x.setAttribute("onclick", "getFacServCharge()");
                // x.setAttribute("checked", "checked");
                x.setAttribute("class", "custom-control-input");
                document.getElementById("hgpid5").appendChild(x);


                var label = document.createElement("Label");
                label.setAttribute("for", it.facid);
                label.setAttribute("class", "custom-control-label");
                label.innerHTML = it.facname;

                var newInput = document.getElementById(it.facid)
                insertAfter(newInput, label);
            })
        } else {
            data.map((h) => {
                document.getElementsByClassName(h)[0].setAttribute("hidden", "hidden")
            });
        }

    }

    function clinicServAndLab(specs, selected) {

        const data = ["otherClinicService", "clinicLab"];
       
        if (specs == "show") {
            data.map((h) => {
                document.getElementsByClassName(h)[0].removeAttribute("hidden")

            });
        } else {
            data.map((h) => {
                document.getElementsByClassName(h)[0].setAttribute("hidden", "hidden")
            });
        }


        getOtherServices(selected)
    }

    function clinicServAndLabAmbu(specs, selected) {
        const data = ["otherClinicService", "clinicLab", "ambuDetails"];
        if (specs == "show") {
            data.map((h) => {

                document.getElementsByClassName(h)[0].removeAttribute("hidden")

            });
        } else {
            data.map((h) => {
                document.getElementsByClassName(h)[0].setAttribute("hidden", "hidden")
            });
        }

        getOtherServices(selected)
    }


    function insertAfter(referenceNode, newNode) {
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    }

    function sel_hosp_class(selected) {
        var myobj = document.getElementById("hgpid6-cont");
        if (myobj) {
            myobj.remove();
        }

        document.getElementsByClassName("showifHospital-class")[0].removeAttribute("hidden");
        var e = document.getElementById("funcid");
        var funcid = e.value;
        var result = [];
        var radio = " ";

        // delete hgpid6-cont first here

        var newDiv = document.createElement("div");
        newDiv.setAttribute("id", "hgpid6-cont");
        document.getElementById("hgpid6").appendChild(newDiv);


        if (selected == 2) {

            var newDiv = document.createElement("div");
            newDiv.setAttribute("class", "custom-control custom-radio mr-sm-2");
            newDiv.setAttribute("id", "hgpid6-new");
            document.getElementById("hgpid6-cont").appendChild(newDiv);

            result = mserv_cap.filter(function(v) {
                return v.hgpid == 6 && v.forSpecialty == 1;
            })
            result.map((it) => {
                var x = document.createElement("INPUT");
                x.setAttribute("id", it.facid);
                x.setAttribute("type", "radio");
                x.setAttribute("value", it.facid);
                x.setAttribute("name", "facid");
                x.setAttribute("onclick", "getFacServCharge()");
                // x.setAttribute("checked", "checked");
                x.setAttribute("class", "custom-control-input");
                document.getElementById("hgpid6-new").appendChild(x);


                var label = document.createElement("Label");
                label.setAttribute("for", it.facid);
                label.setAttribute("class", "custom-control-label");
                label.innerHTML = it.facname;

                var newInput = document.getElementById(it.facid)
                insertAfter(newInput, label);
            })

        } else {
            var hlevel = [{
                id: "H"
            }, {
                id: "H2"
            }, {
                id: "H3"
            }];
          

            mserv_cap.map((it) => {
                hlevel.map((hl) => {
                    if (it.facid == hl.id) {
                        var newDiv = document.createElement("div");
                        newDiv.setAttribute("class", "custom-control custom-radio mr-sm-2");
                        newDiv.setAttribute("id", "hgpid6-" + hl.id);
                        document.getElementById("hgpid6-cont").appendChild(newDiv);

                        var x = document.createElement("INPUT");
                        x.setAttribute("id", it.facid);
                        x.setAttribute("type", "radio");
                        x.setAttribute("value", it.facid);
                        x.setAttribute("name", "facid");
                        x.setAttribute("onclick", "getAncillary(this.id, 6)");
                        // x.setAttribute("onclick", "show_hosplevel_anx(this.id)");
                        x.setAttribute("class", "custom-control-input");
                        document.getElementById("hgpid6-" + hl.id).appendChild(x);


                        var label = document.createElement("Label");
                        label.setAttribute("for", it.facid);
                        label.setAttribute("class", "custom-control-label");
                        label.innerHTML = it.facname;

                        var newInput = document.getElementById(it.facid)
                        insertAfter(newInput, label);
                    }
                })
            })

        }


    }

    function removeOtherServCont() {
        var myobj = document.getElementById("otherServCont");
        if (myobj) {
            myobj.remove();
        }

        var newDiv = document.createElement("div");
        newDiv.setAttribute("id", "otherServCont");
        document.getElementById("mainOsc").appendChild(newDiv);
    }

    function getOtherServices(id) {
       


        mserv_cap.map((it) => {
            if (it.hgpid == id) {
                var newDiv = document.createElement("div");
                newDiv.setAttribute("class", "custom-control custom-radio mr-sm-2");
                newDiv.setAttribute("id", "otherServe-" + it.facid);
                document.getElementById("otherServCont").appendChild(newDiv);

                var x = document.createElement("INPUT");
                x.setAttribute("id", it.facid);
                x.setAttribute("onclick", "getFacServCharge()");
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

    function show_hosplevel_anx(selected, ancData, hgpid) {
       
  
        getFacServCharge()
    //  var ancData = getAncillary();
  
   
     
        document.getElementsByClassName("addOnServe")[0].removeAttribute("hidden")
        document.getElementsByClassName("ancillary")[0].removeAttribute("hidden")
        if (selected == "H") {
            document.getElementsByClassName("hl1")[0].removeAttribute("hidden")
            getJospLevelServices(ancData, "H", hgpid);
            document.getElementsByClassName("hl2")[0].removeAttribute("hidden")
            getJospLevelServices(ancData, "H2", hgpid);
            document.getElementsByClassName("hl3")[0].removeAttribute("hidden")
            getJospLevelServices(ancData, "H3", hgpid);
           
            // getJospLevelServices(ancData);
        } else if (selected == "H2") {
            document.getElementsByClassName("hl1")[0].setAttribute("hidden", "hidden")
            document.getElementsByClassName("hl2")[0].removeAttribute("hidden")
            getJospLevelServices(ancData, "H2", hgpid);
            document.getElementsByClassName("hl3")[0].removeAttribute("hidden")
            getJospLevelServices(ancData, "H3", hgpid);
            // getJospLevelServices(ancData);
        } else if (selected == "H3") {
            document.getElementsByClassName("hl1")[0].setAttribute("hidden", "hidden")
            document.getElementsByClassName("hl2")[0].setAttribute("hidden", "hidden")
            document.getElementsByClassName("hl3")[0].removeAttribute("hidden")
            getJospLevelServices(ancData, "H3", hgpid);
            // getJospLevelServices(ancData);
        }
    }

    function getJospLevelServices (arr,selected, hgpid){
        

        var ar0 = arr[0].filter(function(v) {//get the services of hospital type
                return v.hgpid == 6;
        })

        var ar1 = arr[1].filter(function(v) {
                return v.facid == selected;//get the Id of selected hospital level
        })
        
        var h1Facid = arr[1].filter(function(v) {//get the Id of hospital level1
                return v.facid == "H";
        })



        const duplicats = ar0.reduce((a, e) => {//find the Dupluicates of services groupname or remove the single group name
          a[e.grphrz_name] = ++a[e.grphrz_name] || 0;
         return a;
        }, {});

        var filtDups = ar0.filter(e => duplicats[e.grphrz_name])//get only the objects with duplicates
      
        var filtDupsArr = []

        var servs = filtDups.filter(function(v) {//filter the duplicates base on selected level
                filtDupsArr.push(v.grphrz_name) //store the groupname of duplicates
                return v.servtype_id == ar1[0].servtype_id;
        })

        filtDupsArr = [...new Set(filtDupsArr)] // jeust get single groupname remove pair of duplicates

     


        var special = ar0.filter(function( o){//remove all duplicates from all services, all single groupname must be stores here
        return filtDupsArr.indexOf(o.grphrz_name) == -1;
        });

       

        var specservs = special.filter(function(v) {
                return v.servtype_id == ar1[0].servtype_id;
        })
     
      

        //  var servs = ar0.filter(function(v) {
        //                 return v.servtype_id == ar1[0].servtype_id;
        //         })

       
       
        var allH1GroupName =[]
        var h1gn = filtDups.filter(function(v) {//filter the duplicates base on selected level
                    if( v.servtype_id == h1Facid[0].servtype_id) { 
                    allH1GroupName.push(v.grphrz_name) //store the groupname of duplicates
                    }
                return v.servtype_id == h1Facid[0].servtype_id;
        })

      

        var h1mt = servs.filter(function( o){
        return allH1GroupName.indexOf(o.grphrz_name) > 0;
        });
        // h1mt = h1mt.filter(function(v) {
        //         return v.servtype_id == ar1[0].servtype_id;
        // })

      

        var h1nmatch = servs.filter(function( o){
        return allH1GroupName.indexOf(o.grphrz_name) == -1;
        });
        // h1nmatch = h1nmatch.filter(function(v) {
        //         return v.servtype_id == ar1[0].servtype_id;
        // })
        

        var sortedh1nmatch = h1nmatch.sort(function (a, b) {
        return a.grphrz_name.localeCompare(b.grphrz_name);
         });

         var sortedh1mt = h1mt.sort(function (a, b) {
             return a.grphrz_name.localeCompare(b.grphrz_name);
         });
         
        //  var sorted = servs.sort(function (a, b) {
        // return b.grphrz_name.localeCompare(b.grphrz_name);
        //  });



        document.getElementById("anxservcont"+selected).innerHTML = " ";
       var  asc =document.getElementById("anxservcont"+selected)

        if(asc != undefined || asc != null) {
            sortedh1mt.map((l1) => {
            createAnxRadio(l1,selected)
            })
         

          
            sortedh1nmatch.map((l2) => {
            createAnxRadio(l2,selected)
            })

           

           
        }

        if(specservs.length > 0){
            specservs.map((l1) => {
                createAnxCheckBox (l1,selected)
             })
            
        }

      
    }

    function createAnxRadio (l1, selected){
        var newDiv = document.createElement("div");
                newDiv.setAttribute("id", "asc-" + l1.facid);
                newDiv.setAttribute("class", "row custom-control custom-radio mr-sm-2");
                document.getElementById("anxservcont"+selected).appendChild(newDiv);

                var x = document.createElement("INPUT");
                x.setAttribute("id", l1.facid);
                x.setAttribute("onclick", "getFacServCharge()");
                x.setAttribute("type", "radio");
                x.setAttribute("value", l1.facid);
                x.setAttribute("name", l1.grphrz_name);
                x.setAttribute("class", "custom-control-input anxsel");
                document.getElementById("asc-" + l1.facid).appendChild(x);

                var label = document.createElement("Label");
                label.setAttribute("for", l1.facid);
                label.setAttribute("class", "custom-control-label");
                label.innerHTML = l1.facname;

                var newInput = document.getElementById(l1.facid)
                insertAfter(newInput, label);
    }

    function createAnxCheckBox (l1,selected){
        var newDiv = document.createElement("div");
                newDiv.setAttribute("id", "asc-" + l1.facid);
                newDiv.setAttribute("class", "row custom-control custom-radio mr-sm-2");
                document.getElementById("anxservcont"+selected).appendChild(newDiv);

                var x = document.createElement("INPUT");
                x.setAttribute("id", l1.facid);
                x.setAttribute("onclick", "getFacServCharge()");
                x.setAttribute("type", "checkbox");
                x.setAttribute("value", l1.facid);
                x.setAttribute("name", l1.grphrz_name);
                x.setAttribute("class", "custom-control-input anxsel");
                document.getElementById("asc-" + l1.facid).appendChild(x);

                var label = document.createElement("Label");
                label.setAttribute("for", l1.facid);
                label.setAttribute("class", "custom-control-label");
                label.innerHTML = l1.facname;

                var newInput = document.getElementById(l1.facid)
                insertAfter(newInput, label);
    }

    function getAncillary(selected, hgpid){
        let sArr = ['_token='+document.getElementsByName('_token')[0].value, 'facid[]=H', 'facid[]=H2', 'facid[]=H3'];
        
        let resp = []
        var nas;
        sendRequestRetArr(sArr, "{{asset('client1/request/customQuery/getGoAncillary')}}", "POST", true, {
					functionProcess: function(arr) {
                       
                        show_hosplevel_anx(selected, arr, hgpid)
                    }
				});

               
                // return resp[0];
       
    }

    document.getElementById("buttonId").addEventListener("click", function(event) {
        event.preventDefault()
        var itm = document.getElementById("tr_amb");
        var cln = itm.cloneNode(true);
        cln.removeAttribute("id");
        cln.setAttribute("class", "tr_amb");
        document.getElementById("body_amb").appendChild(cln);
    });

    document.getElementById("buttonIdAos").addEventListener("click", function(event) {
        event.preventDefault()
        var itm = document.getElementById("tr_addOn");
        var cln = itm.cloneNode(true);
        cln.removeAttribute("id");
        cln.setAttribute("class", "tr_addOn");
        document.getElementById("body_addOn").appendChild(cln);
    });


    function preventDef() {
        event.preventDefault()
    }

    window.addEventListener('change', function(e) {
        if (e.target.id == 'ambtyp') {
            getChargesPerAmb();
        }

        if (e.target.name == 'facid') {
            // for updates just loop the data from database evrytime user changes fcid
            if (e.target.value == 'HDS') {
                document.getElementsByClassName("addOnServe")[0].removeAttribute("hidden")
            }

            renewAddOnSelect(e.target.value)
        }
    });

    function renewAddOnSelect(id) {
        removeAddOnRows()
        $('.addOnServ').remove()
        $('#addOnServ').remove()

        var newAddOns = getAddonServices(id);
       

        var newSel = document.createElement("select");
        newSel.setAttribute("class", "form-control");
        newSel.setAttribute("id", "addOnServ");
        newSel.setAttribute("name", "addOnServ");
        document.getElementById("aoselCont").appendChild(newSel);


        newAddOns.map((h) => {
            var opt = document.createElement("option");
            opt.value = h.facid;
            opt.textContent = h.facname;
            document.getElementById("addOnServ").appendChild(opt);


        });
    }

    function removeAddOnRows() {
        var aosObject = document.getElementsByClassName("tr_addOn");
        if (aosObject) {
            $('.tr_addOn').remove();
        }

    }

    function removeAmbuRows() {
        
        let serv_chg_not = document.getElementById('serv_chg_not');
            serv_chg_not.innerHTML = "";

        var amObject = document.getElementsByClassName("tr_amb");
        if (amObject) {
            $('.tr_amb').remove();
        }

    }


    function getAddonServices(theId) {
        let arrAddon = [];
        if (typeof(theId) !== 'undefined' && theId.length) {
            // let arrAddon = [];
            $.ajax({
						url: '{{asset('client1/request/customQuery/getAncillary')}}',
						dataType: "json", 
	    				async: false,
						method: 'POST',
						data: {_token:$("input[name=_token]").val(),id: theId, selected : theId, from: 1},
						success: function(a){
							arrAddon.push(JSON.parse(a));
                            
						}
					});
        }

        // console.log("arrAddon")
        // console.log(arrAddon)
        return arrAddon[0];
    }

    function getChargesPerAmb() {
        var ambT1Ch = parseFloat(('{!!$ambcharges[0]->amt!!}'));
        var ambT2Ch = parseFloat(('{!!$ambcharges[1]->amt!!}'));
        // let sArr = ['_token=' + document.getElementsByName('_token')[0].value, 'appid=' + curAppid],
        theuseless = [],
            ambtyp = document.getElementsByName('ambtyp'),
            plate_number = document.getElementsByName('plate_number'),
            ambOwner = document.getElementsByName('ambOwner'),
            amount = 0;
        if (ambtyp != null || ambtyp != undefined) {
            for (let i = 0; i < ambtyp.length; i++) {

                if (ambtyp[i].value == '1') {
                    plate_number[i].placeholder = "Number of Ambulance";
                    ambOwner[i].parentElement.removeAttribute('hidden');
                }
                if (ambtyp[i].value == '2') {
                    amount = amount + ((amount < 1) ? ambT1Ch + ambT2Ch :ambT1Ch );
                    plate_number[i].placeholder = "Plate Number/Conduction Sticker";
                    ambOwner[i].parentElement.setAttribute('hidden', true);
                    ambOwner[i].value = "";           
                }
            }
            if(amount > 0 ){
            let serv_chg_not = document.getElementById('serv_chg_not');
            serv_chg_not.innerHTML = "";
                   
            serv_chg_not.innerHTML += '<tr><td>' + "Ambulance Charge" + '</td><td>&#8369;&nbsp;<span>' + (parseInt(amount)).toFixed(2) + '</span></td></tr>'
        }
            // sArr.push('ambamt=' + amount);
        }

        


        // sendRequestRetArr(sArr, "{{asset('client1/request/customQuery/getChargesPerAmb')}}", "POST", true, {
        //     functionProcess: function(arr) {
        //         let serv_chg_not = document.getElementById('serv_chg_not');
        //         serv_chg_not.innerHTML = "";
        //         if (arr.length > 0) {
        //             for (let i = 0; i < arr.length; i++) {
        //                 serv_chg_not.innerHTML += '<tr><td>' + arr[i]['chg_desc'] + '</td><td>&#8369;&nbsp;<span>' + (parseInt(arr[i]['amt'])).toFixed(2) + '</span></td></tr>'
        //             }
        //         }
        //     }
        // });
    }

  
    

</script>