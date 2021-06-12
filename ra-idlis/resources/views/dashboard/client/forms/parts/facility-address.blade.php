<p>&nbsp;</p>
<div class="col-md-12"><b class="text-primary">FACILTY ADDRESS</b></div>
<div class="col-md-3">
    <input type="hidden" name="aptid" id="aptid" value="IN">
    <input type="hidden" name="tempAppCharge" id="tempAppCharge" >
    <input type="hidden" name="tempAppChargeHgpid" id="tempAppChargeHgpid" >
    <input type="hidden" name="tempAppChargeAmb" id="tempAppChargeAmb" >
    <input type="hidden" name="assignedRgn" id="assignedRgn" >
   
    <label for="region">Region<span class="text-danger">*</span></label>
    @if(isset($fAddress) && count($fAddress) > 0)
    <input class="form-control "  id="regionU" name="rgnidU" value="{{$fAddress[0]->rgn_desc}}" disabled />
    @else

    <select class="form-control selectpicker show-menu-arrow toRemove"  id="region" name="rgnid" required data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" onChange="fetchProvince(this)">
        <option value="">Please select</option>
        @foreach( $regions as $region)
            @if($region->rgnid != 'HFSRB')
            <option value="{{$region->rgnid}}">{{$region->rgn_desc}}</option>
            @endif
        @endforeach
    </select>
    @endif
</div>
<div class="col-md-3">
    <label for="province">Province/District <span class="text-danger">*</span></label>
    @if(isset($fAddress) && count($fAddress) > 0)
    <input class="form-control "  id="provinceU" name="providU" value="{{$fAddress[0]->provname}}" disabled />
    @else

    <select class="form-control selectpicker show-menu-arrow toRemove" value='{{((isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->provid: null)}}' id="province"  disabled name="provid" required data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" onChange="fetchMonicipality(this)">
        <option value="">Please select</option>
    </select>
    @endif
</div>
<div class="col-md-3">
    <label for="city_monicipality">City/Municipality <span class="text-danger">*</span></label>
    @if(isset($fAddress) && count($fAddress) > 0)
    <input class="form-control " id="city_monicipalityU" name="cmidU"  value="{{$fAddress[0]->cmname}}" disabled />
    @else
    <select class="form-control  selectpicker show-menu-arrow toRemove" value='{{((isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->cmid: null)}}' id="city_monicipality" disabled name="cmid" required data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" onChange="fetchBaranggay(this)">
        <option value="">Please select</option>
    </select>
    @endif
</div>
<div class="col-md-3">
    <label for="brgy">Baranggay<span class="text-danger">*</span></label>
    @if(isset($fAddress) && count($fAddress) > 0)
    <input class="form-control "  id="brgyU" name="brgyidU" value="{{$fAddress[0]->brgyname}}" disabled />
    @else
    <select class="form-control selectpicker show-menu-arrow toRemove" value='{{((isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->brgyid: null)}}' id="brgy" disabled name="brgyid" data-live-search="true" data-style="text-dark form-control custom-selectpicker" data-size="5" required>
        <option value="">Please select</option>
    </select>
    @endif
</div>
<div class="mb-2 col-md-12">&nbsp;</div>
<div class="col-md-4">
    <label for="street_num">Street Number </label>
    <input type="text" class="form-control" id="street_num" name="street_number"placeholder="STREET NUMBER"
     value='{{((isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->street_number: null)}}'
     >
</div>
<div class="col-md-4">
    <label for="street_name">Street name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="street_name" name="street_name" placeholder="STREET NAME"
    value='{{(( isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->street_name: null)}}'
    >
</div>
<div class="col-md-4">
    <label for="zip">Zip Code <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="zip" name="zipcode" required placeholder="ZIP CODE"
    value='{{((isset($fAddress) && count($fAddress) > 0) ? $fAddress[0]->zipcode: null)}}'
    >
    <small><span class="text-danger">NOTE: </span>for reference, please follow this <a href="https://www.phlpost.gov.ph/zip-code-search.php" target="_blank">link</a></small>
</div>



<script>
  var fs =  document.getElementById("facid")
  if(fs){
  fs.value = $('#region').val() == undefined ? '{!!((count($fAddress) > 0) ? $fAddress[0]->rgnid: "")!!}' : $('#region').val()
}

window.addEventListener('click', function(e) {
        var facs =  document.getElementsByName('facid');
        var hgpid =  document.getElementsByName('hgpid');
        var ass =  document.getElementById("assignedRgn")
 setTimeout(function(){
       
    if(facs){
          
          if(facs.length > 0){
              for( i = 0; i < facs.length; i++ ) {
                  console.log("exist facid")
                      if( facs[i].checked ) {
                  
                          if(facs[i].value == 'H2' || facs[i].value == 'H3'){
                              ass.value = 'hfsrb';
                              console.log('facs' + facs[i].value)
                              
                          }else{
                              ass.value = $('#region').val() == undefined ? '{!!((count($fAddress) > 0) ? $fAddress[0]->rgnid: "")!!}' : $('#region').val()
                          }
                                      
                      }
                  }
          }else{
              ass.value = $('#region').val() == undefined ? '{!!((count($fAddress) > 0) ? $fAddress[0]->rgnid: "")!!}' : $('#region').val()
          }
      }

    
         
        if(hgpid){
          
          if(hgpid.length > 0){
              for( i = 0; i < hgpid.length; i++ ) {
                  console.log("exist facid")
                      if( hgpid[i].checked ) {
                  
                          if(hgpid[i].value == '1' || hgpid[i].value == '9' || hgpid[i].value == '28' || hgpid[i].value == '5' || hgpid[i].value == '12'){
                              ass.value = 'hfsrb';
                              console.log('hgpid' + hgpid[i].value)
                              
                          }else if(hgpid[i].value == '6'){
                                    if(facs.length > 0){
                                        for( var a = 0; a < facs.length; a++ ) {
                                        console.log("exist facid")
                                            if( facs[a].checked ) {
                                        
                                                if(facs[a].value == 'H2' || facs[a].value == 'H3'){
                                                    ass.value = 'hfsrb';
                                                    console.log('facs' + facs[a].value)
                                                    
                                                }
                                                            
                                            }
                                        }
                                    }else{
                                        ass.value = $('#region').val() == undefined ? '{!!((count($fAddress) > 0) ? $fAddress[0]->rgnid: "")!!}' : $('#region').val()
                                    }
                          }else{
                              ass.value = $('#region').val() == undefined ? '{!!((count($fAddress) > 0) ? $fAddress[0]->rgnid: "")!!}' : $('#region').val()
                          }
                                      
                      }
                  }
          }else{
              ass.value = $('#region').val() == undefined ? '{!!((count($fAddress) > 0) ? $fAddress[0]->rgnid: "")!!}' : $('#region').val()
          }
      }
 
      

       

      
   }, 1000);
   setTimeout(function(){
       console.log("assignedRgn")
       console.log($('#assignedRgn').val())

      
 }, 2000);
  
 });

 setTimeout(function(){
       console.log("assignedRgn")
       console.log($('#assignedRgn').val())
 }, 2000);

</script>