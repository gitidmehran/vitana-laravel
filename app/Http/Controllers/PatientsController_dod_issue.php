<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patients;
use App\Models\Diagnosis;
use App\Models\SurgicalHistory;
use App\Models\User;
use App\Models\Questionaires;
use App\Models\Insurances;
use Auth,Validator,DB;

class PatientsController extends Controller
{
    protected $singular = "Patient";
    protected $plural   = "Patients";
    protected $action   = "/dashboard/patients";
    protected $view     = "patients.";

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
        $list = Patients::with('insurance','doctor','questionServey')->orderBy('id', 'DESC')->get()->toArray();
        
        $data['list'] = $list;
        // echo '<pre>';print_r($data['list']);die;
        
       /* echo "<pre>";
        

        
        print_r($data['list']);exit;*/
       // $doctorsName = User::all()->toArray();
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
        $doctorsName = User::where('role',2)->get()->toArray();
        $data['doctorsName'] = $doctorsName;

        $insurancesName = Insurances::all()->toArray();
        $data['insurancesName'] = $insurancesName;

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
            'last_name'   => 'required',
            'first_name'  => 'required',
            'contact_no'  => 'required',
            'dob'         => 'required',
            'age'         => 'required',
            'doctor_id'   => 'required',
            'gender'      => 'required|string',
            'disease'     => 'sometimes|required',
            'address'     => 'sometimes|required',
            'insurance_id' => 'sometimes|required',
            'city'     => 'sometimes|required',
            'state'     => 'sometimes|required',
            'zipCode'     => 'sometimes|required',
       ]);
       if($validator->fails()){
        return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
       };
       $input = $validator->valid();
       try {
            $input['created_user'] = Auth::id();
            $input['dob'] = date('Y-m-d',strtotime($input['dob']));
            if (!empty($input['dod'])) {
                $input['dod'] = date('Y-m-d',strtotime($input['dod']));
            }
            $patient = Patients::orderBy('id', 'desc')->first();
            if(!empty($patient)){
                $str = $patient->identity ?? "00000000";
                $a =+$str;
                $a = str_pad($a+1,8,'0',STR_PAD_LEFT);
                $input['identity'] = $a;
                
            }else{
                $input['identity'] = "00000001";
            }

            /* Patient Family History */
            /* $family_history = [
                'cancer' => $input['cancer'] ?? [],
                'diabetes' => $input['diabetes'] ?? [],
                'heart_disease' => $input['heart_disease'] ?? [],
                'hypertension' => $input['hypertension'] ?? [],
            ];

            $input['family_history'] = json_encode($family_history); */



            /* Gettin id after patient create */          
            $p_id = Patients::create($input)->id;
            
            // /* Store Disease in Table */
            // if(!empty($diagnosis)){
            //         $diagnosis = $input['diagnosis'];

            //     $data=[];
            //         foreach ($diagnosis as $key => $row)
            //         {
            //             $created_user = Auth::check()?Auth::id():1;
            //             $data2= [
            //                 'condition' => $row['condition'],
            //                 'description' => $row['description'],
            //                 'status' => $row['status'],
            //                 'patient_id' => $p_id,
            //                 'created_user' => $created_user
            //             ];
            //             array_push($data,$data2);
            //         }

            //         DB::table('diagnosis')->insert($data);
            // }

            // /* Store Madication in Table */
            // if(!empty($medication)) {
            //     $medication = $input['medication'];

            //     $data=[];
            //     foreach ($medication as $key => $row) {
            //         $created_user = Auth::check()?Auth::id():1;
            //         $data2= [
            //             'name' => $row['name'],
            //             'dose' => $row['dose'],
            //             'condition' => $row['condition'],
            //             'status' => "Active",
            //             'patient_id' => $p_id,
            //             'created_user' => $created_user
            //         ];
            //         array_push($data,$data2);
            //     }

            //     DB::table('medications')->insert($data);
            // }

            // /* Store surgical Hidtory in table */
            // if(!empty($surgical_history)) {

            //     $surgical_history = $input['surgical_history'];
                
            //     $data=[];
            //     foreach ($surgical_history as $key => $row) {
            //         $created_user = Auth::check()?Auth::id():1;
            //         $date_add = date('Y-m-d',strtotime($row['date']));
            //         $data2= [
                        
            //             'procedure' => $row['procedure'],
            //             'reason' => $row['reason'],
            //             'surgeon' => $row['surgeon'],
            //                 'date' => $date_add,
            //             'status' => "Active",
            //             'patient_id' => $p_id,
            //             'created_user' => $created_user
            //         ];
            //         array_push($data,$data2);
            //     }

            //     DB::table('surgical_history')->insert($data);
            // }

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
            'row'      => Patients::find($id)->toArray(),
            'gender_selection' => ['Male','Female']
        ];
        $data['doctorsName'] = User::where('role',2)->get()->toArray();
         
        $data['insurancesName'] = Insurances::all()->toArray();
         
        $data['SurgicalHistory'] = SurgicalHistory::all()->toArray();
         /*echo "<pre>";
         print_r($data['SurgicalHistory']);exit;*/
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
            'last_name'  => 'required',
            'first_name' => 'required',
            'contact_no'  => 'required|unique:patients,contact_no,'.$id,
            'dob'        => 'required',
            'age'        => 'required',
            'doctor_id'  => 'required',
            'insurance_id'   => 'required',
            'gender'     => 'required|string',
            'disease'    => 'sometimes|required',
            'address'    => 'sometimes|required',
            'city'       => 'sometimes|required',
            'state'      => 'sometimes|required',
            'zipCode'    => 'sometimes|required',
        ]);
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };
        $input = $validator->valid();

                $note = Patients::find($id);
        try {

            $input['dob'] = date('Y-m-d',strtotime($input['dob']));
            if (!empty($input['dod'])) {
                $input['dod'] = date('Y-m-d',strtotime($input['dod']));
            }
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
            $note = Patients::find($id);
            $note->delete();

            Questionaires::where('patient_id',$id)->delete();


            $response = array('success'=>true,'message'=>$this->singular.' Deleted!');
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        return response()->json($response);
    }
}
