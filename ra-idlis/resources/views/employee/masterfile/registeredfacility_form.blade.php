<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 0px;border: none;">
            <div class="modal-body text-justify">
                <h5 class="modal-title text-center"><strong>Add New Registered Facility</strong></h5>
                <hr>
                <div class="container">
                    <form id="regform">
                        <div class="row">
                            <div class="col-md">
                                <label for="facility_name">Facility Name <span class="text-danger">*</span></label>
                                <input type="text" name="facilityname" class="form-control" placeholder="FACILITY NAME" id="facility_name" onChange="checkFacilityName(this)" required>
                                <small id="facility_name_feedback" class="feedback"></small>
                            </div>
                            <div class="col-md">
                                <label for="facility_name">Facility Type <span class="text-danger">*</span></label>
                                <select name="facilitytype" id="facilitytype" class="form-control">
                                    @foreach( $factype as $ft)
                                    <option value="{{$ft->hgpid}}">{{$ft->hgpdesc}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <br />

                        <div class="row">
                            <div class="col-md">
                                <label for="region">Region<span class="text-danger">*</span></label>
                                <select class="form-control selectpicker show-menu-arrow toRemove" id="region" name="rgnid" required data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" onChange="fetchProvince(this)">
                                    <option value="">Please select</option>
                                    @foreach( $regions as $region)
                                    @if($region->rgnid != 'HFSRB')
                                    <option value="{{$region->rgnid}}">{{$region->rgn_desc}}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md">
                                <label for="province">Province/District <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker show-menu-arrow toRemove" id="province" disabled name="provid" required data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" onChange="fetchMonicipality(this)">
                                    <option value="">Please select</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label for="city_monicipality">City/Municipality <span class="text-danger">*</span></label>
                                <select class="form-control  selectpicker show-menu-arrow toRemove" id="city_monicipality" disabled name="cmid" required data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" onChange="fetchBaranggay(this)">
                                    <option value="">Please select</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label for="brgy">Baranggay<span class="text-danger">*</span></label>
                                <select class="form-control selectpicker show-menu-arrow toRemove" id="brgy" disabled name="brgyid" data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" required>
                                    <option value="">Please select</option>
                                </select>
                            </div>
                        </div>

                        <br />

                        <div class="row">
                            <div class="col-md">
                                <label for="street_num">Street Number </label>
                                <input type="text" class="form-control" id="street_num" name="street_number" placeholder="STREET NUMBER" value='{{((isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->street_number: null)}}'>
                            </div>
                            <div class="col-md">
                                <label for="street_name">Street name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="street_name" name="street_name" placeholder="STREET NAME" value='{{(( isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->street_name: null)}}'>
                            </div>
                            <div class="col-md">
                                <label for="zip">Zip Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="zip" name="zipcode" placeholder="ZIP CODE" value='{{((isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->zipcode: null)}}'>
                            </div>
                        </div>

                        <br />

                        <div class="row">
                            <div class="col-sm">
                                <label for="fac_mobile_number">Facility Mobile No. <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="fac_mobile_number" name="contact" placeholder="FACILITY MOBILE #" value='{{((isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->contact: null)}}'>
                            </div>
                            <div class="col-sm">
                                <label for="fac_email_address">Facility Email Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fac_email_address" name="email" placeholder="EMAIL" value='{{(isset($fAddress) && (count($fAddress) > 0) ? $fAddress[0]->email: null)}}'>
                            </div>
                        </div>

                        <br />
                        <label for="facility_landline">Facility Landline <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-sm"> <input type="number" placeholder="Area code" name="areacode" id="areacode" class="form-control" /></div>
                            <div class="col-sm"> <input type="number" name="landline" id="landline" placeholder="FACILITY LANDLINE" class="form-control" value='{{(isset($fAddress) && (count($fAddress) > 0) ? $fAddress[0]->landline: null)}}' /></div>
                        </div>
                        <br />
                        <label for="fax">Fax Number <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-sm"> <input type="number" placeholder="Area code" name="faxareacode" id="faxareacode" class="form-control" /></div>
                            <div class="col-sm"> <input type="number" name="faxNumber" id="faxNumber" placeholder="FACILITY FAX #" class="form-control" value='{{(isset($fAddress) && (count($fAddress) > 0) ? $fAddress[0]->faxNumber: null)}}' /></div>
                        </div>

                        <br />

                        <div class="row">
                            <div class="col-sm">
                                <label for="ownership">Ownership <span class="text-danger">*</span></label>

                                <select class="form-control selectpicker show-menu-arrow" id="ocid" name="ocid" data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" onChange="fetchClassification(this)">
                                    <option>Please select</option>
                                    <option value="G">Government</option>
                                    <option value="P">Private</option>
                                </select>
                            </div>
                            <div class="col-sm">
                                <label for="classification">Classification <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker show-menu-arrow toRemove" id="classification" value='{{((isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->classid: null)}}' disabled name="classid" data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" onChange="fetchSubClass(this)">
                                    <option>Please select</option>
                                </select>
                            </div>
                            <div class="col-sm">
                                <label for="subclass">Sub Classification <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker show-menu-arrow toRemove" id="subclass" disabled value='{{((isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->subClassid: null)}}' name="subClassid" data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5">
                                    <option>Please select</option>
                                </select>
                            </div>
                        </div>


                        <br />

                        <div class="row">
                            <div class="col-sm">
                                <label for="facmode">Institutional Character <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker show-menu-arrow" id="facmode" name="facmode" data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5">
                                    <option>Please select</option>
                                    <option value="2">Free Standing</option>
                                    <option value="4">Institution Based (Hospital)</option>
                                    <option value="5">Institution Based (Non-Hospital)</option>
                                </select>
                            </div>
                            <div class="col-sm">
                                <label for="funcid">Function <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker show-menu-arrow" data-funcid="main" id="funcid" name="funcid" data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5">
                                    <option>Please select</option>
                                    <option value="1">General</option>
                                    <option value="2">Specialty</option>
                                    <option value="3">Not Applicable</option>
                                </select>
                            </div>
                        </div>

                        <br />

                        <div class="row">
                            <div class="col-sm">
                                <label for="owner">OWNER <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="owner" name="owner" placeholder="OWNER (Name/Company/Organization)">
                            </div>
                            <div class="col-sm">
                                <label for="prop_email">Proponent/Owner Email Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="prop_email" name="ownerEmail" placeholder="Proponent/Owner Email Address" />
                            </div>
                        </div>


                        <br />


                        <label for="prop_landline">Proponent/Owner Contact Info <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-sm"> <input type="text" class="form-control" id="prop_mobile" name="ownerMobile" placeholder="Mobile No." /></div>
                            <div class="col-sm"> <input type="text" class="form-control" id="prop_landline_areacode" name="ownerLandlienAreacode" placeholder="Area Code" /></div>
                            <div class="col-sm"> <input type="text" class="form-control" id="prop_landline" name="ownerLandline" placeholder=" Landline" /></div>
                        </div>

                        <br />


                        <label for="official_mail_address">Official Mailing Address <span class="text-danger">*</span></label>
                        <p>
                            <label>
                                <input name="mailingAddress" type="checkbox" id="isSameAsFacilityAddress" value="1" onChange="{{((isset($fAddress) && count($fAddress) > 0) ? 'setOfficialMailAddressUp(this)': 'setOfficialMailAddress(this)')}}"> Official Mailing address same as Facility Address? If no, please specify complete address</label>
                        </p>
                        <input name="official_mail_address" type="text" class="form-control" id="official_mail_address" placeholder="Official Mailing Address" />
                        <br />
                        <div class="row">
                            <div class="col-sm">
                                <label for="approving_authority_pos">Approving Authority Position/Designation <span class="text-danger">*</span></label>
                                <select class="form-control" id="approving_authority_pos" name="approvingauthoritypos">
                                    <option value="">Please select</option>
                                    <option value="President">President</option>
                                    <option value="Owner">Owner</option>
                                    <option value="Head of Facility">Head of Facility</option>
                                </select>
                            </div>
                            <div class="col-sm">
                                <label for="approving_authority_name">Approving Authority Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="approving_authority_name" name="approvingauthority" placeholder="Approving Authority Full name" />

                            </div>
                        </div>
                        
                        {{-- LTO Type of Facility --}}
                        @include('dashboard.client.forms.parts.license-to-operate.type-of-facility')

                        {{-- LTO Class of Hospitals --}}
                        @include('dashboard.client.forms.parts.license-to-operate.classification-of-hospital')

                        {{-- LTO For Ambulatory Surgical Clinic --}}
                        @include('dashboard.client.forms.parts.license-to-operate.for-ambulatory-surgical-clinic')

                        {{-- LTO Other Clinical Service(s) --}}
                        @include('dashboard.client.forms.parts.license-to-operate.other-clinic-services')

                        
                        {{-- LTO For Dialysis Clinic --}}
                        @include('dashboard.client.forms.parts.license-to-operate.for-dialysis-clinic')

                        <button id="mainbtn" class="btn btn-primary p-2 m-1" onClick="submtForm('update')">Submit</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var mserv_cap = JSON.parse('{!!addslashes($serv_cap)!!}')
    function callApi(url, data, method) {
        const config = {
            method: method,
            url: `${base_url}${url}`,
            headers: {
                'Content-Type': 'application/json'
            },
            data: data
        };
        return axios(config)
    };
    $("#regform").submit(function(e) {
        e.preventDefault();
    });

    const submtForm = async (e) => {

        const data = {



            hfser_id: $('#typeOfApplication').val(),
            facilityname: $('#facility_name').val(),
            rgnid: $('#region').val(),
            provid: $('#province').val(),
            cmid: $('#city_monicipality').val(),
            brgyid: $('#brgy').val(),
            street_number: $('#street_num').val(),
            street_name: $('#street_name').val(),
            zipcode: $('#zip').val(),
            contact: $('#fac_mobile_number').val(),
            areacode: `["${$('#areacode').val()}", "${$('#faxareacode').val()}", "${$('#prop_landline_areacode').val()}"]`,
            landline: $('#landline').val(),
            faxnumber: $('#faxNumber').val(),
            email: $('#fac_email_address').val(),



            ocid: $('#ocid').val(),
            classid: $('#classification').val(),
            subClassid: $('#subclass').val(),
            facmode: $('#facmode').val(),
            funcid: $('#funcid').val(),
            // facid:                  facid,
            owner: $('#owner').val(),
            ownerMobile: $('#prop_mobile').val(),
            ownerLandline: $('#prop_landline').val(),
            ownerEmail: $('#prop_email').val(),
            mailingAddress: $('#official_mail_address').val(),
            approvingauthoritypos: $('#approving_authority_pos').val(),
            approvingauthority: $('#approving_authority_name').val(),
            // hfep_funded: ($('#hfep').prop('checked') ? 0 : null),
            hfep_funded: "",
            facid: $('#facilitytype').val(),

        }
        console.log(data)

        callApi('/api/registered/facility/save', data, 'POST').then(d => {

            document.getElementById("regform").reset();
            alert("Success");
            // alert(d.data.mssg);

        }).then(error => {
            console.log(error);
        })


    };
    const checkFacilityName = async (e) => {
        const facilityname = $('#facility_name').val()
        console.log('EYYY, ', facilityname);
        if (facilityname) {
            callApi('/api/application/validate-name/registered', {
                name: facilityname
            }, 'POST').then(ok => {
                console.log(ok.data.message)
                localStorage.setItem('facilityname', facilityname)
                $("#facility_name").css('border', '1px solid green');
                $("#facility_name_feedback").removeClass('text-danger');
                $("#facility_name_feedback").addClass('text-success');
                $("#facility_name_feedback").html(ok.data.message);
                var btn = document.getElementById("mainbtn");
                btn.innerHTML = "Submit";
                btn.removeAttribute("disabled", "disabled")

            }).catch(err => {
                // alert(err.response.data.message)
                $("#facility_name").css('border', '1px solid red');
                $("#facility_name_feedback").removeClass('text-success');
                $("#facility_name_feedback").addClass('text-danger');
                $("#facility_name_feedback").html(err.response.data.message);

                var btn = document.getElementById("mainbtn");
                btn.innerHTML = "Check Facility Name";
                btn.setAttribute("disabled", "disabled")


            })
        } else {
            $("#facility_name").css('border', '1px solid red');
            $("#facility_name_feedback").removeClass('text-success');
            $("#facility_name_feedback").addClass('text-danger');
            $("#facility_name_feedback").html('Facility name is required');
        }

    }

    const fetchProvince = async (e) => {
        const rgnid = $("#region").val() //.text()
        console.log('EYYY, ', rgnid);
        if (rgnid) {
            const data = {
                'rgnid': rgnid
            }
            callApi('/api/province/fetch', data, 'POST').then(provinces => {
                localStorage.setItem('rgnid', rgnid)
                const localProvID = parseInt(localStorage.getItem('provid'))
                // console.log(localRgnID);
                $("#province").empty();
                $("#province").append(`<option value=''>Please select</option>`);
                $("#province").removeAttr('disabled');
                provinces.data.map(province => {

                    $("#province").append(`<option value='${province.provid}' selected="selected">${province.provname}</option>`);
                })
                // console.log(localProvID)
                // $("#province").val(localProvID)
                $("#province").selectpicker('refresh')
            }).catch(err => {
                console.log(err);
            })
        } else {
            $("#province").addAttr('disabled')
        }
    }

    const fetchMonicipality = async (e) => {
        const provid = $("#province").val();
        console.log('EYYY, ', provid);
        if (provid) {
            const data = {
                'provid': provid
            }
            callApi('/api/municipality/fetch', data, 'POST').then(city => {
                $("#city_monicipality").empty();
                $("#city_monicipality").append(`<option value=''>Please select</option>`);
                $("#city_monicipality").removeAttr('disabled');
                city.data.map(c => {
                    $("#city_monicipality").append(`<option value='${c.cmid}'>${c.cmname}</option>`);
                })
                $("#city_monicipality").selectpicker('refresh')
            }).catch(err => {
                console.log(err);
            });
        } else {
            $("#city_monicipality").addAttr('disabled')
        }
    }

    const fetchBaranggay = async (e) => {
        const cmid = $("#city_monicipality").val();
        console.log("Received brgy")
        console.log('EYYY, ', cmid);
        if (cmid) {
            const data = {
                'cmid': cmid
            }
            callApi('/api/barangay/fetch', data, 'POST').then(barangay => {
                $("#brgy").empty();
                $("#brgy").append(`<option value=''>Please select</option>`);
                $("#brgy").removeAttr('disabled');
                barangay.data.map(c => {

                    $("#brgy").append(`<option value='${c.brgyid}'>${c.brgyname}</option>`);
                })
                $("#brgy").selectpicker('refresh')
            }).catch(err => {
                console.log(err);
            })
        } else {
            $("#brgy").addAttr('disabled')
        }
    }

    const fetchSubClass = async (e) => {
        const ocid = $("#ocid").val();
        const classid = $("#classification").val();
        if (e.value) {
            const data = {
                'ocid': ocid,
                'classid': classid
            }
            callApi('/api/classification/fetch', data, 'POST').then(classification => {
                $("#subclass").empty();
                $("#subclass").append(`<option value=''>Please select</option>`);
                $("#subclass").removeAttr('disabled');
                classification.data.map(c => {
                    $("#subclass").append(`<option value='${c.classid}'>${c.classname}</option>`);
                })
                $("#subclass").selectpicker('refresh')
            })
        } else {
            $("#subclass").addAttr('disabled')
        }
    }
    const fetchClassification = async (e) => {
        const ocid = $("#ocid").val();
        console.log('EYYY, ', ocid);
        if (ocid) {
            const data = {
                'ocid': ocid
            }
            callApi('/api/classification/fetch', data, 'POST').then(classification => {
                $("#classification").empty();
                $("#classification").append(`<option value=''>Please select</option>`);
                $("#classification").removeAttr('disabled');
                classification.data.map(c => {
                    $("#classification").append(`<option value='${c.classid}'>${c.classname}</option>`);
                })
                $("#classification").selectpicker('refresh')
            })



        } else {
            $("#classification").addAttr('disabled')
        }


    }
    const setOfficialMailAddress = async (e) => {

        const isSame = $("#isSameAsFacilityAddress").prop('checked')
        console.log('EYYYY ', isSame)
        if (isSame) {
            street_number = $("#street_num").val();
            street_name = $("#street_name").val();
            brgy = $("#brgy option:selected").text();
            city = $("#city_monicipality option:selected").text();
            prov = $("#province option:selected").text();
            region = $("#region option:selected").text();
            let errMessage = 'Please fill up the following fields: ';
            let isError = false;
            // if (!street_number) {
            //     isError = true;
            //     errMessage = errMessage + 'Street Number';
            // }
            if (!street_name) {
                if (isError) {
                    errMessage = errMessage + ', Street Name';
                } else {
                    isError = true;
                    errMessage = errMessage + ' Street Name';
                }
            }
            if (!brgy) {
                if (isError) {
                    errMessage = errMessage + ', Barangay';
                } else {
                    isError = true;
                    errMessage = errMessage + ' Barangay';
                }
            }
            if (!city) {
                if (isError) {
                    errMessage = errMessage + ', City/Municipality';
                } else {
                    isError = true;
                    errMessage = errMessage + ' City/Municipality';
                }
            }
            if (!prov) {
                if (isError) {
                    errMessage = errMessage + ', Province';
                } else {
                    isError = true;
                    errMessage = errMessage + ' Province';
                }
            }
            if (!region) {
                if (isError) {
                    errMessage = errMessage + ', Region';
                } else {
                    isError = true;
                    errMessage = errMessage + ' Region';
                }
            }
            if (isError) {
                $("#official_mail_address").val('')
                $("#isSameAsFacilityAddress").prop('checked', false)
                alert(errMessage);
            } else {
                $("#official_mail_address").val(`${street_number} ${street_name} ${brgy} ${city} ${prov} ${region}`)
            }
        } else {
            $("#official_mail_address").val('')
        }

    }

    function type_of_fac(selected) {
        removeOtherServCont();
        console.log(selected)
        selected == '6' ? ifHospital("show") :ifHospital("hide");
        selected == '1' ? ifAmbuSurg("show") : ifAmbuSurg("hide");
        selected == '2' || selected == '7'|| selected == '17'|| selected == '18' || selected == '4' || selected == '28' || selected == '5' ? clinicServAndLab("show", selected) :clinicServAndLab("hide", selected);
        // selected == '5' ? ifHemoClinic("show") :ifHemoClinic("hide");
    }

     function ifHospital(specs) {

        if (specs == "show") {
            
            const show = ["hospClassif"];
            // const show = ["hospClassif", "forHosp", "ambuDetails", "addOnServe"];
            // const show = ["hospClassif", "forHosp", "ambuDetails"]; //7-24/2021
            show.map((h) => {
                
                document.getElementsByClassName(h)[0].removeAttribute("hidden")
            });
            
            
        } else {
            const hide = ["hospClassif"];
            // const hide = ["hospClassif", "forHosp", "ambuDetails", "ancillary", "addOnServe"];
            hide.map((h) => {
                document.getElementsByClassName(h)[0].setAttribute("hidden", "hidden")
            });
        }

    }

    
    function ifHemoClinic(specs) {

        // const data = ["dialysisClinic", "clinicLab"];
        const data = ["dialysisClinic"];
        // const data = ["dialysisClinic", "addOnServe", "clinicLab"];
        //place also getting add on service data here
        $('#hgpid5').remove()
        // removeAddOnRows()
        // renewAddOnSelect("HDS")
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
            console.log(result)

            result.map((it) => {
                var x = document.createElement("INPUT");
                // x.setAttribute("id", it.facid);
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

    const data = ["otherClinicService"];

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
               x.setAttribute("class", "custom-control-input os_list");
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


    function insertAfter(referenceNode, newNode) {
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    }

    function ifAmbuSurg(specs) {
        const data = ["ambulSurgCli"];
        // const data = ["ambulSurgCli", "ambuDetails", "clinicLab"];
        if (specs == "show") {
            ASCfacilities ()
            data.map((h) => {
                document.getElementsByClassName(h)[0].removeAttribute("hidden")
            });
        } else {
            data.map((h) => {
                document.getElementsByClassName(h)[0].setAttribute("hidden", "hidden")
            });
        }

    }

    function ASCfacilities (){
        $('#hgpid1').remove()

            var newDiv = document.createElement("div");
            newDiv.setAttribute("id", "hgpid1");
            newDiv.setAttribute("class", "custom-control");
            document.getElementById("forAmb").appendChild(newDiv);

            var result = mserv_cap.filter(function(v) {
                return v.hgpid == 1;
            })

            result.map((it) => {
                var newDiv = document.createElement("div");
                newDiv.setAttribute("class", "row custom-control  mr-sm-2");
                // newDiv.setAttribute("class", "col-md-4");
                newDiv.setAttribute("id", "hgpid1-" + it.facid);
                document.getElementById("hgpid1").appendChild(newDiv);

                var x = document.createElement("INPUT");
                x.setAttribute("id", it.facid);
                x.setAttribute("type", "checkbox");
                x.setAttribute("value", it.facid);
                x.setAttribute("name", "facid");
                x.setAttribute("onclick", "getFacServCharge()");
                x.setAttribute("class", "custom-control-input exAddRenew");
                document.getElementById("hgpid1-" + it.facid).appendChild(x);

                var label = document.createElement("Label");
                label.setAttribute("for", it.facid);
                label.setAttribute("class", "custom-control-label");
                label.innerHTML = it.facname;

                var newInput = document.getElementById(it.facid)
                insertAfter(newInput, label);

            
        })


    }
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js" integrity="sha512-bZS47S7sPOxkjU/4Bt0zrhEtWx0y0CRkhEp8IckzK+ltifIIE9EMIMTuT/mEzoIMewUINruDBIR/jJnbguonqQ==" crossorigin="anonymous"></script>