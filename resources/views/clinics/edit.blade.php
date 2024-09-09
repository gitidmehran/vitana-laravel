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
                    <label for="name">Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Last Name" required value="{{@$row['name']}}">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="short_name">Short Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="short_name" name="short_name" placeholder="Short Name" required value="{{@$row['short_name']}}">
                </div>
            </div>
            
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Contact <span style="color:red;">*</span></label>
                   <input type="tel" class="form-control"  maxlength="12" id="contact_no" name="contact_no"  placeholder="301-123-4567" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" required value="{{@$row['contact_no']}}">

                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Phone </label>
                   <input type="tel" class="form-control"  maxlength="12" id="phone" name="phone"  placeholder="301-123-4567" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"  value="{{@$row['phone']}}">

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
            
            <div class="col-12 my-2">
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
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">City <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="city" name="city" required
                 placeholder="City Name" value="{{@$row['city']}}">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">State <span style="color:red;">*</span></label>
                    <input type="text" style="text-transform:uppercase" class="form-control" id="state" name="state" placeholder="State (2 alpha characters only)" maxlength="2" required pattern="([A-z0-9À-ž\s]){2,}" value="{{@$row['state']}}" >
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Zip Code <span style="color:red;">*</span></label>
                    <input type="number" class="form-control" id="zip_code" name="zip_code" maxlength="5" required placeholder="Five digit zip code" value="{{@$row['zip_code']}}">
                </div>
            </div>          
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</form>