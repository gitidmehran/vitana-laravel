<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use App\Models\User;
use Auth,Validator;

class PharmacistsController extends Controller
{
    protected $singular = "User";
    protected $plural   = "Users";
    protected $action   = "/dashboard/users";
    protected $view     = "pharmacists.";

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
        $data['roles'] = Config('constants.roles');
        $list = user::where('role','!=',2)->get()->toArray();
        $data['list'] = $list;
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
            'gender_selection' => ['Male','Female']
        ];
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
            'gender'      => 'required|string',
            'address'     => 'sometimes|required',
       ]);
       if($validator->fails()){
        return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
       };
       $input = $validator->valid();
       try {
            $input['created_user'] = Auth::id();
            $input['role'] = $request->role;
            $input['password'] = Hash::make($request->password);
            User::create($input);
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
            'gender_selection' => ['Male','Female']
        ];
        
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
            'first_name'        => 'required',
            'last_name'        => 'required',
            'contact_no'  => 'required|unique:users,contact_no,'.$id,
            
            'gender'      => 'required|string',
            'address'     => 'sometimes|required',
        ]);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
        $input = $validator->valid();
        $note = User::find($id);
       // $input['password'] = Hash::make($request->password);
        try {
            $note->update($input);
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
