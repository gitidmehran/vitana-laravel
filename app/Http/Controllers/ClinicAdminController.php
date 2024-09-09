<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ClinicUser;

use Auth,Validator,DB;
use Carbon\Carbon;


class ClinicAdminController extends Controller
{
    protected $singular = "Clinic Admin";
    protected $plural   = "ClinicAdmins";
    protected $action   = "/dashboard/clinicAdmins";
    protected $view     = "clinicAdmins.";

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            'singular' => $this->singular,
            'plural'   => $this->plural,
            'action'   => $this->action,
            'page_title' => $this->singular.' List'
        ];
       /* $list = User::with('user','clinics12');
        dd($list);*/
        $data['roles'] = Config('constants.roles');
        $list = User::with('clinicUser.clinic')->get()->toArray();
        $data['list'] = $list;
        
        /*$clinic = DB::table('clinic_users')
            ->join('clinics', 'clinic_users.clinic_id', '=', 'clinics.id')
            ->select('clinics.*', 'clinics.name', 'clinic_users.user_id',)
            ->get()->toArray();*/
        return view($this->view.'list',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [
            'singular' => $this->singular,
            'plural'   => $this->plural,
            'action'   => $this->action,
        ];


        $data['clinicName'] = DB::table('clinics')->get()->toArray();
        //dd($data['clinicName']);
        return view($this->view.'create',$data);
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
       ]);
       if($validator->fails()){
        return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
       };
       $input = $validator->valid();
       try {
            $input['created_user'] = Auth::id();
            $input['mid_name'] = $request->mid_name;
            //$c_id = DB::table('clinics')->latest('created_at')->first()->id;
            $input['role'] = $request->role;    
            $input['password'] = Hash::make($request->password);

            $user = User::create($input);

            $clinic_user['created_user'] = Auth::id();
            $clinic_user['clinic_id'] = $request->clinic_id;
            $clinic_user['user_id'] = $user['id'];
           
           ClinicUser::create($clinic_user);
            //User::create($input);
            $response = array('success'=>true,'message'=>$this->singular.' Added Successfully','action'=>'reload');
       } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
       }
       return response()->json($response);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = [
            'singular' => $this->singular,
            'plural'   => $this->plural,
            'action'   => $this->action,
            'row'      => User::find($id)->toArray(),
            'clinic_user'      => ClinicUser::where('user_id',$id)->first(),
            
        ];

        $data['clinicName'] = DB::table('clinics')->get()->toArray();
       // dd($data['row']);
        return view($this->view.'edit',$data);
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
            'contact_no'  => 'required|unique:users,contact_no,'.$id,
            'clinic_id'   => 'required',
            'role'        => 'required',
            'address'     => 'sometimes|required',
        ]);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
        $input = $validator->validated();
      /*  $user = User::find($id);*/
        try {
            /*$user->update($input);*/

            ClinicUser::where('user_id', $id)->update(['clinic_id' => $input['clinic_id']]);
            unset($input['clinic_id']);
            User::where('id',$id)->update($input);


            $response = array('success'=>true,'message'=>$this->singular.' Updated Successfully','action'=>'reload');
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
            $response = array('success'=>true,'message'=>$this->singular.' Deleted!');
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        return response()->json($response);
    }
}
