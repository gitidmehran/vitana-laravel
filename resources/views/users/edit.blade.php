<form method="post" action="{{url($action.'/'.$row['id'])}}" class="make_file_ajax" enctype="multipart/form-data">
     @method('PUT')
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Update {{@$singular}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="last_name">Last Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" required value="{{@$row['last_name']}}">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="first_name">First Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required value="{{@$row['first_name']}}">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="mid_name">Middle Name</label>
                    <input type="text" class="form-control" id="mid_name" name="mid_name" placeholder="Middle Name" value="{{@$row['mid_name']}}">
                </div>
            </div>
            <!-- <div class="col-6 my-2">
                <div class="form-group">
                    <label for="role">Designation</label>
                        <select name="role" id="role" class="form-control">
                            
                            <option value="1">Admin</option>
                            <option value="2">Doctor</option>
                            <option value="3">Pharmacist</option>
                            <option value="4">CMM Coordinate</option>
                        </select>
                </div>
            </div> -->

            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="role">Designation <span style="color:red;">*</span></label>

                    <select name="role" id="role" class="form-control" required value= "{{@$row['role']}}">

                   @foreach(Config('constants.roles') as $key => $val)
                       
                            <option value="{{$key}}" @if($key==$row['role']) selected @endif>{{$val}}</option>
                       
                    @endforeach
                       <!--  <option value="1" @if(1==$row['role']) selected @endif>Admin</option>

                        <option value="2" @if(2==$row['role']) selected @endif>Doctor</option>
                        <option value="3" @if(3==$row['role']) selected @endif>Pharmacist</option> 
                        <option value="4" @if(3==$row['role']) selected @endif>CMM Coordinate</option>
 -->
                        
                    </select>
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Contact <span style="color:red;">*</span></label>
                   <input type="tel" class="form-control"  maxlength="12" id="contact_no" name="contact_no"  placeholder="301-123-4567" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" required value="{{@$row['contact_no']}}">

                </div>
            </div>
            <!-- <div class="col-6 my-2">
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" class="form-control" id="age" name="age" placeholder="Doctor Age" value="{{@$row['age']}}">
                </div>
            </div> -->
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Gender <span style="color:red;">*</span></label>
                    <select name="gender" id="gender" class="form-control">
                        @foreach($gender_selection as $val)
                            <option value="{{$val}}" @if($val==@$row['gender']) selected @endif>{{$val}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- <div class="col-12 my-2">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required value="{{@$row['email']}}">
                </div>
            </div>
            <div class="col-12 my-2">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required value="{{@$row['password']}}">
                </div>
            </div>  -->   
            
            <!-- <div class="col-12 my-2">
                <div class="form-group">
                    <label for="name">Address</label>
                    <textarea class="form-control" name="address" id="address" rows="3">{{@$row['address']}}</textarea>
                </div>
            </div>  -->          
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</form>