$(function() {
    console.log("BASE: ", base_url);

});
const checkFacilityName = async (e) => {
    console.log('EYYY, ', e.value);
    if( e.value ) {
        callApi('/api/application/validate-name', {
            name: e.value
        }, 'POST').then(ok => {
            console.log(ok.data.message)
            $("#" + e.id).css('border', '1px solid green');
            $("#" + e.id + "_feedback").addClass('text-success');
            $("#" + e.id + "_feedback").html(ok.data.message);
        }).catch(err => {
            // alert(err.response.data.message)
            $("#" + e.id).css('border', '1px solid red');
            $("#" + e.id + "_feedback").addClass('text-danger');
            $("#" + e.id + "_feedback").html(err.response.data.message);
        })
    }
    else {
        $("#" + e.id).css('border', '1px solid red');
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