<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClinicAdminCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\Utility;

use App\Models\Clinic;
use App\Models\User;
use App\Models\ClinicUser;
use App\Models\Programs;

use Auth,Validator,DB;
use Carbon\Carbon;

class ClinicAdminController extends Controller
{
    protected $per_page = '';
	public function __construct(){
	    $this->per_page = Config('constants.perpage_showdata');
	}


    //Show all users data
    public function index(Request $request)
    {
        $roles = Config('constants.roles');

        $clinic_id = @$request->input('clinic_id') ?? "";

        $query = User::with('clinic')->OfClinicID($clinic_id);
        
        if(Auth::user()->role=="11"){
            $query->where('role','>', 11);
        }
        
        /* For Search Query in clinic users */
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->get('search');
            $query = $query->where('first_name', 'like', '%' . $search . '%')->orWhere('last_name', 'like', '%' . $search . '%');
        }

        $query = $query->orderBy('id', 'DESC');
        $query = $query->paginate($this->per_page);
        $total = $query->total();
        $current_page = $query->currentPage();
        $result = $query->toArray();

        $clinics = Clinic::get()->toArray();
        
        $clinics_data = []; 
        foreach($clinics as $key => $val) {
            $clinics_data[] = [
                'id' => $val['id'],
                'name' => $val['name'],
            ];
        }

        $list = [];
        foreach($result['data'] as $key => $val) {

            $clinicIds = explode(",", $val['clinic_id']);
            $programIds = explode(',', $val['program_id']);
            
            $clinicArray = array();
            foreach ($clinics_data as $clinickey => $value) {
                if (in_array($value['id'], $clinicIds)) {
                    $clinicArray[$value['id']] = $value['name'];
                }
            }

            $list[] = [
                'id' => $val['id'],
                'name' => $val['name'],
                'first_name' => $val['first_name'],
                'mid_name' => $val['mid_name'],
                'last_name' => $val['last_name'],
                'email' => $val['email'],
                'clinic' => $clinicArray,
                'clinic_id' => $clinicIds,
                'clinic_name' => array_values($clinicArray),
                'program_id' => $programIds,
                'contact_no' => $val['contact_no'],
                'role' => $val['role'],
                'degree' => $val['degree'],
                'role_name' => @$roles[$val['role']],
            ];
        }

        $program_list = $programs = Programs::when(!empty($clinic_id), function($query) use ($clinic_id) {
            $query->whereRaw("FIND_IN_SET('$clinic_id', clinic_id)");
        })->get()->toArray();
        
        $response = [
            'success' => true,
            'message' => 'ClinicAdmins Data Retrived Successfully',
            'current_page' => $current_page,
            'total_records' => $total,
            'per_page'   => $this->per_page,
            'roles_data' => $roles,
            'clinics' =>$clinics_data,
            'programs' => @$program_list ?? [],
            'data' => $list
        ];
        try {
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        
        return response()->json($response);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'first_name'  => 'required',
            'last_name'   => 'required',
            'contact_no'  => 'required',
            'email'  => 'required|unique:users',
            'address'     => 'sometimes|required',
            'password'    => 'required',
        ]);

        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };

        $input = $validator->valid();

        try {
            /* Using Utility to add data in clinic User functions */
            $input = Utility::appendRoles($input);

            $input['clinic_id'] = implode(',', $input['clinic_id']);
            $input['mid_name'] = $request->mid_name;
            $input['role'] = $request->role;    
            $input['password'] = Hash::make($request->password);
            $input['degree'] = @$request->degree ?? NULL;

            if ($request->has('program_id')) {
                $input['program_id'] = implode(',', $request->input('program_id'));
            }

            $user = User::create($input);

            $data = [
                'first_name' => $user['first_name'],
                'mid_name' => $user['mid_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'password' =>$user['password'],
                'address' =>$user['address'],
                'contact_no'  => $user['contact_no'],
                'role' =>$user['role'],
                'degree' =>$user['degree'],
                'clinic_id'=>$user['clinic_id'],
                'created_user' => $user['created_user'],
            ];
            $response = [
                'success' => true,
                'message' => 'New Clinic User Created Successfully',
                'data' => $data
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }
        return response()->json($response);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'first_name'  => 'required',
            'last_name'   => 'required',
            'mid_name'    => '',
            'email'    => 'required',
            'contact_no'  => 'required',
            'clinic_id'   => 'required',
            'role'        => 'required',
        ]);

        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
        
        $input = $validator->validated();
        
        try {
            /* Using Utility to add data in clinic User functions */
            $input = Utility::appendRoles($input);
            if(@$input['clinic_id']) {
                $input['clinic_id'] = implode(',', $input['clinic_id']);
            }
            if ($request->has('password')) {
                $input['password'] = Hash::make($request->password);
            }
            
            if ($request->has('program_id')) {
                $input['program_id'] = implode(',', $request->input('program_id'));
            }

            $input['degree'] = @$request->degree ?? NULL;

	        User::where('id',$id)->update($input);
	        $user = User::where('id',$id)->first();
            
            $note = [
                'first_name' => $user['first_name'],
                'mid_name' => $user['mid_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'clinic_id' => $user['clinic_id'],
                'clinic_id' => $user['program_id'],
                'contact_no'  => $user['contact_no'],
                'role' =>$user['role'],
                'created_user' => $user['created_user'],
                'created_at' => $user['created_at'],
                'updated_at' =>$user['updated_at']
            ];

            $response = [
                'success' => true,
                'message' => 'Clinic User Updated Successfully',
                'data' => $note
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
       return response()->json($response);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $note = user::find($id);
            $note->delete();
            $response = [
                'success' => true,
                'message' => 'Clinic User Deleted Successfully',
                'data' => $note
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        return response()->json($response);
    }
    

    /**
     * Fetch the specified resource from storage.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $note = clinicUser::find($id);
            if ($note) 
            {
               $response = [
                'success' => true,
                'message' => 'Sorry clinic User Not Find',
                'data' => $note
                ];
            }
            $response = [
                'success' => true,
                'message' => 'clinic User Data Fetch Successfully',
                'data' => $note
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }


    /**
     * Update passwprd against the specified resource in storage.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function updatePassword(Request $request, $id)
    {
        $input = $request->all();
        
        $validator = Validator::make($request->all(),[
            'new_password'  =>  'required|required_with:confirm|same:confirm',
            'confirm'  =>  'required'
        ]);

        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
        try {
            $update['password'] = Hash::make($request->new_password);
            $update['password_updated'] = true;
            $status = User::where('id',$id)->update($update);

            $response = [
                'success' => true,
                'message' => 'Password Updated Successfully',
            ];
            
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    public function testUpdate(Request $request){
        $update['password'] = Hash::make('123123');

        $data=User::where('email', 'zainarain.7666@gmail.com')->update($update);
        return response()->json($data, 200);
    }
}
