<?php

use Illuminate\Http\Request;
use App\Http\Middleware\APIMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get(
    '/clients', 
    'Client\Api\ClientApiController@index'
); //->middleware([APIMiddleware::class]);

Route::post(
    '/application/validate-name/',
    'Client\Api\ApplicationApiController@check'
); //->middleware([APIMiddleware::class]);

Route::post(
    '/application/save',
    'Client\Api\ApplicationApiController@save'
); //->middleware([APIMiddleware::class]);

Route::post(
    '/application/fetch',
    'Client\Api\ApplicationApiController@fetch'
); //->middleware([APIMiddleware::class]);

Route::post(
    '/province/fetch/',
    'Client\Api\ProvinceApiController@fetch'
); //->middleware([APIMiddleware::class]);

Route::post(
    '/municipality/fetch/',
    'Client\Api\MunicipalityApiController@fetch'
); //->middleware([APIMiddleware::class]);

Route::post(
    '/barangay/fetch/',
    'Client\Api\BarangayApiController@fetch'
); //->middleware([APIMiddleware::class]);

Route::post(
    '/classification/fetch/',
    'Client\Api\ClassificationApiController@fetch'
); //->middleware([APIMiddleware::class]);

// my changes
Route::get(
    '/activitylogs/fetch/',
    'Client\Api\ActivityLogsApiController@fetch'
);

Route::get(
    '/appassessment/fetch/',
    'Client\Api\AppAssessmentApiController@fetch'
);

Route::get(
    '/formhistory/fetch/',
    'Client\Api\AppFormHistApiController@fetch'
);

Route::get(
    '/forminsp/fetch/',
    'Client\Api\AppFormInspApiController@fetch'
);

// No Primary Key Set
// Route::get(
//     '/form_meta/fetch/',
//     'Client\Api\AppFormMetaApiController@fetch'
// );

Route::get(
    '/form_oopdata/fetch/',
    'Client\Api\AppFormOOPDataApiController@fetch'
);

Route::get(
    '/form_orderofpayment/fetch/',
    'Client\Api\AppFormOrderOfPaymentApiController@fetch'
);

Route::get(
    '/apptype/fetch/',
    'Client\Api\AppTypeApiController@fetch'
);

Route::get(
    '/appupload/fetch/',
    'Client\Api\AppUploadApiController@fetch'
);

Route::get(
    '/asmt2/fetch/',
    'Client\Api\Asmt2ApiController@fetch'
);

Route::get(
    '/asmt2Col/fetch/',
    'Client\Api\Asmt2ColApiController@fetch'
);

Route::get(
    '/asmt2Loc/fetch/',
    'Client\Api\Asmt2LocApiController@fetch'
);

Route::get(
    '/asmt2SDSCA/fetch/',
    'Client\Api\Asmt2SDSCApiController@fetch'
);

Route::get(
    '/asmtH1/fetch/',
    'Client\Api\AsmtH1ApiController@fetch'
);

Route::get(
    '/asmtH2/fetch/',
    'Client\Api\AsmtH2ApiController@fetch'
);

Route::get(
    '/asmtH3/fetch/',
    'Client\Api\AsmtH3ApiController@fetch'
);

Route::get(
    '/asmt_title/fetch/',
    'Client\Api\AsmtTitleApiController@fetch'
);


Route::get(
    '/assessed/fetch/',
    'Client\Api\AssessedApiController@fetch'
);

Route::get(
    '/assessed_ptc/fetch/',
    'Client\Api\AssessedPtcApiController@fetch'
);

Route::get(
    '/assessment/fetch/',
    'Client\Api\AssessmentApiController@fetch'
);

Route::get(
    '/assessment_combined/fetch/',
    'Client\Api\AssessmentCombinedApiController@fetch'
);

Route::get(
    '/assessment_combined_dup/fetch/',
    'Client\Api\AssessmentCombinedDuplicateApiController@fetch'
);

Route::get(
    '/assessment_combined_dup_ptc/fetch/',
    'Client\Api\AssessmentCombinedDuplicatePtcApiController@fetch'
);

Route::get(
    '/assessmentrecommendation/fetch/',
    'Client\Api\AssessmentRecommendationApiController@fetch'
);

Route::get(
    '/assessmentrecommendation_hist/fetch/',
    'Client\Api\AssessmentRecommendationHistoryApiController@fetch'
);

Route::get(
    '/assessment_upload/fetch/',
    'Client\Api\AssessmentUploadApiController@fetch'
);

Route::get(
    '/branch/fetch/',
    'Client\Api\BranchApiController@fetch'
);

Route::get(
    '/cat_assess/fetch/',
    'Client\Api\CatAssessApiController@fetch'
);

Route::get(
    '/category/fetch/',
    'Client\Api\CategoryApiController@fetch'
);

Route::get(
    '/cdrr_attachment/fetch/',
    'Client\Api\CdrrAttachmentApiController@fetch'
);

Route::get(
    '/cdrr_hr_otherattachment/fetch/',
    'Client\Api\CdrrHrOtherAttachmentApiController@fetch'
);

Route::get(
    '/cdrr_hr_personnel/fetch/',
    'Client\Api\CdrrHrPersonnelApiController@fetch'
);

