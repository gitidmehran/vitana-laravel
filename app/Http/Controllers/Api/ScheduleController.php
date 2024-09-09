<?php

namespace App\Http\Controllers\Api;

use App\Models\Schedule;
use App\Models\Patients;
use App\Models\Questionaires;
use Illuminate\Http\Request;
use App\Http\Resources\ScheduleCollection;
use App\Http\Controllers\Controller;
use Validator;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    protected $per_page = '';
    public function __construct(){
        $this->per_page = Config('constants.perpage_showdata');
    }
   
    /* List of All Stored Schedules */
    public function index(Request $request)
    {
        try {
            $query  = new Schedule();
            //return response()->json($query);
            $from = Carbon::now()->subMonths(6);
            $to = Carbon::now()->subMonths(12);



            $Patients_data = Patients::select('id','first_name','mid_name','Last_name','doctor_id')->get()->toArray();


            $doctor_id = $request->input("doctor_id") ?? '';
            $patient_id = $request->input("patient_id") ?? '';
            $clinic_id = $request->input("clinic_id") ?? '';
            $insurance_id = $request->input("insurance_id") ?? '';
            $total_scheduled = $request->input("total_scheduled") ?? '';
            $total_scheduled_A12 = $request->input("total_scheduled_A12") ?? '';
            $total_refused = $request->input("total_refused") ?? '';


            $query = $query::with('patient');
            // return response()->json();

            // $query = Schedule::where('status','!=' 'Done');
            if(!empty($total_scheduled_A12))
            {
               $query = $query->where("last_visit",">", $to);
            }
    
            if(!empty($total_refused)){
                $query = $query->onlyTrashed();
            }
        
            if (!empty($total_scheduled)) 
            {
                $query = $query->where('deleted_at',Null);
            }

            if(!empty($doctor_id)){
                $query->where('doctor_id',$doctor_id);
            }

            if(!empty($insurance_id)){
                $query->where('insurance_id',$insurance_id);
            }

            if(!empty($clinic_id)){
                $query->where('clinic_id',$clinic_id);
            }

            if(!empty($patient_id)){
                $query->where('patient_id',$patient_id);
            }


            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->get('search');
                $query = $query->whereHas('patient', function($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            }


            $query = $query->paginate($this->per_page);
        	//$query = $query->paginate($per_page);
            $total = $query->total();
            $current_page = $query->currentPage();
            $result = $query->toArray();

            $list = [];

            

            foreach($result['data'] as $key => $val){
                
                $list[] = [
                    'id' => $val['id'],
                    'patient_id' => $val['patient_id'],
                    'doctor_id' => $val['doctor_id'],
                    'clinic_id' => $val['clinic_id'],
                    'insurance_id' => $val['insurance_id'],
                    'patient_name' => $val['patient']['name'] ?? '',
                    'status' => $val['status'],
                    'scheduled_date' => $val['scheduled_date'],
                    'scheduled_time' => $val['scheduled_time']
                ];
            }

            $response = [
                'success' => true,
                'message' => 'Schedule Data Retrived Successfully',
                'current_page' => $current_page,
                'total_records' => $total,
                'per_page'   => $this->per_page,
                'data' => $list,
                'patients_data' => $Patients_data
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }

        return response()->json($response);
    }
    
    
    /* Store Patient Visit Schedule against AWV program only */
    public function store(Request $request)
    {
        $last_AWV = Questionaires::where('patient_id',$request->patient_id)->where('program_id','1')->orderBy('id','Desc')->first();
        $patient_data = patients::where('id',$request->patient_id)->orderBy('id','Desc')->first();

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required'
        ]);

        try {
            $schedule = new Schedule();

            if (!empty($last_AWV)) {
                $schedule->last_visit = $last_AWV->created_at;
            }else{
               $schedule->last_visit = Carbon::now(); 
            }

            $schedule->patient_id = $request->patient_id;
            $schedule->doctor_id = $patient_data->doctor_id;
            $schedule->clinic_id = $patient_data->clinic_id;
            $schedule->program_id = $last_AWV->program_id;
            $schedule->insurance_id = $patient_data->insurance_id;
            $schedule->status = $request->status;
            $schedule->scheduled_time = $request->scheduled_time;
            $schedule->scheduled_date = date('Y-m-d',strtotime($request->scheduled_date));
            $schedule->save();
            $schedule['patient_name'] = $patient_data['name'];

            return response()->json([
                'success' => true,
                "message" => "Schedule has been saved",
                'data' => $schedule
            ]);
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }







    public function update(Request $request, $id)



    {



        $validator = Validator::make($request->all(), [



            'patient_id' => 'required'



        ]);



        try {



            $schedule = Schedule::with('patient')->find($id);

            if (!$schedule) {



                return response()->json([



                    'success' => false,



                    "message" => "There is no record with this id"



                ]);



            } else {               



                $schedule->status = $request->status;



                $schedule->scheduled_time = $request->scheduled_time;



                $schedule->scheduled_date = $request->scheduled_date;





               $schedule->update();



$schedule['patient_name'] = $schedule['patient']['name']?? '';

if(isset($schedule['patient'])) unset($schedule['patient']);

               $response = [



                    'success' => true,



                    'message' => 'Update Schedule Record Successfully',



                    'data' => $schedule



                ];



            }



        } catch (\Exception $e) {



            $response = array('success' => false, 'message' => $e->getMessage());



        }



        return response()->json($response);



    }



    public function destroy($id)



    { //return response()->json("ffsds");



        try {



            $schedule = Schedule::find($id);



            if (empty($schedule)) {



                $response = [



                    'success' => false,



                    'message' => 'Sorry Record not Found',



                    'data' => $schedule



                ];



            } else {



                $schedule->delete();



                $response = [



                    'success' => true,



                    'message' => 'Deleted Record Successfully',



                    'data' => $schedule



                ];



            }



        } catch (\Exception $e) {



            $response = array('success' => false, 'message' => $e->getMessage());



        }



        return response()->json($response);



    }



}



