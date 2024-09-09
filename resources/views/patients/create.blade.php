<form method="post" action="{{url($action)}}" class="make_file_ajax" enctype="multipart/form-data">
    <div class="modal-header ">
      <h5 class="modal-title" id="exampleModalLabel">Add New {{@$singular}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body ">
        <div class="row">
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Last Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name">
                </div>
            </div>
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">First Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name">
                </div>
            </div><!--  -->
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Middle Name</label>
                    <input type="text" class="form-control" id="mid_name" name="mid_name" placeholder="Middle Name">
                </div>
            </div>

            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="dob">DOB <span style="color:red;">*</span></label>
                    <input type="text" class="form-control patients_date" id="dob" name="dob" placeholder="Data Of Birth" autocomplete="off" required>
                </div>
            </div>
   
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="age">Age <span style="color:red;">*</span></label>
                    <input type="number" class="form-control" id="age" name="age" placeholder=" Age" readonly="">
                </div>
            </div>

            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Gender <span style="color:red;">*</span></label>
                    <select name="gender" id="gender" class="form-control" required>
                        @foreach($gender_selection as $val)
                            <option value="{{$val}}">{{$val}}</option>
                        @endforeach
                    </select>
                </div>
            </div>


            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Phone <span style="color:red;">*</span></label>
                    <input type="tel" class="form-control"  maxlength="12" id="contact_no" name="contact_no"  placeholder="301-123-4567" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" required>
                </div>
            </div> 
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Cell</label>
                    <input type="tel" class="form-control"  maxlength="12" id="cell" name="cell"  placeholder="301-123-4567" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" >
                </div>
            </div>
            
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Insurance <span style="color:red;">*</span></label>
                    
                    <select class="form-select" name="insurance_id" id="insurance_id" required="required">
                        <option value="">-- Select Insurance --</option>
                        
                        @foreach($insurancesName as $key => $val)
                            <option value="{{$val['id']}}">{{$val['name']}}</option>
                        @endforeach
                        
                    </select>

                </div>
            </div> 
            
                          
           <!--  <div class="col-12 my-2">
                <div class="form-group">
                    <label for="name">Address Line 1</label>rows="3"
                    <textarea class="form-control" name="address" id="address" ></textarea>
                </div>
            </div> -->
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="address">Address Line 1 <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Address Line 1" required>
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="address_2">Address Line 2</label>
                    <input type="text" class="form-control" id="address_2" name="address_2" placeholder="Address Line 2">
                </div>
            </div>
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">City <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="city" name="city" placeholder="City Name" required>
                </div>
            </div>
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">State <span style="color:red;">*</span></label>
                    <input type="text" style="text-transform:uppercase" class="form-control" id="state" name="state" placeholder="State (2 alpha characters only)" required maxlength="2">
                </div>
            </div>
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Zip Code <span style="color:red;">*</span></label>
                    <input type="tel" class="form-control" id="zipCode" maxlength="5" name="zipCode" pattern="[0-9]{5}" placeholder="Five digit zip code" required>
                </div>
            </div>

            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder=" Email" >
                </div>
            </div>
            <!-- <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Date of Death</label>
                    <input type="text" class="form-control patients_date" id="dod" name="dod" placeholder="Date of Death" >
                </div>
            </div> -->
            <div class="col-12 my-2"></div>
            <hr>

            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Primary Care Physician <span style="color:red;">*</span></label>
                    
                    <select class="form-select" name="doctor_id" id="doctor_id" required="required">
                        <option value="">-- Primary Care Physician --</option>
                        
                        @foreach($doctorsName as $key => $val)
                            <option value="{{$val['id']}}">{{$val['first_name'].' '.$val['mid_name'].' '.$val['last_name']}}</option>
                        @endforeach
                        
                    </select>

                </div>
            </div>

           
                
                <div class="col-6 my-2"></div>            
                <div class="col-12 my-2">
                    <div class="form-group">
                        <label for="name">Condition / Diagnosis </label>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Condition / Diagnosis</th>       
                                        <th>Description / Remarks</th>
                                        <th>Active / Resolved</th>   
                                        <th style="width:10%;">
                                            <a onclick="addRowDiagnosis(this)"><i class="fas fa-plus"></i></a>    
                                        </th> 
                                    </tr>
                                </thead>
                                <tbody >
                                    <tr>
                                        <td>

                                            <input type="text" name="diagnosis[1][condition]" class="form-control"  >  
                                        </td>    
                                            
                                        <td>
                                            <input type="text" name="diagnosis[1][description]" class="form-control"  >  
                                        </td> 

                                        <td>
                                            <select name="diagnosis[1][status]" id="status" class="form-control"  >
                                
                                                <option value="Active">Active</option>
                                                <option value="Resolved">Resolved</option>
                                            </select>
    
                                        </td>
                                        <td>

                                        </td>   
                                    </tr> 
                                </tbody>    
                            </table>


                    </div>
                </div>

                <div class="col-12 my-2">
                    <div class="form-group">
                        <label for="name">Medication</label>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name of medication </th>       
                                        <th>Dose / Frequency </th>
                                        <th>Conditions being treated</th>   
                                        <th style="width:10%;">
                                            <a class="addRow" onclick="addRowMedication(this)"><i class="fas fa-plus"></i></a>    
                                        </th> 
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" name="medication[1][name]" class="form-control"  >  
                                        </td>    
                                            
                                        <td>
                                            <input type="text" name="medication[1][dose]" class="form-control"  >  
                                        </td> 

                                        <td>
                                            
                                            <input type="text" name="medication[1][condition]" class="form-control"  > 
                                        </td>
                                        <td>

                                        </td>   
                                    </tr> 
                                </tbody>    
                            </table>
                        

                    </div>
                </div>

                <div class="col-12 my-2">
                    <div class="form-group">
                        <label for="name">Surgical History </label>
                        
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Procedure</th>       
                                        <th>Reason for procedure</th>
                                        <th>Date</th>
                                        <th>Surgeon or facility</th>   
                                        <th style="width:10%;">
                                            <a class="addRow" onclick="addRowSurgical(this)"><i class="fas fa-plus"></i></a>    
                                        </th> 
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" name="surgical_history[1][procedure]" class="form-control"  >  
                                        </td>

                                        <td>
                                            <input type="text" name="surgical_history[1][reason]" class="form-control"  >  
                                        </td> 

                                        <td>
                                            <input type="text" class="form-control patients_date" id="procedure_date" name="surgical_history[1][date]">
                                        </td>

                                        <td>
                                            <input type="text" name="surgical_history[1][surgeon]"
                                            class="form-control"  > 
                                        </td>
                                        <td>

                                        </td>   
                                    </tr> 
                                </tbody>    
                            </table>


                    </div>
                </div>

                <div class="col-12 my-2">
                    <div class="form-group">
                    
                        
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Family History</th>       
                                        <th class="center_line">Father</th>
                                        <th class="center_line">Mother</th>
                                        <th class="center_line">Children</th>
                                        <th class="center_line">Siblings</th>
                                        <th class="center_line">Grandparents</th>    
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <lable>Cancer</lable>
                                        </td>
                                        <td class="center_line">
                                            <input type="checkbox" id="cancer_father" name="cancer[father]" value="Yes">
                                        </td>
                                        <td class="center_line">
                                        <input type="checkbox" id="cancer_mother" name="cancer[mother]" value="Yes">
                                        </td>
                                        <td class="center_line">
                                            <input type="checkbox" id="cancer_children" name="cancer[children]" value="Yes">
                                        </td> 
                                        <td class="center_line">
                                            <input type="checkbox" id="cancer_siblings" name="cancer[siblings]" value="Yes">
                                        </td>

                                        <td class="center_line">
                                            <input type="checkbox" id="cancer_grandparents" name="cancer[grandparents]" value="Yes"> 
                                        </td>
                                        
                                    </tr> 
                                    <tr>
                                        <td>
                                            <lable>Diabetes</lable>
                                        </td>
                                        <td class="center_line">
                                            <input type="checkbox" name="diabetes[father]" value="Yes">
                                        </td>
                                        <td class="center_line">
                                        <input type="checkbox" name="diabetes[mother]" value="Yes">
                                        </td>
                                        <td class="center_line">
                                            <input type="checkbox" name="diabetes[children]" value="Yes">
                                        </td> 
                                        <td class="center_line">
                                            <input type="checkbox" name="diabetes[siblings]" value="Yes">
                                        </td>

                                        <td class="center_line">
                                            <input type="checkbox" name="diabetes[grandparents]" value="Yes"> 
                                        </td>
                                        
                                    </tr>
                                    <tr>
                                        <td>
                                            <lable>Heart disease</lable>
                                        </td>
                                        <td class="center_line">
                                            <input type="checkbox" name="heart_disease[father]" value="Yes">
                                        </td>
                                        <td class="center_line">
                                        <input type="checkbox" name="heart_disease[mother]" value="Yes">
                                        </td>
                                        <td class="center_line">
                                            <input type="checkbox" name="heart_disease[children]" value="Yes">
                                        </td> 
                                        <td class="center_line">
                                            <input type="checkbox" name="heart_disease[siblings]" value="Yes">
                                        </td>

                                        <td class="center_line">
                                            <input type="checkbox" name="heart_disease[grandparents]" value="Yes"> 
                                        </td>
                                        
                                    </tr>
                                    <tr>
                                        <td>
                                            <lable>Hypertension</lable>
                                        </td>
                                        <td class="center_line">
                                            <input type="checkbox" name="hypertension[father]" value="Yes">
                                        </td>
                                        <td class="center_line">
                                        <input type="checkbox" name="hypertension[mother]" value="Yes">
                                        </td>
                                        <td class="center_line">
                                            <input type="checkbox" name="hypertension[children]" value="Yes">
                                        </td> 
                                        <td class="center_line">
                                            <input type="checkbox" name="hypertension[siblings]" value="Yes">
                                        </td>

                                        <td class="center_line">
                                            <input type="checkbox" name="hypertension[grandparents]" value="Yes"> 
                                        </td>
                                        
                                    </tr>
                                </tbody>    
                            </table>


                    </div>
                </div> 
           


            
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</form>



<script type="text/javascript">
    

    $('.patients_date').datepicker({
        format: "mm/dd/yyyy",
        startView: "days",
        minViewMode: "days",
        orientation: "bottom auto",
        autoclose: true
    });
    $(document).on('change','.patients_date',function(){
        const current_date = new Date();
        const date = new Date($(this).val())
        const age = current_date.getFullYear() - date.getFullYear();
        $('#age').val(age);
    });

function addRowDiagnosis(table_ele)
{
     var table = $(table_ele).parents('table:first');

    var tot = $(table).find('tbody:first tr').length+1;
    var tr = `<tr>
    <td><input type="text" name="diagnosis[`+tot+`][condition]" class="form-control "></td>
    <td><input type="text" name="diagnosis[`+tot+`][description]" class="form-control "></td>
    <td><select name="diagnosis[`+tot+`][status]" id="status" class="form-control"> 
        <option value="Active">Active</option> 
        <option value="Resolved">Resolved</option>
    </select></td> 
    <td><a class="btn btn-danger remove" title="Remove Record"><i class="fas fa-times"></i></a></td>
    </tr>`;
    /*var tr = $(table_ele).find('tr:eq(1)').clone();*/
    /*$('tbody1').append(tr);*/
    $(table_ele).parents('table:first').find('tbody').append(tr);


  }
function addRowMedication(table_elem)
{
    var table = $(table_elem).parents('table:first');

    var tot = $(table).find('tbody:first tr').length+1;
    var tr = `<tr>+
    <td><input type="text" name="medication[`+tot+`][name]" class="form-control "></td>
    <td><input type="text" name="medication[`+tot+`][dose]" class="form-control "></td>
    <td><input type="text" name="medication[`+tot+`][condition]" class="form-control "></td>
    <td><a class="btn btn-danger remove" title="Remove Record"><i class="fas fa-times"></i></a></td>
    </tr>`;
    /*$('tbody1').append(tr);*/
    $(table_elem).parents('table:first').find('tbody').append(tr);


  }
function addRowSurgical(table_eles)
{
    var table = $(table_eles).parents('table:first');

    var tot = $(table).find('tbody:first tr').length+1;
    var tr = `<tr>
    <td><input type="text" name="surgical_history[`+tot+`][procedure]" class="form-control "></td>
    <td><input type="text" name="surgical_history[`+tot+`][reason]" class="form-control "></td>
    <td><input type="text" name="surgical_history[`+tot+`][date]" class="form-control patients_date1"></td>
    <td><input type="text" name="surgical_history[`+tot+`][surgeon]" class="form-control "></td>
    <td><a class="btn btn-danger remove" title="Remove Record"><i class="fas fa-times"></i></a></td>
    </tr>`;
    /*$('tbody1').append(tr);*/
    $(table_eles).parents('table:first').find('tbody').append(tr);
    $('.patients_date1').datepicker({
        format: "mm/dd/yyyy",
        startView: "days",
        minViewMode: "days",
        orientation: "bottom auto",
        autoclose: true
    });

  }
  


  $(document).on('click', '.remove',function() {
      $(this).closest('tr').remove();
  });
</script>