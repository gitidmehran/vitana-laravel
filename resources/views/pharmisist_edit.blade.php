<form method="post" action="{{url($action.'/'.$row['id'])}}" class="make_file_ajax" enctype="multipart/form-data">
	 @method('PUT')
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Update {{@$singular}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-12 my-2">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Name" required value="{{@$row['name']}}">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="Email" class="form-control" id="email" name="email" placeholder="Enter Your Email" value="{{@$row['email']}}">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter Your Password" value="{{@$row['password']}}">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="role">Role</label>
                       @php
    $userRole = ['Admin'=>'1', 'User'=>'2', 'Pharmacist'=>'3'];
@endphp

                    <select name="role" id="role" class="form-control" value= "{{@$row['role']}}">

                   <!--  @foreach($userRole as $key => $val)
                            <option value="{{$val}}" @if($val==$row['role']) selected @endif>{{$key}}</option>
                    @endforeach -->
                         <option value="1" @if(1==$row['role']) selected @endif>Admin</option>
                        <option value="2" @if(2==$row['role']) selected @endif>User</option>
                        <option value="3" @if(3==$row['role']) selected @endif>Pharmacist</option> 
                        
                    </select>
                </div>
            </div>
            <!-- <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Disease</label>
                    <input type="text" class="form-control" id="disease" name="disease" placeholder="Patient Disease" value="{{@$row['disease']}}">
                </div>
            </div>
            <div class="col-12 my-2">
                <div class="form-group">
                    <label for="name">Address</label>
                    <textarea class="form-control" name="address" id="address" rows="3">{{@$row['address']}}</textarea>
                </div>
            </div> -->           
        </div>
    </div>
    <div class="modal-footer">
    	<button type="submit" class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</form>