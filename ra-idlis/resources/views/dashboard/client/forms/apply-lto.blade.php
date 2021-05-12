<div class="row">
<div class="col-md-8">
    <?php $_aptid = "IN";
    $_aptdesc = "Initial New";
    $_dispSubmit = false;
    $_dispData = "Submit Details";
    if (isset($aptid)) {
        if ($aptid == "IC") {
            $_aptid = $aptid;
            $_aptdesc = "Change Request";
            $_dispSubmit = true;
            $_dispData = "Update Details";
        }
        if ($aptid == "R") {
            $_aptid = $aptid;
            $_aptdesc = "Renewal";
            $_dispSubmit = true;
            $_dispData = "Update Details";
        }
    } ?>
    {{-- {{dd([$_aptdesc,$_dispSubmit,$_dispData,$aptid,$_aptdesc])}} --}}
    {{csrf_field()}}
    <section class="container-fluid">
        <div class="card">
            <div class="card-header">
                <p class="lead text-center text-danger">Please note: Red asterisk (*) is a required field and may be encountered throughout the system </p>
            </div>
            <div class="card-body">
                <form class="row" id="ltoForm">
                    <input type="hidden" name="uid" id="uid" value="{{$user->uid}}" />
                    <input type="hidden" name="appid" id="appid"  />
                    <!-- <input type="hidden" name="appid" id="appid" value="{{ isset($appdata->appid) ? $appdata->appid : '' }}" /> -->

                    <!-- Application Details -->
                    @include('dashboard.client.forms.parts.application-details')

                    <!-- Facility Address -->
                    @include('dashboard.client.forms.parts.facility-address')

                    <!-- Facility Contact Details -->
                    @include('dashboard.client.forms.parts.facility-contact-details')

                    <!-- Classfication -->
                    @include('dashboard.client.forms.parts.classification')

                    <!-- Service Capabilities -->
                    <!-- @include('dashboard.client.forms.parts.service-capabilities') -->

                    <!-- Owner Details -->
                    @include('dashboard.client.forms.parts.owner-details')

                    <!-- Owner Contact Details -->
                    @include('dashboard.client.forms.parts.proponent-owner-contact-details')

                    <!-- Official Mailing Address -->
                    @include('dashboard.client.forms.parts.official-mailing-address')

                    <!-- Approving Authority Details -->
                    @include('dashboard.client.forms.parts.approving-authority-details')

                    {{-- LTO Health Facility Address --}}
                    <!-- @include('dashboard.client.forms.parts.license-to-operate.health-facility-address') -->

                    {{-- LTO PTC Code --}}
                    @include('dashboard.client.forms.parts.license-to-operate.ptc-code')

                    {{-- LTO Type of Facility --}}
                    @include('dashboard.client.forms.parts.license-to-operate.type-of-facility')

                    {{-- LTO Class of Hospitals --}}
                    @include('dashboard.client.forms.parts.license-to-operate.classification-of-hospital')

                    {{-- LTO For Hospital --}}
                    @include('dashboard.client.forms.parts.license-to-operate.for-hospital')

                    {{-- LTO Ancillary/Clinical Services --}}
                    @include('dashboard.client.forms.parts.license-to-operate.ancillary-clinical-services')

                    {{-- LTO For Dialysis Clinic --}}
                    @include('dashboard.client.forms.parts.license-to-operate.for-dialysis-clinic')
                    
                    {{-- LTO Add-On Services --}}
                    @include('dashboard.client.forms.parts.license-to-operate.add-on-services')

                    {{-- LTO For Ambulatory Surgical Clinic --}}
                    @include('dashboard.client.forms.parts.license-to-operate.for-ambulatory-surgical-clinic')

                    {{-- LTO For Ambulance Details --}}
                    @include('dashboard.client.forms.parts.license-to-operate.for-ambulance-details')

                  

                    {{-- LTO Other Clinical Service(s) --}}
                    @include('dashboard.client.forms.parts.license-to-operate.other-clinic-services')
                   

                    {{-- LTO For Clinical Laboratory --}}
                    @include('dashboard.client.forms.parts.license-to-operate.for-clinical-laboratory')

                    {{-- LTO Classification According To --}}
                    @include('dashboard.client.forms.parts.license-to-operate.classification-according-to')

                    {{-- LTO Authorized Bed Capacity --}}
                    @include('dashboard.client.forms.parts.license-to-operate.authorized-bed-capacity')

                    {{-- LTO For Pharmacy --}}
                    @include('dashboard.client.forms.parts.license-to-operate.for-pharmacy')


                    <div class="form-group row col-md-12 mt-5">
                        <div class="col-lg-3 col-md-3 col-xs-12"></div>
                        <div class="col-lg-3 col-md-3 col-xs-12 mb-5">
                            <a class="btn btn-danger btn-block" href="{{URL::to('/client1/apply')}}">
                                <i class="fa fa-times" aria-hidden="true"></i> Cancel
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-3 col-xs-12 mb-5">
                            <button class="btn btn-info btn-block" type="button" value="submit" name="submit" data-toggle="modal" data-target="#confirmSubmitModal">
                                <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                Submit Form
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-3 col-xs-12 mb-5">
                            <button class="btn btn-success btn-block" type="button" onClick="savePartialLto(this)">
                            <!-- onClick="savePartial(this)" -->
                                <i class="fa fa-floppy-o" aria-hidden="true"></i>
                                Save as Draft
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @include('dashboard.client.modal.facilityname-helper')
    </section>

    {{-- payment --}}
    <div class="col-md-4">
        @include('dashboard.client.forms.parts.payment.payment-form')
    </div>
</div>


</div>

<style>
    .feedback {
        width: 100%;
        display: block;
    }

    .custom-selectpicker {
        border: 1px solid #ced4da;
    }

    .region {
        display: none;
    }

    .province {
        display: none;
    }
</style>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

<script src="{{asset('ra-idlis/public/js/forall.js')}}"></script>

@include('dashboard.client.forms.parts.license-to-operate.new_ftr')
@include('dashboard.client.forms.parts.license-to-operate.lto-form-submission')