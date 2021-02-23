$(function() {
    console.log("BASE: ", base_url);
    $("#institution_helper").tooltip()
});
const savePartial = async (e) => {
    console.log("Saving Partial Form");
}
const setOfficialMailAddress = async (e) => {
    
    const isSame = $("#isSameAsFacilityAddress").prop('checked')
    console.log('EYYYY ', isSame) 
    if(isSame) {
        street_number = $("#street_num").val();
        street_name = $("#street_name").val();
        brgy = $("#brgy option:selected" ).text();
        city = $("#city_monicipality option:selected" ).text();
        prov = $("#province option:selected" ).text();
        region = $("#region option:selected" ).text();
        let errMessage = 'Please fill up the following fields: ';
        let isError = false;
        if(street_number) {
            isError = true;
            errMessage = errMessage + 'Street Number';
        }
        if(street_name) {
            if(isError) {
                errMessage = errMessage + ', Street Name';
            }
            else {
                isError = true;
                errMessage = errMessage + ' Street Name';
            }
        }
        if(brgy) {
            if(isError) {
                errMessage = errMessage + ', Barangay';
            }
            else {
                isError = true;
                errMessage = errMessage + ' Barangay';
            }
        }
        if(city) {
            if(isError) {
                errMessage = errMessage + ', City/Municipality';
            }
            else {
                isError = true;
                errMessage = errMessage + ' City/Municipality';
            }
        } 
        if(prov) {
            if(isError) {
                errMessage = errMessage + ', Province';
            }
            else {
                isError = true;
                errMessage = errMessage + ' Province';
            }
        }
        if(region) {
            if(isError) {
                errMessage = errMessage + ', Region';
            }
            else {
                isError = true;
                errMessage = errMessage + ' Region';
            }
        }
        if(isError) {
            $("#official_mail_address").val('')
            $("#isSameAsFacilityAddress").prop('checked', false)
            alert(errMessage);
        }
        else {
            $("#official_mail_address").val(`${street_number} ${street_name} ${brgy} ${city} ${prov} ${region}`)
        }
    }
    else {
        $("#official_mail_address").val('')
    }
    
}
const fetchSubClass = async (e) => {
    const ocid = $("#ocid").val();
    const classid = $("#classid").val();
    if( e.value ) {
        const data = { 'ocid' : ocid, 'classid' : classid }
        callApi('/api/classification/fetch', data, 'POST').then(classification => {
            $("#subclass").empty();
            $("#subclass").append(`<option value=''>Please select</option>`);
            $("#subclass").removeAttr('disabled');
            classification.data.map(c => {
                $("#subclass").append(`<option value='${c.classid}'>${c.classname}</option>`);
            })
            $("#subclass").selectpicker('refresh')
        })
    }
    else {
        $("#subclass").addAttr('disabled')
    }
}
const fetchClassification = async (e) => {
    const ocid = $("#ocid").val();
    console.log('EYYY, ', ocid);
    if( e.value ) {
        const data = { 'ocid' : ocid }
        callApi('/api/classification/fetch', data, 'POST').then(classification => {
            $("#classification").empty();
            $("#classification").append(`<option value=''>Please select</option>`);
            $("#classification").removeAttr('disabled');
            classification.data.map(c => {
                $("#classification").append(`<option value='${c.classid}'>${c.classname}</option>`);
            })
            $("#classification").selectpicker('refresh')
        })
    }
    else {
        $("#classification").addAttr('disabled')
    }
}
const fetchBaranggay = async (e) => {
    const cmid = $("#city_monicipality").val();
    console.log('EYYY, ', cmid);
    if( e.value ) {
        const data = { 'cmid' : cmid }
        callApi('/api/barangay/fetch', data, 'POST').then(barangay => {
            $("#brgy").empty();
            $("#brgy").append(`<option value=''>Please select</option>`);
            $("#brgy").removeAttr('disabled');
            barangay.data.map(c => {
                $("#brgy").append(`<option value='${barangay.brgyid}'>${c.brgyname}</option>`);
            })
            $("#brgy").selectpicker('refresh')
        }).catch(err => {
            console.log(err);
        })
    }
    else {
        $("#brgy").addAttr('disabled')
    }
}
const fetchMonicipality = async (e) => {
    const provid = $("#province").val();
    console.log('EYYY, ', provid);
    if( e.value ) {
        const data = { 'provid' : provid }
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
    }
    else {
        $("#city_monicipality").addAttr('disabled')
    }
}
const fetchProvince = async (e) => {
    const rgnid = $("#" + e.id).val() //.text()
    console.log('EYYY, ', rgnid);
    if( e.value ) {
        const data = { 'rgnid' : e.value }
        callApi('/api/province/fetch', data, 'POST').then(provinces => {
            console.log(provinces.data);
            $("#province").empty();
            $("#province").append(`<option value=''>Please select</option>`);
            $("#province").removeAttr('disabled');
            provinces.data.map(province => {
                $("#province").append(`<option value='${province.provid}'>${province.provname}</option>`);
            })
            $("#province").selectpicker('refresh')

        }).catch(err => {
            console.log(err);
        })
    }
    else {
        $("#province").addAttr('disabled')
    }
}
const checkFacilityName = async (e) => {
    console.log('EYYY, ', e.value);
    if( e.value ) {
        callApi('/api/application/validate-name', {
            name: e.value
        }, 'POST').then(ok => {
            console.log(ok.data.message)
            $("#" + e.id).css('border', '1px solid green');
            $("#" + e.id + "_feedback").removeClass('text-danger');
            $("#" + e.id + "_feedback").addClass('text-success');
            $("#" + e.id + "_feedback").html(ok.data.message);
        }).catch(err => {
            // alert(err.response.data.message)
            $("#" + e.id).css('border', '1px solid red');
            $("#" + e.id + "_feedback").removeClass('text-success');
            $("#" + e.id + "_feedback").addClass('text-danger');
            $("#" + e.id + "_feedback").html(err.response.data.message);
        })
    }
    else {
        $("#" + e.id).css('border', '1px solid red');
        $("#" + e.id + "_feedback").removeClass('text-success');
        $("#" + e.id + "_feedback").addClass('text-danger');
        $("#" + e.id + "_feedback").html('Facility name is required');
    }
    
}
function callApi(url, data, method) {
    const config = {
        method: method,
        url: `${base_url}${url}`,
        headers: { 
          'Content-Type': 'application/json'
        },
        data : data
    };
    return axios(config)
};