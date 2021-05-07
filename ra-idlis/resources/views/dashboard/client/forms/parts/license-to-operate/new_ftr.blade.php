<script>
    mserv_cap = JSON.parse('{!!addslashes($serv_cap)!!}')
    console.log(mserv_cap);

    function type_of_fac(selected) {
        const data = ["hospClassif", "forHosp", "ambuDetails",  "ancillary", "addOnServe", "ambulSurgCli", "clinicLab", "dialysisClinic","otherClinicService"];
        data.map((h) => {
                document.getElementsByClassName(h)[0].setAttribute("hidden", "hidden")
            });

        selected == '6' ? ifHospital("show") : " ";
        selected == '2' || selected == '7'|| selected == '4'|| selected == '28' ? clinicServAndLab("show", selected) : " ";
        selected == '17' ? clinicServAndLabAmbu("show", selected) : " ";
        selected == '18' ? clinicServAndLabAmbu("show", selected) : " ";
        selected == '1' ? ifAmbuSurg("show") : " ";
        selected == '5' ? ifHemoClinic("show") : " ";

    }

    function ifHospital(specs) {

        if (specs == "show") {
            const show = ["hospClassif", "forHosp", "ambuDetails"];
            show.map((h) => {
                console.log(h)
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
        const data = ["dialysisClinic", "addOnServe", "clinicLab"];
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
        console.log(data)
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
            console.log(hlevel)

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
</script>