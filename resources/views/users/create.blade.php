
<form method="post" action="{{url($action)}}" class="make_file_ajax" enctype="multipart/form-data">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Add New {{@$singular}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="last_name">Last Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" required>
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="first_name">First Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required>
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="mid_name">Middle Name</label>
                    <input type="text" class="form-control" id="mid_name" name="mid_name" placeholder="Middle Name">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="role">Designation <span style="color:red;">*</span></label>
                        <select name="role" id="role" required class="form-control">
                         @foreach(Config('constants.roles') as $key => $val)
                         
                            <option value="{{$key}}">{{$val}}</option>
                       
                    @endforeach     
                           <!--  <option value="1">Admin</option>
                             <option value="2">Doctor</option> 
                            <option value="3">Pharmacist</option>
                            <option value="4">CMM Coordinate</option> -->
                        </select>
                    <!-- <input type="text" class="form-control" id="mid_name" name="mid_name" placeholder="Middle Name"> -->
                </div>
            </div>
            
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Contact <span style="color:red;">*</span></label>
                     <input type="tel" class="form-control"  maxlength="12" id="contact_no" name="contact_no"  placeholder="301-123-4567" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" required>
                   
                </div>
            </div>   
            <!-- <div class="col-6 my-2">
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" class="form-control" id="age" name="age" placeholder="Doctor Age">
                </div>
            </div> -->
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Gender <span style="color:red;">*</span></label>
                    <select name="gender" id="gender" required class="form-control">
                        @foreach($gender_selection as $val)
                            <option value="{{$val}}">{{$val}}</option>
                        @endforeach
                    </select>
                </div>
            </div> 
            <div class="col-12 my-2">
                <div class="form-group">
                    <label for="email">Email <span style="color:red;">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                </div>
            </div>
            <div class="col-12 my-2">
                <div class="form-group">
                    <label for="password">Password <span style="color:red;">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                </div>
            </div>              
            
            <!-- <div class="col-12 my-2">
                <div class="form-group">
                    <label for="name">Address</label>
                    <textarea class="form-control" name="address" id="address" rows="3"></textarea>
                </div>
            </div>   -->          
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</form>