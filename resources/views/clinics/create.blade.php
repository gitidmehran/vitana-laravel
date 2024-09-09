
<form method="post" action="{{url($action)}}" class="make_file_ajax" enctype="multipart/form-data">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Add New {{@$singular}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="first_name">Short Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="short_name" name="short_name" placeholder="Short Name" required>
                </div>
            </div>

            
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Contact <span style="color:red;">*</span></label>
                     <input type="tel" class="form-control"  maxlength="12" id="contact_no" name="contact_no"  placeholder="301-123-4567" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" required>
                   
                </div>
            </div> 
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Phone</label>
                     <input type="tel" class="form-control"  maxlength="12" id="phone" name="phone"  placeholder="301-123-4567" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}">
                   
                </div>
            </div>  
            <!-- <div class="col-12 my-2">
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
            </div>         -->      
            
             <div class="col-12 my-2">
                <div class="form-group">
                    <label for="address_2">Address Line 1 <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Address Line">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="address_2">Address Line 2</label>
                    <input type="text" class="form-control" id="address_2" name="address_2" placeholder="Address Line 2">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">City <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="city" name="city" placeholder="City Name" required>
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">State <span style="color:red;">*</span></label>
                    <input type="text" style="text-transform:uppercase" class="form-control" id="state" name="state" placeholder="State (2 alpha characters only)" required maxlength="2">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Zip Code <span style="color:red;">*</span></label>
                    <input type="tel" class="form-control" id="zip_code" maxlength="5" name="zip_code" pattern="[0-9]{5}" placeholder="Five digit zip code" required>
                </div>
            </div>          
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</form>