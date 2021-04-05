<section class="container-fluid">
<div class="card">
    <div class="card-header">
        <p class="lead text-center text-danger">Please note: Red asterisk (*) is a required field and may be encountered throughout the system </p>
    </div>
    <div class="card-body">
        <form class="row">
            <input type="hidden" name="uid" id="uid" value="{{$user->uid}}"/>
            <input type="hidden" name="appid" id="appid" value="{{ isset($appdata->appid) ? $appdata->appid : '' }}" />
            
            <!-- Application Details -->
            @include('dashboard.client.forms.parts.application-details')

            <!-- Facility Address -->
            @include('dashboard.client.forms.parts.facility-address')

            <!-- Facility Contact Details -->
            @include('dashboard.client.forms.parts.facility-contact-details')

            <!-- Classfication -->
            @include('dashboard.client.forms.parts.classification')

            <!-- Service Capabilities -->
            @include('dashboard.client.forms.parts.service-capabilities')

            <!-- Owner Details -->
            @include('dashboard.client.forms.parts.owner-details')

            <!-- Owner Contact Details -->
            @include('dashboard.client.forms.parts.proponent-owner-contact-details')

            <!-- Official Mailing Address -->
            @include('dashboard.client.forms.parts.official-mailing-address')

            <!-- Approving Authority Details -->
            @include('dashboard.client.forms.parts.approving-authority-details')

                <!-- PTC Type of Consturction -->
                @include('dashboard.client.forms.parts.permit-to-construct.type-of-construction')

                <!-- PTC Type of Health Facility -->
                @include('dashboard.client.forms.parts.permit-to-construct.type-of-health-facility')
            
                <!-- PTC Type of Service Capabilities -->
                @include('dashboard.client.forms.parts.permit-to-construct.service-capabilities')

                {{-- PTC Available Add-Ons --}}
                @include('dashboard.client.forms.parts.permit-to-construct.available-add-ons')

                {{-- PTC Option --}}
                @include('dashboard.client.forms.parts.permit-to-construct.options')

                {{-- PTC CON Code --}}
                @include('dashboard.client.forms.parts.permit-to-construct.con-code')

                {{-- PTC Proposed Health Facility Address --}}
                @include('dashboard.client.forms.parts.permit-to-construct.proposed-health-facility-address')

                {{-- PTC Classification According To --}}
                @include('dashboard.client.forms.parts.permit-to-construct.classification-according-to')
           

            <div class="form-group row col-md-12 mt-5">
                <div class="col-lg-3 col-md-3 col-xs-12"></div>
                <div class="col-lg-3 col-md-3 col-xs-12 mb-5">
                    <a class="btn btn-danger btn-block" href="{{URL::to('/client1/apply')}}">
                        <i class="fa fa-times" aria-hidden="true"></i> Cancel
                    </a>
                </div>
                <div class="col-lg-3 col-md-3 col-xs-12 mb-5">
                    <button 
                        class="btn btn-info btn-block" 
                        type="button" 
                        value="submit" 
                        name="submit"
                        data-toggle="modal" 
                        data-target="#confirmSubmitModal"
                    >
                        <i class="fa fa-paper-plane" aria-hidden="true"></i>
                        Submit Form
                    </button>
                </div>
                <div class="col-lg-3 col-md-3 col-xs-12 mb-5">
                    <button class="btn btn-success btn-block" type="button" onClick="savePartial(this)">
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