<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClinicCollection;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Auth, Validator, Hash;
class ClinicController extends Controller
{
    protected $per_page = '';
	
    public function __construct(){
	    $this->per_page = Config('constants.perpage_showdata');
	}
    
    
    //Show all Clinics data
    public function index(Request $request)
    {
        try {

            $query  = new Clinic();

            /*start*/
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->get('search');
                $query = $query->where('name', 'like', '%' . $search . '%');
            }

            $query = $query->paginate($this->per_page);
            $total = $query->total();
            $current_page = $query->currentPage();
            $result = $query->toArray();
            //return response()->json($result);
            $list = [];
            /*end*/
            foreach ($result['data'] as $key => $val) {
                //$list[$key]['role']= $roles[$row['role']];
                $list[] = [
                    'id' => $val['id'],
                    'name' => $val['name'],
                    'short_name' => $val['short_name']
                ];
            }

            /* foreach($list as $key1 => $row){
                $list[$key1]['role']= $roles[$row['role']];
            }*/
            $response = [
                'success' => true,
                'message' => 'Clinics Data Retrived Successfully',
                'current_page' => $current_page,
                'total_records' => $total,
                'per_page'   => $this->per_page,
                'data' => $list
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }

    //Create New user 
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required',
            'short_name'   => 'required',
            'contact_no'  => 'required',
            'address'     => 'required',
            'city'     => 'required',
            'state'     => 'required',
            'zip_code'     => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };
        $input = $validator->valid();
        try {
            // $input['created_user'] =  Auth::id();
           
            $user  = Clinic::create($input);

            $response = [
                'success' => true,
                'message' => 'New Clinic Create Successfully',
                'data' => $user
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }

    // Edit existing clinic
    public function edit($id)
    {
        try {
            $note = Clinic::find($id);
            if ($note) 
            {
               $response = [
                'success' => true,
                'message' => 'Sorry Clinic Not Find',
                'data' => $note
                ];
            }

            $response = [
                'success' => true,
                'message' => 'Clinic data Successfully',
                'data' => $note
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        return response()->json($response);
    }


    //Update user data
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required',
            'short_name'        => 'required',
            'contact_no'  => 'required',
            'address'     => 'sometimes|required',
            'city'     => 'sometimes|required',
            'state'     => 'sometimes|required',
            'zip_code'     => 'sometimes|required',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };
        $input = $validator->valid();
        $note = Clinic::find($id);
        //$input['password'] = Hash::make($request->password);
        try {
            //$user  = User::update($input);

            $note->update($input);
            $response = [
                'success' => true,
                'message' => 'Update Clinic Record Successfully',
                'data' => $note
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }

    //Delete user 
    public function destroy($id)
    {

        try {
            $note = clinic::find($id);
            $note->delete();

            $response = [
                'success' => true,
                'message' => 'Clinic Deleted Successfully',
                'data' => $note
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }
}
