                <div class="row my-2">
                    <div class="stepwizard" row='1'>
                        <div class="stepwizard-row setup-panel">

                            <div class="stepwizard-step">
                                <a href="#step-1" type="button" class="btn btn-primary btn-circle" disabled="disabled">1</a>
                                <p>Caregiver Assessment</p>
                            </div>

                            <div class="stepwizard-step">
                                <a href="#step-2" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled">2</a>
                                <p>Environment Assessment</p>
                            </div>

                            <div class="stepwizard-step">
                                <a href="#step-3" type="button" class="btn btn-default bg-secondary btn-circle" disabled="disabled">3</a>
                                <p>Other Providers</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row my-2">
                    <div class="col-12">

                        {{-- CAREGIVER ASSESSMENT START --}}
                        <div class="row setup-content" id="step-1">
                            <h3> Caregiver Assessment </h3>       

                            <div class="form-group mb-3">
                                <label class="control-label">
                                    In the past 7 days, did you need help from others to perform every day activities such as eating, getting dressed, grooming, bathing, walking or using the toilet?
                                </label>
                                @foreach(Config('constants.agree_options') as $key => $val)
                                    <div class="form-group form-check">
                                        <input class="form-check-input tab6" name="caregiver_assessment[every_day_activities]" type="radio" value="{{$val}}" onclick="show_Adls()">
                                        <label class="form-check-label"> {{$val}} </label>
                                    </div>
                                @endforeach
                            </div> 

                            <div class="form-group mb-3">
                                <label class="control-label">
                                    In the past 7 days, did you need help from others to take care of things such as laundry, house-keeping, banking, shopping, using the telephone, food preparation, transportation or taking your own medications?  
                                </label>
                                @foreach(Config('constants.agree_options') as $key => $val)
                                    <div class="form-group form-check">
                                        <input class="form-check-input tab6" name="caregiver_assessment[medications]" type="radio" value="{{$val}}" onclick="show_Adls()">
                                        <label class="form-check-label"> {{$val}} </label>
                                    </div>
                                @endforeach
                            </div>
                                                    
                            {{-- adls Section (show if(no) on both) --}}
                        
                                <div class="form-group mb-3 d-none adls_section">
                                    <label class="control-label"> Do You have a Care giver to help take care of ADLs? </label>
                                    @foreach(Config('constants.agree_options') as $key => $val)
                                    <div class="form-group form-check-inline">
                                        <input class="form-check-input" name="caregiver_assessment[adls]" type="radio" value="{{$val}}" onclick="adls_section_Yes(this)">
                                        <label class="form-check-label"> {{$val}} </label>
                                    </div>
                                    @endforeach
                                </div>

                                {{-- Live_the_patient Section (show if yes adls)  start--}}
                        
                                <div class="form-group mb-3 d-none Live_the_patient_section">                                   
                                    <div class="form-group mb-3">

                                        <label class="control-label"> Who is your help?</label>
                                        <input type="text" class="form-control your_help_wife" name="caregiver_assessment[your_help_wife]" value="" >
                                    </div>
                                    
                                    <div class="form-group mb-3">

                                       <label class="control-label">Live with the patient?</label>
                                        <input type="text" class="form-control Live_patient" name="caregiver_assessment[Live_patient]"  value="">
                                    </div>
                                    
                                </div>
                               {{-- Live_the_patient Section (show if yes adls)  end--}}

                               {{-- Live_the_patient Section (show if no adls)  start--}} 
                                     <div class="form-group mb-3 d-none adls_section_No">
                                         <label class="control-label"> Referred to Home Health? </label>
                                        @foreach(Config('constants.agree_options') as $key => $val)
                                        <div class="form-group form-check-inline">
                                            <input class="form-check-input" name="caregiver_assessment[adls_No]" type="radio" value="{{$val}}" >
                                            <label class="form-check-label"> {{$val}} </label>
                                        </div>
                                        @endforeach
                                    </div>
                                
                                {{-- Live_the_patient Section (show if no adls) End--}}
                            
                            <div class="pull-right align-items-end">
                                <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
                                    <button class="btn btn-primary nextBtn mx-2 pull-right" type="button" >Next</button>
                                </div>
                            </div>
                        </div>
                        {{-- CAREGIVER ASSESSMENT  END --}}


                                {{-- OTHER PROVIDER START --}}
                        <div class="row setup-content" id="step-3">
                                <h3> Other Provider </h3>
                            <div class="form-group mb-3">
                                <label class="control-label">
                                    Do you see any other Provider beside PCP?
                                </label>
                                @foreach(Config('constants.agree_options') as $key => $val)
                                    <div class="form-group form-check">
                                        <input class="form-check-input tab6" name="Other_Provider[other_Provider_beside_PCP]" type="radio" value="{{$val}}" onclick="other_Provider_beside_PCP_Yes()">
                                        <label class="form-check-label"> {{$val}} </label>
                                    </div>
                                @endforeach
                            </div>
                            {{-- other_Provider_beside_PCP_Yes Section (show if yes adls)  start--}}
                        
                            <div class="form-group mb-3 d-none other_Provider_beside_PCP_section">                                   
                                    <div class="form-group mb-3">

                                        <label class="control-label"> Name </label>
                                        <input type="text" class="typeahead form-control full_Name" name="other_Provider[full_Name]" value="" id ="searchaabb">
                                    </div>
                                    
                                    <div class="form-group mb-3">

                                       <label class="control-label">Speciality </label>
                                        <input type="text" class="form-control speciality" name="other_Provider[speciality]"  value="">
                                    </div>
                                    
                            </div>
                               {{-- Live_the_patient Section (show if yes adls)  end--}} 
            
                            <div class="pull-right align-items-end">
                                <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
                                    <button class="btn btn-primary mx-2" type="submit" >Finish!</button>
                                    <button class="btn btn-primary prevBtn" type="button">Previous</button>
                                </div>
                            </div>

                        </div>
        {{-- OTHER PROVIDER END --}}
                       
                    </div>
                </div>