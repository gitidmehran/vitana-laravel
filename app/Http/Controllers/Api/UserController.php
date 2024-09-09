<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use Illuminate\Http\Request;

use App\Helpers\Utility;

use App\Models\User;
use App\Models\ClinicUser;

use Auth,Validator,Hash,DB;

class UserController extends Controller
{
    protected $per_page = '';
    
    public function __construct(){
        $this->per_page = Config('constants.perpage_showdata');
    }

    //Show all users data
    public function index(Request $request)
    {
        try {
            $roles = Config('constants.roles');
            $query  = new User();
            
            /*start*/
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->get('search');
                $query = $query->where('first_name', 'like', '%' . $search . '%')->orWhere('last_name', 'like', '%' . $search . '%');
            }


            $query = $query->paginate($this->per_page);
            $total = $query->total();
            $current_page = $query->currentPage();
            $result = $query->toArray();
            
            $list = [];
            foreach($result['data'] as $key => $val){
                $list[] = [
                    'id' => $val['id'],
                    'name' => $val['name'],
                    'first_name' => $val['first_name'],
                    'mid_name' => $val['mid_name'],
                    'last_name' => $val['last_name'],
                    'contact_no' => $val['contact_no'],
                    'gender' => $val['gender'],
                    'email' => $val['email'],
                    'role' => $val['role'],
                    'role_name' => @$roles[$val['role']]
                ];
            }
            
            $response = [
                'success' => true,
                'message' => 'User Data Retrived Successfully',
                'current_page' => $current_page,
                'total_records' => $total,
                'per_page'   => $this->per_page,
                'roles_data' => $roles,
                'data' => $list
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }

        return response()->json($response);
    }

    //Create New user 
    public function store(Request $request)
    {
        $roles = Config('constants.roles');
        
        $validator = Validator::make($request->all(),[
            'first_name'  => 'required',
            'last_name'   => 'required',
            'contact_no'  => 'required',
            'email'  => 'required|unique:users',
            'gender'      => 'required|string',
       ]);

        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };


        $input = $validator->valid();
        try {

            /* Using Utility to add data in clinic User functions */
            $input = Utility::appendRoles($input);

            // $input['created_user'] = Auth::id();
            $input['role'] = $request->role;
           // $input['physician_npi_num'] = $request->physician_npi_num;
            $input['password'] = Hash::make($request->password);
            $user  = User::create($input);

            if (@$input['clinic_id']) {
                /* Creating user on behalf of CLINIC ID */
                $clinicData = ['clinic_id' => implode(',', $input['clinic_id']), 'user_id'=> $user['id'], 'created_user' => $input['created_user']];
                ClinicUser::create($clinicData);
            }
            

            $user['role_name'] = @$roles[$user['role']];

            $response = [
                'success' => true,
                'message' => 'New User Create Successfully',
                'data' => $user
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        
        return response()->json($response);
    }

    /* Edit User */
    public function edit($id)
    {
        try {
            $note = User::find($id);

            if (empty($note)) {
                $response = [
                    'success' => false,
                    'message' => 'Sorry User Not Find',
                    'data' => $note
                ];
            }
            $response = [
                'success' => true,
                'message' => 'User data Successfully',
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
        $roles = Config('constants.roles');
        $validator = Validator::make($request->all(),[
            'first_name'        => 'required',
            'last_name'        => 'required',
            'contact_no'  => 'required',
            'gender'      => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        }

        $input = $validator->valid();
        $note = User::find($id);

        if ($request->has('new_password') && !empty($request->new_password)) {
            $input['password'] = Hash::make($request->new_password);
        }
        
        /* Using Utility to add data in clinic User functions */
        $input = Utility::appendRoles($input);

        if ($input['clinic_id'] != "") {
            $input['clinic_id'] = implode(',', $input['clinic_id']);
        }

        try {
            
            $note->update($input);
            $note['role_name'] = @$roles[$note['role']];
            
            $response = [
                'success' => true,
                'message' => 'Update User Record Successfully',
                'data' => $note
            ];
       } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
       }

       return response()->json($response);
    }
    
    //Delete user
    public function destroy($id)
    {
        try {
            $note = user::find($id);
            $note->delete();
            
            $response = [
                'success' => true,
                'message' => 'User Deleted Successfully',
                'data' => $note
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }

        return response()->json($response);
    }

    /* Logout */
    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }

}