Route::get(
    '/cdrr_hr_receipt/fetch/',
    'Client\Api\CdrrHrReceiptApiController@fetch'
);

Route::get(
    '/cdrr_hr_requirement/fetch/',
    'Client\Api\CdrrHrRequirementApiController@fetch'
);

Route::get(
    '/cdrr_hr_requirement/fetch/',
    'Client\Api\CdrrHrRequirementApiController@fetch'
);

Route::get(
    '/cdrr_hr_xrayServCat/fetch/',
    'Client\Api\CdrrHrXrayListApiController@fetch'
);

Route::get(
    '/cdrrPersonnel/fetch/',
    'Client\Api\CdrrPersonnelApiController@fetch'
);

Route::get(
    '/cdrrReceipt/fetch/',
    'Client\Api\CdrrReceiptApiController@fetch'
);

Route::get(
    '/charge/fetch/',
    'Client\Api\ChargeApiController@fetch'
);

Route::get(
    '/chgApp/fetch/',
    'Client\Api\ChgAppApiController@fetch'
);

Route::get(
    '/chgAppTo/fetch/',
    'Client\Api\ChgApplyToApiController@fetch'
);

Route::get(
    '/chgFaci/fetch/',
    'Client\Api\ChgFaciApiController@fetch'
);

Route::get(
    '/chgFil/fetch/',
    'Client\Api\ChgFilApiController@fetch'
);

Route::get(
    '/chgLoc/fetch/',
    'Client\Api\ChgLocApiController@fetch'
);

Route::get(
    '/committeeTeam/fetch/',
    'Client\Api\CommitteeTeamApiController@fetch'
);

Route::get(
    '/complaintsForm/fetch/',
    'Client\Api\ComplaintsFormApiController@fetch'
);

Route::get(
    '/conCatch/fetch/',
    'Client\Api\CONCatchApiController@fetch'
);

Route::get(
    '/conEvalSave/fetch/',
    'Client\Api\CONEvalSaveApiController@fetch'
);

Route::get(
    '/conEvaluate/fetch/',
    'Client\Api\CONEvaluateApiController@fetch'
);

Route::get(
    '/conHospital/fetch/',
    'Client\Api\CONHospitalApiController@fetch'
);

Route::get(
    '/department/fetch/',
    'Client\Api\DepartmentApiController@fetch'
);

Route::get(
    '/emailNotif/fetch/',
    'Client\Api\EmailNotifyApiController@fetch'
);

Route::get(
    '/facAssessment/fetch/',
    'Client\Api\FacAssessmentApiController@fetch'
);

Route::get(
    '/facAssessment/fetch/',
    'Client\Api\FacAssessmentApiController@fetch'
);

Route::get(
    '/facilityRequirement/fetch/',
    'Client\Api\FacilityRequirementApiController@fetch'
);

Route::get(
    '/facilityType/fetch/',
    'Client\Api\FacilityTypeApiController@fetch'
);

Route::get(
    '/facilityTypUpload/fetch/',
    'Client\Api\FacilityTypUploadApiController@fetch'
);

Route::get(
    '/FACLGroup/fetch/',
    'Client\Api\FACLGroupApiController@fetch'
);

Route::get(
    '/facMode/fetch/',
    'Client\Api\FacModeApiController@fetch'
);

Route::get(
    '/fac_oop/fetch/',
    'Client\Api\FacOOPApiController@fetch'
);

Route::get(
    '/fdaCert/fetch/',
    'Client\Api\FDACertApiController@fetch'
);

Route::get(
    '/fdaChgFil/fetch/',
    'Client\Api\FDACertApiController@fetch'
);

Route::get(
    '/fdaEvaluation/fetch/',
    'Client\Api\FDAEvaluationApiController@fetch'
);

Route::get(
    '/fdaEvaluation_hist/fetch/',
    'Client\Api\FDAEvaluationHistoryApiController@fetch'
);

Route::get(
    '/fdaMonitoring/fetch/',
    'Client\Api\FDAMonitoringApiController@fetch'
);

Route::get(
    '/fdaMonitoringFiles/fetch/',
    'Client\Api\FDAMonitoringFilesApiController@fetch'
);

Route::get(
    '/fdaPharmacyCharges/fetch/',
    'Client\Api\FDAPharmacyChargesApiController@fetch'
);

Route::get(
    '/fdaRange/fetch/',
    'Client\Api\FDARangeApiController@fetch'
);

Route::get(
    '/fdaXrayCat/fetch/',
    'Client\Api\FDAXrayCatApiController@fetch'
);

Route::get(
    '/fdaXrayLoc/fetch/',
    'Client\Api\FDAXrayLocationApiController@fetch'
);

Route::get(
    '/fdaXrayMach/fetch/',
    'Client\Api\FDAXrayMachApiController@fetch'
);

Route::get(
    '/fdaXrayServ/fetch/',
    'Client\Api\FDAXrayServApiController@fetch'
);

Route::get(
    '/forAmbulance/fetch/',
    'Client\Api\ForAmbulanceApiController@fetch'
);

Route::get(
    '/fromMobileInspec/fetch/',
    'Client\Api\FromMobileInspectionApiController@fetch'
);

Route::get(
    '/funCapF/fetch/',
    'Client\Api\FunCapFApiController@fetch'
);