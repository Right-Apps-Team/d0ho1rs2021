<script>
    mserv_cap = JSON.parse('{!!addslashes($serv_cap)!!}')
    // console.log(mserv_cap);

    // console.log("token")
    // console.log(document.getElementsByName('_token')[0].value)

    function type_of_fac(selected) {
        const data = ["hospClassif", "forHosp", "ambuDetails", "ancillary", "addOnServe", "ambulSurgCli", "clinicLab", "dialysisClinic", "otherClinicService"];
        data.map((h) => {
            document.getElementsByClassName(h)[0].setAttribute("hidden", "hidden")
        });
        removeOtherServCont();

        selected == '6' ? ifHospital("show") : " ";
        selected == '2' || selected == '7' || selected == '4' || selected == '28' ? clinicServAndLab("show", selected) : " ";
        selected == '17' ? clinicServAndLabAmbu("show", selected) : " ";
        selected == '18' ? clinicServAndLabAmbu("show", selected) : " ";
        selected == '1' ? ifAmbuSurg("show") : " ";
        selected == '5' ? ifHemoClinic("show") : " ";

    }

    function ifHospital(specs) {

        if (specs == "show") {
            const show = ["hospClassif", "forHosp", "ambuDetails"];
            show.map((h) => {
                // console.log(h)
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
        removeAddOnRows()
        renewAddOnSelect("HDS")
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

    function clinicServAndLab(specs, selected) {

        const data = ["otherClinicService", "clinicLab"];
        // console.log(data)
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

            var x = document.createElement("INPUT");
            x.setAttribute("id", result[0].facid);
            x.setAttribute("type", "radio");
            x.setAttribute("value", result[0].facid);
            x.setAttribute("name", "facid");
            x.setAttribute("checked", "checked");
            x.setAttribute("class", "custom-control-input");
            document.getElementById("hgpid6-new").appendChild(x);


            var label = document.createElement("Label");
            label.setAttribute("for", result[0].facid);
            label.setAttribute("class", "custom-control-label");
            label.innerHTML = result[0].facname;

            var newInput = document.getElementById(result[0].facid)
            insertAfter(newInput, label);


        } else {
            var hlevel = [{
                id: "H"
            }, {
                id: "H2"
            }, {
                id: "H3"
            }];
            // console.log(hlevel)

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
                        x.setAttribute("onclick", "show_hosplevel_anx(this.id)");
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

    function removeOtherServCont (){
        var myobj = document.getElementById("otherServCont");
        if (myobj) {
            myobj.remove();
        }

        var newDiv = document.createElement("div");
        newDiv.setAttribute("id", "otherServCont");
        document.getElementById("mainOsc").appendChild(newDiv);
    }

    function getOtherServices(id){

      

        mserv_cap.map((it) => {  
            if(it.hgpid == id){
                         var newDiv = document.createElement("div");
                        newDiv.setAttribute("class", "custom-control custom-radio mr-sm-2");
                        newDiv.setAttribute("id", "otherServe-" + it.facid);
                        document.getElementById("otherServCont").appendChild(newDiv);

                        var x = document.createElement("INPUT");
                        x.setAttribute("id", it.facid);
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

    function show_hosplevel_anx(selected) {
        document.getElementsByClassName("addOnServe")[0].removeAttribute("hidden")
        document.getElementsByClassName("ancillary")[0].removeAttribute("hidden")
        if (selected == "H") {
            document.getElementsByClassName("hl1")[0].removeAttribute("hidden")
            document.getElementsByClassName("hl2")[0].removeAttribute("hidden")
            document.getElementsByClassName("hl3")[0].removeAttribute("hidden")
        } else if (selected == "H2") {
            document.getElementsByClassName("hl1")[0].setAttribute("hidden", "hidden")
            document.getElementsByClassName("hl2")[0].removeAttribute("hidden")
            document.getElementsByClassName("hl3")[0].removeAttribute("hidden")
        } else if (selected == "H3") {
            document.getElementsByClassName("hl1")[0].setAttribute("hidden", "hidden")
            document.getElementsByClassName("hl2")[0].setAttribute("hidden", "hidden")
            document.getElementsByClassName("hl3")[0].removeAttribute("hidden")
        }
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

    function renewAddOnSelect (id){
        removeAddOnRows()
            $('.addOnServ').remove()
            $('#addOnServ').remove()

            var newAddOns = getAddonServices(id);
            // console.log("newAddons");
            // console.log(newAddOns);

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
        return arrAddon[0];
    }

    function getChargesPerAmb() {

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
                    // amount = amount + ((amount < 1) ? {
                    //     {
                    //         ($ambcharges[0] - > amt + $ambcharges[1] - > amt)
                    //     }
                    // } : {
                    //     {
                    //         $ambcharges[0] - > amt
                    //     }
                    // });
                    plate_number[i].placeholder = "Plate Number/Conduction Sticker";
                    ambOwner[i].parentElement.setAttribute('hidden', true);
                    ambOwner[i].value = "";
                }
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