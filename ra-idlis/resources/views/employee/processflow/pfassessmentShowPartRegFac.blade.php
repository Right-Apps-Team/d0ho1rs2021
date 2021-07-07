@if (session()->exists('employee_login'))  
	@extends('mainEmployee')
	@section('title', 'Choose assessment file')
	@section('content')
		<style>
			a{
				text-decoration: none!important;
			}
			.legend {
			  background-color: #fff;
			  left: 80px;
			  padding: 20px;
			  border: 1px solid;
			}
			.legend h4 {
			  text-transform: uppercase;
			  font-family: sans-serif;
			  text-align: center;
			}
			.legend ul {
			  list-style-type: none;
			  margin: 0;
			  padding: 0;
			}
			.legend li { padding-bottom: 5px; }
			.legend span {
			  display: inline-block;
			  width: 12px;
			  height: 12px;
			  margin-right: 6px;
			}
			a.button6{
				border: 1px solid black;
			    display: inline-block;
			    padding: 2em 1.4em;
			    margin: 0 0.3em 0.3em 0;
			    border-radius: 0.15em;
			    box-sizing: border-box;
			    text-decoration: none;
			    font-family: 'Roboto',sans-serif;
			    text-transform: uppercase;
			    font-weight: 400;
			    color: #FFFFFF;
			    background-color: rgb(255,192,0);
			    box-shadow: inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
			    text-align: center;
			    position: relative;
			    color:black;
			    /*text-shadow: 1px 1px 1px #000, 3px 3px 5px black; */
			}
			.buttonOthers{
				border: 1px solid black;
			    display: inline-block;
			    padding: 2em 1.4em;
			    margin: 0 0.3em 0.3em 0;
			    border-radius: 0.15em;
			    box-sizing: border-box;
			    text-decoration: none;
			    font-family: 'Roboto',sans-serif;
			    text-transform: uppercase;
			    font-weight: 400;
			    color: #FFFFFF;
			    background-color: rgb(255,192,0);
			    box-shadow: inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
			    text-align: center;
			    position: relative;
				text-shadow: 1px 1px 1px #000, 3px 3px 5px black; 
			}
			a.button6:active{
			 top:0.1em;
			}
			@media all and (max-width:30em){
			 a.button6{
			  display:block;
			  margin:0.4em auto;
			 }
			}
		</style>
		<div class="container border">
			<input type="text" id="CurrentPage" hidden="" value="PF009">
			
			@if(isset($head[0]->idForBack) || isset($customAddress))
										@php
				                          $url = 'employee/dashboard/processflow/parts/new/'.$data->regfac_id.'/'.$isMon.'/' ;
				                     
				                   
				                        @endphp
										<!-- {{-- url = 'employee/dashboard/processflow/parts/new/'.$data->regfac_id.'/'.$mon->monid ;--}} -->
										
			<div class="col-md mt-5">   <button class="btn btn-outline-primary" onclick="window.location.href='{{url($url)}}'">Back </button></div>
			@else
		
				<div class="col-md mt-5">   <a href="{{asset('employee/dashboard/others/monitoring/inspection')}}"> <button class="btn btn-outline-primary" >Back </button></a></div>
			
			@endif
			
			<table>
				<tr>
					<td style="width: 25%;">
						
					</td>
					<td style="width: 50%;">
						<div class="col display-4 text-center mt-5 font-weight-bold">{{$data->facilityname}}</div>
					</td>
					<td style="width: 25%;">
						<div class="legend">
						    <h4>Legend</h4>
						    <ul>
						        <li><span class="bg-success"></span>Assessed</li>
						        <li><span class="bg-warning"></span>Pending for Assessment</li>
						    </ul>
						</div>
					</td>
				</tr>
				
			</table>
			@if(!isset($headon))
			<script>
			console.log('{!! $dbcheck !!}')
			</script>
			@endif
			<div class="col text-center mt-3" style="font-size: 30px">{{($isMon ? 'Monitoring Tool' : (isset($isPtc) ? 'Evaluation Tool' : 'Assessment Tool') )}}</div>
			@if(isset($hasselfassess) && $hasselfassess)
			<!-- remove 6-22-2021 -->
			@endif
			
			<div class="row p-5 text-center main">
				@php 
					$arrDat = array();
					$arrDat1 = array();
				@endphp
				@foreach($head as $key => $value)

				
					<!-- @if(!in_array($value->id, $arrDat))
					@php 
						array_push($arrDat, $value->id)
					@endphp
					<div class="col-sm-12">
                    
						<a href="{{$address.'/'.$value->id.'/'.$isMon.($isOtherUid ?? '')}}" class="button6 btn-block {{$value->id}}">{{$value->desc}}</a> -->
						<!-- Excluded <a href="{{$address.'/'.$value->id.'/'.$isMon.($isOtherUid ?? '')}}" class="button6 btn-block {{$value->id}}">{{$value->desc}}</a> -->
					<!-- </div>
					@endif -->

					@if(isset($headon) && ($value->h1HeadID == 'AOASPT2AT' || $value->h1HeadID == 'AOASPT1AT'))

				
					@if(!in_array((isset($value->xid)? $value->xid : $value->id), $arrDat))
					@php 
						array_push($arrDat, (isset($value->xid)? $value->xid : $value->id))
					@endphp
					
					<div class="col-sm-12">
						<a href="{{$address.'/'.$value->id.'/'.$isMon.($isOtherUid ?? '').'?xid='.(isset($value->xid)? $value->xid : $value->id).'&pid='.$value->id.'&hid='.(app('request')->input('pid')?app('request')->input('pid'): app('request')->input('hid')).'&monid='.$isMon}}" class="button6 btn-block {{(isset($value->xid)? $value->xid : $value->id)}}">{{$value->desc}}</a>
						<!-- <a href="{{$address.'/'.$value->id.'/'.$isMon.'?xid='.$value->xid}}" class="button6 btn-block {{$value->id}}">{{$value->desc}}</a> -->
					</div>
					@endif 
					@else

					
					@if(!in_array($value->id, $arrDat1))
					@php 
						array_push($arrDat1, $value->id)
					@endphp 
					<div class="col-sm-12">
						<a href="{{$address.'/'.$value->id.'/'.$isMon.($isOtherUid ?? '').'?xid='.(isset($value->xid)? $value->xid : $value->id).'&pid='.$value->id.'&hid='.(app('request')->input('pid')?app('request')->input('pid'): app('request')->input('hid')).'&monid='.$isMon}}" id="{{$value->xid}}" class="button6 btn-block {{$value->id}}">{{$value->desc}}</a>
						
					</div>
					@endif
					@endif


				@endforeach
				@isset($isMain)
				<div class="col-sm-12">
					<a href="{{url('employee/dashboard/processflow/'.(isset($isPtc) ? 'floorPlan/' : '').'GenerateReportAssessments/regfac/'.$regfac_id.'/'.$isMon.(isset($isPtc) ? $revision. '/'. session()->get('employee_login')->uid :''))}}" class="button6 btn-block generate">Generate Report</a>
				</div>
				@endisset
			</div>
			<div class="container buttonHere">
				
			</div>
		</div>
		<script>
            $(function(){

			

            	@if(isset($isSentFromMobile) && $isSentFromMobile)
        		$('a.btn-block').not('.generate').each(function(index, el) {
        			let textOnDiv = $(this).text();
        			$(this).replaceWith('<p class="buttonOthers btn-block done" style="background-color:#28A745;"><i class="fa fa-check-circle p-2" aria-hidden="true"></i>'+textOnDiv+'</p>');
        		});

            	@endif

					@if(app('request')->input('pid') == 'AOASPT1AT' || app('request')->input('pid') == 'AOASPT2AT')
					
					let assesed = {!!empty($assesednew) ? json_encode('none') : json_encode($assesednew) !!};
					console.log("assesed")
					console.log(assesed)
					if(assesed instanceof Array){
						$.each(assesed,function(index, el) {
							console.log("el")
							console.log(el)
							// if(el != 'AOASPT1AT' && el != 'AOASPT2AT' && el != 298 && el != 299){

							let textOnDiv = $('.'+el).text();
							$('.'+el).replaceWith('<p class="buttonOthers btn-block done" style="background-color:#28A745;"><i class="fa fa-check-circle p-2" aria-hidden="true"></i>'+textOnDiv+'</p>');
						//    }
						
									});
					}
					@else
					console.log("assesed114343")
							let assesed = {!! empty($assesed) ? json_encode('none') : json_encode($assesed) !!};
							console.log(assesed)
							if(assesed instanceof Array){
								$.each(assesed,function(index, el) {
									console.log("el1")
									console.log(el)
									// if(el != 'AOASPT1AT' && el != 'AOASPT2AT' && el != 298 && el != 299){

									let textOnDiv = $('.'+el).text();
									$('.'+el).replaceWith('<p class="buttonOthers btn-block done" style="background-color:#28A745;"><i class="fa fa-check-circle p-2" aria-hidden="true"></i>'+textOnDiv+'</p>');
								//    }
								
								});
							}

					@endif


                // let assesed = {!!empty($assesed) ? json_encode('none') : json_encode($assesed) !!};
                // if(assesed instanceof Array){
                //     $.each(assesed,function(index, el) {
                //         let textOnDiv = $('.'+$.escapeSelector(el)).text();
                //         $('.'+el).replaceWith('<p class="buttonOthers btn-block done" style="background-color:#28A745;"><i class="fa fa-check-circle p-2" aria-hidden="true"></i>'+textOnDiv+'</p>');
                //     });
                // }


                if($('.main div').length == $('.main p.done').length){
                	@if(!isset($isMain))
					$.ajax({
						url: '{{url('employee/dashboard/processflow/registerAssess')}}',
						method: 'POST',
						data: {_token: '{{csrf_token()}}',level: '{{$neededData['level']}}', appid: '{{$regfac_id}}',id: '{{$neededData['id']}}', monid: '{{$isMon}}', isPtc: '{{($isPtc ?? false)}}'}
					})			
					@endif

				}


				var idss =JSON.parse('{!! ((count($dbcheck) > 0) ? $dbcheck: "[]")  !!}');


				if(idss.length > 0){
					for(var i = 0; i < idss.length; i++){
						console.log(idss[i].x08_id)
						document.getElementById(idss[i].x08_id).style.backgroundColor  = "#28A745";
						document.getElementById(idss[i].x08_id).style.color  = "white";
					}
				}

            })
        </script>
	@endsection
@else
  <script type="text/javascript">window.location.href= "{{ asset('employee') }}";</script>
@endif
