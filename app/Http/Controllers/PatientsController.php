<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patients;
use App\Models\Diagnosis;
use App\Models\SurgicalHistory;
use App\Models\User;
use App\Models\Questionaires;
use App\Models\Insurances;
use App\Models\Medications;
use Auth,Validator,DB;
use Carbon\Carbon;

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
    public function index(Request $request)
    {
        $active = $request->input("active")??1;
        $data = [
            'singular' => $this->singular,
            'plural'   => $this->plural,
            'action'   => $this->action,
            'page_title' => $this->singular.' List','active'=>$active
        ];
        $list = Patients::with('insurance','doctor','questionServey');
       if($active==2){
        $list = $list->onlyTrashed();
       }
        $list= $list->orderBy('id', 'DESC')->get()->toArray();

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
        $doctorsName = User::where('role',21)->get()->toArray();
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

            $family_history = $input['family_history'] ?? [];

            if(!empty($family_history))
            {
                 $family_history = [
                    'cancer' => $input['cancer'] ?? [],
                    'diabetes' => $input['diabetes'] ?? [],
                    'heart_disease' => $input['heart_disease'] ?? [],
                    'hypertension' => $input['hypertension'] ?? [],
                ];

                $input['family_history'] = json_encode($family_history); 
            }


            /* Gettin id after patient create */          
            $p_id = Patients::create($input)->id;
            /*echo "Patients Id is ";
            dd($p_id);*/
            $diagnosis = $input['diagnosis'] ?? [];
            // /* Store Disease in Table */
            if(!empty($diagnosis)){

                $data=[];
                    foreach ($diagnosis as $key => $row)
                    {
                        $created_user = Auth::check()?Auth::id():1;
                        $d_date = Carbon::now();
                        $data2= [
                            'condition' => $row['condition'],
                            'description' => $row['description'],
                            'status' => $row['status'],
                            'patient_id' => $p_id,
                            'created_user' => $created_user,
                            'created_at' => $d_date
                        ];
                        array_push($data,$data2);
                    }
                    if(!empty($input['condition']))
                    {
                        DB::table('diagnosis')->insert($data);
                    }else{
                        unset($data);     
                    }
            }

            // /* Store Madication in Table */
            $medication = $input['medication'] ?? [];
            if(!empty($medication)) {
                

                $data=[];
                foreach ($medication as $key => $row) {
                    $created_user = Auth::check()?Auth::id():1;
                    $d_date = Carbon::now();
                    $data2= [
                        'name' => $row['name'],
                        'dose' => $row['dose'],
                        'condition' => $row['condition'],
                        'status' => "1",
                        'patient_id' => $p_id,
                        'created_user' => $created_user,
                        'created_at' => $d_date
                    ];
                    array_push($data,$data2);
                }
                if(!empty($input['name']))
                    {
                        DB::table('medications')->insert($data);
                    }else{
                        unset($data);     
                    }
            }

            // /* Store surgical Hidtory in table */
            $surgical_history = $input['surgical_history'] ?? [];
            if(!empty($surgical_history)) {

               // $surgical_history = $input['surgical_history'];
                
                $data=[];
                foreach ($surgical_history as $key => $row) {
                    $created_user = Auth::check()?Auth::id():1;
                    $date_add = date('Y-m-d',strtotime($row['date']));
                    $d_date = Carbon::now();
                    $data2= [
                        
                        'procedure' => $row['procedure'],
                        'reason' => $row['reason'],
                        'surgeon' => $row['surgeon'],
                        'date' => $date_add,
                        'status' => "1",
                        'patient_id' => $p_id,
                        'created_user' => $created_user,
                        'created_at' => $d_date
                    ];
                    array_push($data,$data2);
                }
                if(!empty($input['procedure']))
                    {
                        DB::table('surgical_history')->insert($data);
                    }else{
                        unset($data);     
                    }
            }

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
       
        $row = Patients::find($id)->toArray();
        $data = [
            'singular' => $this->singular,
            'plural'   => $this->plural,
            'action'   => $this->action,
            'row'      => $row,
            'gender_selection' => ['Male','Female']
        ];
        $data['doctorsName'] = User::where('role',21)->get()->toArray();
        $data['insurancesName'] = Insurances::all()->toArray();
        $data['SurgicalHistory'] = SurgicalHistory::where('patient_id',$id)->get()->toArray();
        $data['diagnosis'] = Diagnosis::where('patient_id',$id)->get()->toArray();

        $data['medications'] = Medications::where('patient_id',$id)->get()->toArray();

        $data['family_history'] = json_decode($row['family_history'],TRUE);
         /*echo "<pre>";
         print_r($data['diagnosis']);exit;*/
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
            'contact_no'  => 'required:patients,contact_no,'.$id,
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
 //return response()->json($input);

$created_user = Auth::check()?Auth::id():1;
 $surgical_history = $input['surgical_history'] ?? [];
 $history = [];
   foreach($surgical_history as $key => $value){
    $check  = SurgicalHistory::where(['procedure'=>$value['procedure'],'patient_id'=>$id])->count();
    $d_date = Carbon::now();
    if($check==0){
$date_add = date('Y-m-d',strtotime($value['date']));
        $history[] = [
            'patient_id' => $id,
            'procedure' => $value['procedure'],
            'reason' => $value['reason'],
            'date' => $date_add,
            'surgeon' => $value['surgeon'],
            'created_user' => $created_user,
            'updated_at' => $d_date
        ];
    }
   }
   if(!empty($history)){
    SurgicalHistory::insert($history);
   }

// Medications Table Update Data
   $medication = $input['medication'] ?? [];
   $medications = [];
   foreach($medication as $key => $value){
    $check  = Medications::where(['name'=>$value['name'],'patient_id'=>$id])->count();
    $d_date = Carbon::now();
    if($check==0){

        $medications[] = [
            'patient_id' => $id,
            'name' => $value['name'],
            'dose' => $value['dose'],
            'condition' => $value['condition'],
            'created_user' => $created_user,
            'updated_at' => $d_date,
        ];
    }
   }
   if(!empty($medications)){
    Medications::insert($medications);
   }



   // Diagnosis Table Update Data
   $diagnos = $input['diagnosis'] ?? [];
   $diagnosis = [];
   foreach($diagnos as $key => $value){
    $check  = Diagnosis::where(['condition'=>$value['condition'],'patient_id'=>$id])->count();
    $d_date = Carbon::now();
    if($check==0){

        $diagnosis[] = [
            'patient_id' => $id,
            'condition' => $value['condition'],
            'description' => $value['description'],
            'status' => $value['status'],
            'created_user' => $created_user,
            'updated_at' => $d_date,
        ];
    }
   }
   if(!empty($diagnosis)){
    Diagnosis::insert($diagnosis);
   }


   /* Patient Family History */
             $family_history = [
                'cancer' => $input['cancer'] ?? [],
                'diabetes' => $input['diabetes'] ?? [],
                'heart_disease' => $input['heart_disease'] ?? [],
                'hypertension' => $input['hypertension'] ?? [],
            ];

            $input['family_history'] = json_encode($family_history); 

 /*exit;  */           
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


    public function surgical_history_destroy($id)
    {
        $deleted_user = Auth::id();
        $d_date = Carbon::now();//date("Y-m-d h:i:s a", time());
        $data= [
                    'deleted_user' => $deleted_user,
                    'deleted_at' => $d_date
                ];
        

        DB::table('surgical_history')->where('id', $id)->limit(1)->update($data);      
        return response()->json([
            'success' => 'Record deleted successfully!'
        ]);
       //dd($id);
        /*SurgicalHistory::find($id)->delete($id);
      
        return response()->json([
            'success' => 'Record deleted successfully!'
        ]);*/
    }
    public function surgical_history_spellMistake($id)
    {   
        $deleted_user = Auth::id();
        $d_date = Carbon::now();//date("Y-m-d h:i:s a", time());
        $data= [
                    'status' => "0",
                    'deleted_user' => $deleted_user,
                    'deleted_at' => $d_date
                ];
        

        DB::table('surgical_history')->where('id', $id)->limit(1)->update($data);      
        return response()->json([
            'success' => 'Record deleted successfully!'
        ]);
    }

    //

    public function medication_destroy($id)
    {
        $deleted_user = Auth::id();
        $d_date = Carbon::now();//date("Y-m-d h:i:s a", time());
        $data= [
                    'deleted_user' => $deleted_user,
                    'deleted_at' => $d_date
                ];
        

        DB::table('medications')->where('id', $id)->limit(1)->update($data);      
        return response()->json([
            'success' => 'Record deleted successfully!'
        ]);
       //dd($id);
        /*Medications::find($id)->delete($id);
      
        return response()->json([
            'success' => 'Record deleted successfully!'
        ]);*/
    }
    public function medication_spellMistake($id)
    {   
        $deleted_user = Auth::id();
        $d_date = Carbon::now();//date("Y-m-d h:i:s a", time());
        $data= [
                    'status' => "0",
                    'deleted_user' => $deleted_user,
                    'deleted_at' => $d_date
                ];
        

        DB::table('medications')->where('id', $id)->limit(1)->update($data);      
        return response()->json([
            'success' => 'Record deleted successfully!'
        ]);
    }

    //

    public function diagnosis_destroy($id)
    {
        $deleted_user = Auth::id();
        $d_date = Carbon::now();//date("Y-m-d h:i:s a", time());
        $data= [
                    'deleted_user' => $deleted_user,
                    'deleted_at' => $d_date,

                ];
        

        DB::table('diagnosis')->where('id', $id)->limit(1)->update($data);      
        return response()->json([
            'success' => 'Record deleted successfully!'
        ]);
       //dd($id);
        /*Diagnosis::find($id)->delete($id);
      
        return response()->json([
            'success' => 'Record deleted successfully!'
        ]);*/
    }
    public function diagnosis_spellMistake($id)
    {   
        $deleted_user = Auth::id();
        $d_date = Carbon::now();//date("Y-m-d h:i:s a", time());
        $data= [
                    'display' => "0",
                    'deleted_user' => $deleted_user,
                    'deleted_at' => $d_date,

                ];
        

        DB::table('diagnosis')->where('id', $id)->limit(1)->update($data);      
        return response()->json([
            'success' => 'Record deleted successfully!'
        ]);
    }

    public function status_change($id)
    {   
        $selectedValue = $_POST['selected'];

        $deleted_user = Auth::id();
        $d_date = Carbon::now();//date("Y-m-d h:i:s a", time());
        if ($selectedValue =="Inactive") 
        {
          
            $data= [
                    /*'deleted_user' => $deleted_user,*/
                    'deleted_at' => $d_date,
                ];
            DB::table('patients')->where('id', $id)->limit(1)->update($data);      
            return response()->json([
                'success' => 'Record deleted successfully!'
            ]);
        }
        else if($selectedValue =="Active")
        {
            $data= [
                    'deleted_at' => NULL,
                ];
            DB::table('patients')->where('id', $id)->limit(1)->update($data);      
            return response()->json([
                'success' => 'Record deleted successfully!'
            ]);
        }
    }

     

    public function Inactive_patients()
    {

/*dd("Inactive_patients");*/
        $selectedValue = $_POST['selected'];
        if(!empty($selectedValue))
        {
            
            $data = [
            'singular' => $this->singular,
            'plural'   => $this->plural,
            'action'   => $this->action,
            'page_title' => $this->singular.' List'
        ];
       
        $list = Patients::with('insurance','doctor','questionServey')->onlyTrashed()->orderBy('id', 'DESC')->get()->toArray();
        
        $data['list'] = $list;
        return view($this->view.'list',$data);

            //dd($selectedValue);
        }else{
            dd($selectedValue);
        }




        /*$deleted_user = Auth::id();
        $d_date = Carbon::now();//date("Y-m-d h:i:s a", time());
        $data= [
                    'deleted_user' => $deleted_user,
                    'deleted_at' => $d_date,

                ];
        

        DB::table('diagnosis')->where('id', $id)->limit(1)->update($data);      
        return response()->json([
            'success' => 'Record deleted successfully!'
        ]);*/
       
    }
}
