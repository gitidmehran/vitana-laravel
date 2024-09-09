<form method="post" action="{{url($action)}}" class="make_file_ajax" enctype="multipart/form-data">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Add New {{@$singular}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-12 my-2">
                <div class="form-group">
                    <label for="name">Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
                </div>
            </div>
            <div class="col-12 my-2">
                <div class="form-group">
                    <label for="name">Short Name <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="short_name" name="short_name" placeholder="Short Name" required>
                </div>
            </div>            
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>