<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use App\Models\Physicians;
use App\Models\Specialists;
use Auth,Validator;
use Illuminate\Database\Schema\Blueprint;



class PhysiciansController extends Controller
{
    protected $singular = "Physician";
    protected $plural   = "Physicians";
    protected $action   = "/dashboard/physicians";
    protected $view     = "physicians.";
    protected $role = 5;

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
        $list = Physicians::where('role',$this->role)->get()->toArray();       
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
        $physiciansSpeciality = Specialists::all()->toArray();
         $data['physiciansSpeciality'] = $physiciansSpeciality;
         //dd($data['physiciansSpeciality']);
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
            
            'gender'      => 'required|string',
            'speciality'  => 'sometimes|required',
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
            Physicians::create($input);
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
            'row'      => Physicians::find($id)->toArray(),
            'gender_selection' => ['Male','Female']
        ];
        $physiciansSpeciality = Specialists::all();
       
         $data['physiciansSpeciality'] = $physiciansSpeciality;
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
            'contact_no'  => 'required|unique:physicians,contact_no,'.$id,
            
            'gender'      => 'required|string',
            'speciality'     => 'sometimes|required',
            'address'     => 'sometimes|required',
        ]);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
        $input = $validator->valid();
        $note = Physicians::find($id);
        //$input['password'] = Hash::make($request->password);
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
            $note = Physicians::find($id);
            $note->delete();
            $response = array('success'=>true,'message'=>$this->singular.' Deleted!');
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        return response()->json($response);
    }












    /*  public function autocomplete(Request $request)
    {
        $data = User::select("last_name")
                    ->where('last_name', 'LIKE', '%'. $request->get('query'). '%')
                    ->get();
     
        return response()->json($data);
    }*/
}
