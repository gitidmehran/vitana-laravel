

@php 
    $dob = \Carbon\Carbon::parse($row['dob'])->format('m/d/Y'); 
    $dod = \Carbon\Carbon::parse($row['dod'])->format('m/d/Y'); 
@endphp


<form method="post" action="{{url($action.'/'.$row['id'])}}" class="make_file_ajax" enctype="multipart/form-data">
	 @method('PUT')
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Update {{@$singular}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Last Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" value="{{@$row['last_name']}}" required>
                </div>
            </div>
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">First Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" value="{{@$row['first_name']}}" required>
                </div>
            </div>
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Middle Name</label>
                    <input type="text" class="form-control" id="mid_name" name="mid_name" placeholder="Middle Name" value="{{@$row['mid_name']}}">
                </div>
            </div>

             <div class="col-4 my-2">
                <div class="form-group">
                    <label for="dob">DOB <span style="color:red;">*</span></label>
                    <input type="text" class="form-control patients_date" id="dob" name="dob" placeholder=" Data Of Birth" required value="{{$dob}}">
                </div>
            </div> 
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="age">Age <span style="color:red;">*</span></label>
                    <input type="number" class="form-control" id="age" name="age" placeholder=" Age" value="{{@$row['age']}}" readonly="">
                </div>
            </div>
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Gender <span style="color:red;">*</span></label>
                    <select name="gender" id="gender" class="form-control" required>
                        @foreach($gender_selection as $val)
                            <option value="{{$val}}" @if($val==@$row['gender']) selected @endif>{{$val}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Phone <span style="color:red;">*</span></label>
                    <input type="tel" class="form-control"  maxlength="12" id="contact_no" name="contact_no"  placeholder="301-123-4567" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" required value="{{@$row['contact_no']}}">
                   <!--  <input type="text" class="form-control" id="contact_no" name="contact_no" placeholder="Short Name" value="{{@$row['contact_no']}}"> -->
                </div>
            </div>
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Cell</label>
                    <input type="tel" class="form-control"  maxlength="12" id="cell" name="cell"  placeholder="301-123-4567" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" value="{{@$row['cell']}}">
                   <!--  <input type="text" class="form-control" id="contact_no" name="contact_no" placeholder="Short Name" value="{{@$row['contact_no']}}"> -->
                </div>
            </div>
            
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Insurance <span style="color:red;">*</span></label>
                    
                    <select class="form-select" required name="insurance_id" id="insurance_id" value= "{{@$row['insurancesName']}}">>
                        <option value="">-- Select Insurance --</option>

                        @foreach($insurancesName as $key => $val)
                           
                            <option value="{{$val['id']}}" @if($val['id']==$row['insurance_id']) selected @endif>{{$val['name']}} </option>
                        @endforeach 
                        
                    </select>

                </div>
            </div> 
            
            <!-- <div class="col-12 my-2">
                <div class="form-group">
                    <label for="name">Address</label>
                    <textarea class="form-control" name="address" id="address" rows="3">{{@$row['address']}}</textarea>
                </div>
            </div>  -->
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Address Line 1 <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="address" name="address" required placeholder="Address Line 1" value="{{@$row['address']}}">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Address Line 2</label>
                    <input type="text" class="form-control" id="address_2" name="address_2" placeholder="Address Line 2" value="{{@$row['address_2']}}">
                </div>
            </div>
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">City <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="city" name="city" required
                 placeholder="City Name" value="{{@$row['city']}}">
                </div>
            </div>
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">State <span style="color:red;">*</span></label>
                    <input type="text" style="text-transform:uppercase" class="form-control" id="state" name="state" placeholder="State (2 alpha characters only)" maxlength="2" required pattern="([A-z0-9À-ž\s]){2,}" value="{{@$row['state']}}" >
                </div>
            </div>
            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Zip Code <span style="color:red;">*</span></label>
                    <input type="number" class="form-control" id="zipCode" name="zipCode" maxlength="5" required placeholder="Five digit zip code" value="{{@$row['zipCode']}}">
                </div>
            </div>

            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name"> Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder=" Email" value="{{@$row['email']}}">
                </div>
            </div>
            {{-- <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Date of Death</label>
                    <input type="text" class="form-control patients_date" id="dod" name="dod" placeholder="Date of Death" value="{{$dod}}">
                </div>
            </div>--}}

            <div class="col-12 my-2"></div>
            <hr>

            <div class="col-4 my-2">
                <div class="form-group">
                    <label for="name">Primary Care Physician <span style="color:red;">*</span></label>

                    <select class="form-select" required name="doctor_id" id="doctor_id" value= "{{@$row['doctorsName']}}"> >
                        <option value="">-- Primary Care Physician --</option>

                        @foreach($doctorsName as $key => $val)
                           
                            <option value="{{$val['id']}}" @if($val['id']==$row['doctor_id']) selected @endif>{{$val['first_name']}} {{$val['mid_name']}} {{$val['last_name']}}</option>
                        @endforeach 
                        
                    </select>
                    <!-- <input type="text" class="form-control" id="name" name="name" placeholder="Name" required value="{{@$row['name']}}"> -->
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
                                @foreach($diagnosis as $key => $val)                    

                                <tr>
                                    <td>

                                        <input type="text" name="diagnosis[1][condition]" class="form-control" value="{{$val['condition']}}" readonly>  
                                    </td>    
                                        
                                    <td>
                                        <input type="text" name="diagnosis[1][description]" class="form-control" value="{{$val['description']}}" readonly>  
                                    </td> 

                                    <td>
                                        <select name="diagnosis[1][status]" id="status" class="form-control" value="{{$val['status']}}" readonly> 
                                            

                                            <option value="Active" @if("Active"==$val['status']) selected @endif>Active</option>
                                            <option value="Resolved"  @if("Resolved"==$val['status']) selected @endif>Resolved</option>
                                            
                                        </select>
  
                                    </td>
                                    <td>
                                       <!-- <button type="button" class="btn btn-danger remove" title="Remove Row"><i class="fas fa-times"></i></button>  -->
                                            
                                        <button type="button" class="spellMistake_D btn btn-warning " data-id="{{ $val['id'] }}" title="Delete the record in case of any error."><i class="fa fa-times" aria-hidden="true"></i></button>

                                         <button class="deleteRecord_D btn btn-danger" data-id="{{ $val['id'] }}" title="Delete Record"><i class="fa fa-times" aria-hidden="true"></i></button>
                                </tr> 
                                @endforeach 
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
                                        <a  class="addRow" onclick="addRowMedication(this)"><i class="fas fa-plus"></i></a>    
                                    </th> 
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($medications as $key => $val)    
                                <tr>
                                    <td>
                                        <input type="text" name="medication[1][name]" class="form-control"  value="{{$val['name']}}" readonly>   
                                    </td>    
                                        
                                    <td>
                                        <input type="text" name="medication[1][dose]" class="form-control" value="{{$val['dose']}}" readonly>  
                                    </td> 

                                    <td>
                                        
                                         <input type="text" name="medication[1][condition]" class="form-control"  value="{{$val['condition']}}" readonly> 
                                    </td>
                                    <td>
                                        <!-- <button type="button" class="btn btn-danger remove" title="Remove Row"><i class="fas fa-times"></i></button>  -->
                                            
                                        <button type="button" class="spellMistake_M btn btn-warning " data-id="{{ $val['id'] }}" title="Delete the record in case of any error."><i class="fa fa-times" aria-hidden="true"></i></button>

                                         <button class="deleteRecord_M btn btn-danger" data-id="{{ $val['id'] }}" title="Delete Record"><i class="fa fa-times" aria-hidden="true"></i></button>
                                    </td>   
                                </tr>
                                @endforeach  
                            </tbody>    
                        </table>
                    

                </div>
            </div>

            <div class="col-12 my-2">
                <div class="form-group">
                    <label for="name">Surgical History f</label>
                    
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Procedure</th>       
                                    <th>Reason for procedure</th>
                                    <th>Date</th>
                                    <th>Surgeon or facility</th>   
                                    <th style="width:10%;">
                                        <a  class="addRow" onclick="addRowSurgical(this)"><i class="fas fa-plus"></i></a>    
                                    </th> 
                                </tr>
                            </thead>
                            <tbody>
                            
                           
                                @foreach($SurgicalHistory as $key => $val) 
                                @php
                                    $procedure_date = ($val['date'] !="" ? date('m/d/Y',strtotime($val['date'])):"");
                                    
                                 @endphp                   
                                <tr>
                                      
                                    <td>
                                        <input type="text" name="surgical_history[1][procedure]" class="form-control" value="{{$val['procedure']}}" readonly>  
                                    </td>

                                    <td>
                                        <input type="text" name="surgical_history[1][reason]" class="form-control" value="{{$val['procedure']}}" readonly>  
                                    </td> 

                                    <td>
                                        <input type="text" class="form-control patients_date1" id="procedure_date1" name="surgical_history[1][date]" value="{{ $procedure_date }}" readonly>
                                        
                                    </td>

                                    <td>
                                         <input type="text" name="surgical_history[1][surgeon]"
                                          class="form-control" value="{{$val['surgeon']}}" readonly> 
                                    </td>
                               
                                    <td>
                                         <!-- <button type="button" class="btn btn-danger remove"><i class="fas fa-times" title="Remove Row"></i></button> -->

                                         <button type="button" class="spellMistake_SH btn btn-warning " data-id="{{ $val['id'] }}" title="Delete the record in case of any error."><i class="fa fa-times" aria-hidden="true"></i></button>

                                         <button class="deleteRecord_SH btn btn-danger" data-id="{{ $val['id'] }}" title="Delete Record"><i class="fa fa-times" aria-hidden="true"></i></button>

                                    </td>  
                                      
                                </tr> 
                                @endforeach
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
                                         <input type="checkbox" id="cancer_father" name="cancer[father]"  value="Yes"  @if(!empty($family_history['cancer']['father']))  checked="checked" @endif >
                                    </td>
                                    <td class="center_line">
                                       <input type="checkbox" id="cancer_mother" name="cancer[mother]" value="Yes"  @if(!empty($family_history['cancer']['mother']))  checked="checked" @endif >
                                    </td>
                                    <td class="center_line">
                                        <input type="checkbox" id="cancer_children" name="cancer[children]" value="Yes"  @if(!empty($family_history['cancer']['children']))  checked="checked" @endif ">
                                    </td> 
                                    <td class="center_line">
                                        <input type="checkbox" id="cancer_siblings" name="cancer[siblings]" value="Yes"  @if(!empty($family_history['cancer']['siblings']))  checked="checked" @endif ">
                                    </td>

                                    <td class="center_line">
                                         <input type="checkbox" id="cancer_grandparents" name="cancer[grandparents]" value="Yes"  @if(!empty($family_history['cancer']['grandparents']))  checked="checked" @endif "> 
                                    </td>
                                      
                                </tr> 
                                <tr>
                                    <td>
                                         <lable>Diabetes</lable>
                                    </td>
                                    <td class="center_line">
                                         <input type="checkbox" name="diabetes[father]" value="Yes"  @if(!empty($family_history['diabetes']['father']))  checked="checked" @endif ">
                                    </td>
                                    <td class="center_line">
                                       <input type="checkbox" name="diabetes[mother]" value="Yes"  @if(!empty($family_history['diabetes']['mother']))  checked="checked" @endif ">
                                    </td>
                                    <td class="center_line">
                                        <input type="checkbox" name="diabetes[children]" value="Yes"  @if(!empty($family_history['diabetes']['children']))  checked="checked" @endif ">
                                    </td> 
                                    <td class="center_line">
                                        <input type="checkbox" name="diabetes[siblings]" value="Yes"  @if(!empty($family_history['diabetes']['siblings']))  checked="checked" @endif ">
                                    </td>

                                    <td class="center_line">
                                         <input type="checkbox" name="diabetes[grandparents]" value="Yes"  @if(!empty($family_history['diabetes']['grandparents']))  checked="checked" @endif ">
                                    </td>
                                      
                                </tr>
                                <tr>
                                    <td>
                                         <lable>Heart disease</lable>
                                    </td>
                                    <td class="center_line">
                                         <input type="checkbox" name="heart_disease[father]" value="Yes"  @if(!empty($family_history['heart_disease']['father']))  checked="checked" @endif ">
                                    </td>
                                    <td class="center_line">
                                       <input type="checkbox" name="heart_disease[mother]" value="Yes" @if(!empty($family_history['heart_disease']['mother']))  checked="checked" @endif ">
                                    </td>
                                    <td class="center_line">
                                        <input type="checkbox" name="heart_disease[children]" value="Yes" @if(!empty($family_history['heart_disease']['children']))  checked="checked" @endif ">
                                    </td> 
                                    <td class="center_line">
                                        <input type="checkbox" name="heart_disease[siblings]" value="Yes" @if(!empty($family_history['heart_disease']['siblings']))  checked="checked" @endif ">
                                    </td>

                                    <td class="center_line">
                                         <input type="checkbox" name="heart_disease[grandparents]" value="Yes" @if(!empty($family_history['heart_disease']['grandparents']))  checked="checked" @endif ">
                                    </td>
                                      
                                </tr>
                                 <tr>
                                    <td>
                                         <lable>Hypertension</lable>
                                    </td>
                                    <td class="center_line">
                                         <input type="checkbox" name="hypertension[father]" value="Yes"  @if(!empty($family_history['hypertension']['father']))  checked="checked" @endif ">

                                    </td>
                                    <td class="center_line">
                                       <input type="checkbox" name="hypertension[mother]" value="Yes"@if(!empty($family_history['hypertension']['mother']))  checked="checked" @endif ">

                                    </td>
                                    <td class="center_line">
                                        <input type="checkbox" name="hypertension[children]" value="Yes"  @if(!empty($family_history['hypertension']['children'])) checked="checked" @endif ">

                                    </td> 
                                    <td class="center_line">
                                        <input type="checkbox" name="hypertension[siblings]" value="Yes"  @if(!empty($family_history['hypertension']['siblings']))  checked="checked" @endif ">
                                    </td>

                                    <td class="center_line">
                                         <input type="checkbox" name="hypertension[grandparents]" value="Yes"  @if(!empty($family_history['hypertension']['grandparents']))  checked="checked" @endif ">

                                    </td>
                                      
                                </tr>
                            </tbody>    
                        </table>


                </div>
            </div> 

                             
        </div>
    </div>
    <div class="modal-footer">
    	<button type="submit" class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</form>

<script type="text/javascript">
    

    $('.patients_date').datepicker({
        format: "mm/dd/yyyy",
        startView: "days",
        minViewMode: "days",
        orientation: "bottom auto"
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
        orientation: "auto top"
    });

  }
  


  $(document).on('click', '.remove',function() {
      $(this).closest('tr').remove();
  });



  $(".deleteRecord_SH").click(function(){

    var id = $(this).data("id");
    var token = $("meta[name='csrf-token']").attr("content");
   //alert(id);
    $.ajax(
    {
        
        url: "surgical_history_destroy/"+id,
        type: 'DELETE',
        data: {
            "id": id,
            "_token": token,
        },
        success: function (){
            toastr.success('Surgical record is deleted successfully!');
        }
    });


      $(this).closest('tr').remove();

   
});

  $(".spellMistake_SH").click(function(){

    var id = $(this).data("id");
    var token = $("meta[name='csrf-token']").attr("content");
  // alert(id);
    $.ajax(
    {
        
        url: "surgical_history_spellMistake/"+id,
        type: 'GET',
        data: {
            "id": id,
            "_token": token,
        },
        success: function (){
            toastr.success('Surgical record is deleted successfully!');
        }
    });

    $(this).closest('tr').remove();
   
});


// 


$(".deleteRecord_M").click(function(){

    var id = $(this).data("id");
    var token = $("meta[name='csrf-token']").attr("content");
   //alert(id);
    $.ajax(
    {
        
        url: "medication_destroy/"+id,
        type: 'DELETE',
        data: {
            "id": id,
            "_token": token,
        },
        success: function (){
            toastr.success('Medication record is deleted successfully!');
        }
    });

    $(this).closest('tr').remove();
   
});

  $(".spellMistake_M").click(function(){

    var id = $(this).data("id");
    var token = $("meta[name='csrf-token']").attr("content");
   //alert(id);
    $.ajax(
    {
        
        url: "medication_spellMistake/"+id,
        type: 'GET',
        data: {
            "id": id,
            "_token": token,
        },
        success: function (){
            toastr.success('Medication record is deleted successfully!');
        }
    });

    $(this).closest('tr').remove();
   
});


  //



  $(".deleteRecord_D").click(function(){

    var id = $(this).data("id");
    var token = $("meta[name='csrf-token']").attr("content");
   //alert(id);
    $.ajax(
    {
        
        url: "diagnosis_destroy/"+id,
        type: 'DELETE',
        data: {
            "id": id,
            "_token": token,
        },
        success: function (){
            toastr.success('Diagnosis record is deleted successfully!');
        }
    });

    $(this).closest('tr').remove();
   
});

  $(".spellMistake_D").click(function(){

    var id = $(this).data("id");
    var token = $("meta[name='csrf-token']").attr("content");
   //alert(id);
    $.ajax(
    {
        
        url: "diagnosis_spellMistake/"+id,
        type: 'GET',
        data: {
            "id": id,
            "_token": token,
        },
        success: function (){
            toastr.success('Diagnosis record is deleted successfully!!');
        }
    });

    $(this).closest('tr').remove();
   
});
  
</script>