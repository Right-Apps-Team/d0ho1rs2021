<?php

namespace App\Http\Controllers\Client\Api;
use Session;
use App\Http\Controllers\Controller;
use App\Models\ApplicationForm;
use Illuminate\Http\Request;

class ApplicationApiController extends Controller {
    public function check(Request $request) {
        $name = $request->name;
        $applications = ApplicationForm::where('facilityname', $name)->get();
        if(count($applications)) {
            return  response()->json(['message' => 'Facility name no longer available'], 400);
        }
        else {
            return  response()->json(['message' => 'Facility name is safe to use'], 200);
        }
        
    }
    public function save(Request $request) {
        
        $appform = new ApplicationForm;

        $appform->hfser_id = array_key_exists($request->hfser_id) ? $request->hfser_id : null;
        $appform->facilityname = array_key_exists($request->facilityname) ? $request->facilityname : null;
        $appform->rgnid = array_key_exists($request->rgnid) ? $request->rgnid : null;
        $appform->provid = array_key_exists($request->provid) ? $request->provid : null;
        $appform->cmid = array_key_exists($request->cmid) ? $request->cmid : null;
        $appform->brgyid = array_key_exists($request->brgyid) ? $request->brgyid : null;
        $appform->street_number = array_key_exists($request->street_number) ? $request->street_number : null;
        $appform->street_name = array_key_exists($request->street_name) ? $request->street_name : null;
        $appform->zipcode = array_key_exists($request->zipcode) ? $request->zipcode : null;
        $appform->contact = array_key_exists($request->contact) ? $request->contact : null;
        $appform->areacode = array_key_exists($request->areacode) ? $request->areacode : null;
        $appform->landline = array_key_exists($request->landline) ? $request->landline : null;
        $appform->faxNumber = array_key_exists($request->faxNumber) ? $request->faxNumber : null;
        $appform->email = array_key_exists($request->email) ? $request->email : null;
        $appform->uid = array_key_exists($request->uid) ? $request->uid : null;
        $appform->ocid = array_key_exists($request->ocid) ? $request->ocid : null;
        $appform->classid = array_key_exists($request->classid) ? $request->classid : null;
        $appform->subClassid = array_key_exists($request->subClassid) ? $request->subClassid : null;
        $appform->facmode = array_key_exists($request->facmode) ? $request->facmode : null;
        $appform->funcid = array_key_exists($request->funcid) ? $request->funcid : null;
        $appform->owner = array_key_exists($request->owner) ? $request->owner : null;
        $appform->ownerMobile = array_key_exists($request->ownerMobile) ? $request->ownerMobile : null;
        $appform->ownerLandline = array_key_exists($request->ownerLandline) ? $request->ownerLandline : null;
        $appform->ownerEmail = array_key_exists($request->ownerEmail) ? $request->ownerEmail : null;


        $appform->mailingAddress = array_key_exists($request->mailingAddress) ? $request->mailingAddress : null;
        $appform->zipcode = array_key_exists($request->zipcode) ? $request->zipcode : null;
        $appform->zipcode = array_key_exists($request->zipcode) ? $request->zipcode : null;
        $appform->zipcode = array_key_exists($request->zipcode) ? $request->zipcode : null;
        $appform->zipcode = array_key_exists($request->zipcode) ? $request->zipcode : null;

        return response()->json($request);
    }
}