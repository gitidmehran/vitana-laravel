<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Questionaires;
use App\Models\Patients;
use App\Models\Doctors;
use App\Models\Diagnosis;
use App\Models\Programs;
use App\Models\User;
use Auth,Validator,Session,Config;
use Carbon\Carbon;

class QuestionairesController extends Controller
{
    protected $singular = "Questionaire Survey";
    protected $plural = "Questionaire Surveys";
    protected $action = "/dashboard/questionaires-survey";
    protected $view = "questionaries.";

    public function index(Request $request){        
        $data = [
            'singular' => $this->singular,
            'page_title' => $this->plural.' List',
            'action'   => $this->action
        ];
        $query = Questionaires::with('patient','user','program:id,name,short_name');

        if (Auth::user()->role == 2) {
            $authId = Auth::id();
            $patientsList = Patients::where('doctor_id', $authId)->get()->toArray();
            $patientsList = array_column($patientsList, 'id');
            $query = $query->whereIn('patient_id', $patientsList)->orWhere('created_user',$authId);       
        }
        $list = $query->orderBy('id', 'DESC')->get()->toArray();
        $data['list'] = $list;
        return view($this->view.'list',$data);
    }

    public function create(Request $request){
        // Session::forget('questionaires');
        $sessionData = Session::has('questionaires')?Session::get('questionaires'):[];
        $programmId = $sessionData['program_id'] ?? '';
        $patientId = $sessionData['patient_id'] ?? '';
        $dateofService = $sessionData['date_of_service'] ?? '';
        $directory = $programmId == 1 ? 'awv':'ccm';
        $path = $this->view.$directory.'/form';
        $lastStep = $sessionData['last_step'] ?? 0;
        $data = [
            'singular' => $this->singular,
            'action' => $this->action,
            'patients' => Patients::all()->toArray(),
            'programs' => Programs::all()->toArray(),
            'path' => $path,
            'questions' => $sessionData,
            'patient_id' => $patientId,
            'program_id' => $programmId,
            'date_of_service' => $dateofService,
            'last_step' => $lastStep
        ];
        return view($this->view.'create',$data);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
            'patient_id' => 'required',
            'program_id'  => 'required',
            'date_of_service' => 'required'
            ],
            [
                'patient_id.required' => 'Please Select a Patient.',
                'program_id.required' => 'Please Select a Program.',
                'date_of_service.required' => 'Please Select Date of service.'
            ]
        );
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };

        $input = $request->all();

        try{

            if ($request->getHost() == "amvapplication.com" || $request->getHost() == "127.0.0.1") {
                $question = [];
                $not_needed = ['patient_id', 'program_id', 'date_of_service'];

                foreach ($input as $key => $value) {
                    if (!in_array($key, $not_needed)) {
                        $question[$key] = $input[$key] ?? [];
                    }
                }

                
            } else {
                $question = [
                    'physical_activities' => $input['physical_activities'] ?? [],
                    'alcohol_use' => $input['alcohol_use'] ?? [],
                    'tobacco_use' => $input['tobacco_use'] ?? [],
                    'ldct_counseling' => $input['ldct_counseling'] ?? [],
                    'nutrition' => $input['nutrition'] ?? [],
                    'seatbelt_use' => $input['seatbelt_use'] ?? [],
                    'depression_phq9' => $input['depression_phq9'] ?? [],
                    'high_stress' => $input['high_stress'] ?? [],
                    'general_health' => $input['general_health'] ?? [],
                    'social_emotional_support' => $input['social_emotional_support'] ?? [],
                    'pain' => $input['pain'] ?? [],
                    'fall_screening' => $input['fall_screening'] ?? [],
                    'cognitive_assessment' => $input['cognitive_assessment'] ?? [],
                    'immunization' => $input['immunization'] ?? [],
                    'screening' => $input['screening'] ?? [],
                    'diabetes' => $input['diabetes'] ?? [],
                    'cholesterol_assessment' => $input['cholesterol_assessment'] ?? [],
                    'bp_assessment' => $input['bp_assessment'] ?? [],
                    'weight_assessment' => $input['weight_assessment'] ?? [],
                    'miscellaneous' => $input['misc'] ?? []
                ];
            }
            
            $data = [
                'questions_answers' => json_encode($question),
                'date_of_service' => Carbon::parse($input['date_of_service'])
            ];

            $row = Questionaires::where(['patient_id'=>$input['patient_id'],'program_id'=>$input['program_id']])->first();

            $row->update($data);
            Session::forget('questionaires');
            $response = array('success'=>true,'message'=>$this->singular.' Added Successfully','action'=>'redirect','url'=>url('/dashboard/reports/analytics-report/'.$row['serial_no']));
        }catch(\Exception $e){
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    public function edit(Request $request,$id){
        $row = Questionaires::find($id)->toArray();
        $questions_answers = json_decode($row['questions_answers'],true);
        $path = $row['program_id'] === 1 ? 'awv':'ccm';

        if($row['program_id']==2){
            $patientDiagnosis = Diagnosis::where('patient_id',$row['patient_id'])->get()->toArray();
            
            $chronic_diseases = Config::get('constants')['chronic_diseases'];

            $patient_diseases = [];
            foreach ($patientDiagnosis as $key => $value) {
                $condition_id = explode(' ', $value['condition'])[0];
                $disease_status = $value['status'];
                $data = array_filter($chronic_diseases, function ($item) use ($condition_id, $disease_status) {
                    if ($disease_status == 'Active') {
                        return in_array($condition_id, $item);
                    }
                });

                if ($data) $patient_diseases[] = array_keys($data)[0];
            }

            $questions_answers['diagnosis'] = $patient_diseases ?? [];
        }

        $data = [
            'singular' => 'Edit '.$this->singular,
            'page_title' => 'Edit '.$this->singular,
            'action' => $this->action,
            'patients' => Patients::all()->toArray(),
            'programs' => Programs::all()->toArray(),
            'row' => $row,
            'path' => $path,
            'list' => $questions_answers
        ];
        // echo '<pre>';print_r($data['patients']);die;
        return view($this->view.'edit',$data);
    }

    public function update(Request $request,$id){
        $validator = Validator::make($request->all(),
            [
            'patient_id' => 'required',
            'program_id'  => 'required'
            ],
            [
                'patient_id.required' => 'Please Select a Patient.',
                'program_id.required' => 'Please Select a Program.'
            ]
        );
        if($validator->fails()){
            return response()->json(['success'=>false,'errors'=>$validator->getMessageBag()]);
        };

        $input = $request->all();

        try{

            if ($request->getHost() == "amvapplication.com" || $request->getHost() == "127.0.0.1") {
                $question = [];
                $not_needed = ['patient_id', 'program_id', 'date_of_service'];
                foreach ($input as $key => $value) {
                    if (!in_array($key, $not_needed)) {
                        $question[$key] = $input[$key] ?? [];
                    }
                }

                
            } else {
                $question = [
                    'physical_activities' => $input['physical_activities'] ?? [],
                    'depression_phq9' => $input['depression_phq9'] ?? [],
                    'alcohol_use' => $input['alcohol_use'] ?? [],
                    'tobacco_use' => $input['tobacco_use'] ?? [],
                    'ldct_counseling' => $input['ldct_counseling'] ?? [],
                    'nutrition' => $input['nutrition'] ?? [],
                    'seatbelt_use' => $input['seatbelt_use'] ?? [],
                    'depression_phq9' => $input['depression_phq9'] ?? [],
                    'high_stress' => $input['high_stress'] ?? [],
                    'general_health' => $input['general_health'] ?? [],
                    'social_emotional_support' => $input['social_emotional_support'] ?? [],
                    'pain' => $input['pain'] ?? [],
                    'fall_screening' => $input['fall_screening'] ?? [],
                    'cognitive_assessment' => $input['cognitive_assessment'] ?? [],
                    'immunization' => $input['immunization'] ?? [],
                    'screening' => $input['screening'] ?? [],
                    'diabetes' => $input['diabetes'] ?? [],
                    'cholesterol_assessment' => $input['cholesterol_assessment'] ?? [],
                    'bp_assessment' => $input['bp_assessment'] ?? [],
                    'weight_assessment' => $input['weight_assessment'] ?? [],
                    'miscellaneous' => $input['misc'] ?? []
                ];
            }
            
            $data = [
                'patient_id' => $input['patient_id'],
                'program_id' => $input['program_id'],
                'questions_answers' => json_encode($question),
            ];

            Questionaires::where('id',$id)->update($data);
            $response = array('success'=>true,'message'=>$this->singular.' Updated Successfully','action'=>'redirect','url'=>url($this->action));
        }catch(\Exception $e){
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    public function autocomplete(Request $request)
    {
        $data = Doctors::select("l_name")
                    ->where('l_name', 'LIKE', '%'. $request->get('query'). '%')
                    ->get();
     
        return response()->json($data);
    }

    public function getProgramms(Request $request){
        try{
            $input = $request->all();
            $list = [];
            if($input['program_id']==2){
                $awvData = Questionaires::where('patient_id',$input['patient_id'])->where('program_id',1)->orderBy('id','desc')->first();
                if(!empty($awvData))
                    $awvData->toArray();

                $list = !empty($awvData)?json_decode($awvData['questions_answers'],true):[];

                $patientDiagnosis = Diagnosis::where('patient_id',$input['patient_id'])->get()->toArray();

                $chronic_diseases = Config::get('constants')['chronic_diseases'];

                $patient_diseases = [];
                foreach ($patientDiagnosis as $key => $value) {
                    $condition_id = explode(' ', $value['condition'])[0];
                    $disease_status = $value['status'];
                    $data = array_filter($chronic_diseases, function ($item) use ($condition_id, $disease_status) {
                        if ($disease_status == 'Active') {
                            return in_array($condition_id, $item);
                        }
                    });

                    if ($data) $patient_diseases[] = array_keys($data)[0];
                }


                if (count($patient_diseases) < 2) {
                    return response()->json(['success'=>false, 'errors'=>'Patient is not eligible for CCM']);
                } else {
                    $list['diagnosis'] = $patient_diseases ?? [];
                }
            }

            if ($input['program_id']==1) {
                $last_awv = Questionaires::where('patient_id',$input['patient_id'])->where('program_id',1)->select('date_of_service')->orderBy('id','desc')->first();
                if ($last_awv) {
                    $last_awv = Carbon::parse($last_awv['date_of_service']);
                    $diffYears = \Carbon\Carbon::now()->diffInYears($last_awv);

                    if ($diffYears <= 1) {
                        return response()->json(['success'=>false,'errors'=>'AWV is already performed for this patient']);
                    }
                }
            }

            $direcoty = $input['program_id']==1?'awv':'ccm';
            $path = $this->view.$direcoty.'/form';
            // echo $path;die;
            $input['questions'] = [
                'fall_screening' => [],
                'depression_phq9' => [],
                'high_stress' => [],
            ];
            Session::put('questionaires',$input);
            $view = view($path,compact('list'))->render();
            $response = array('success'=>true,'view'=>$view);
        }catch(\Exception $e){
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    public function destroy(Request $request,$id){
        try {
            $note = Questionaires::find($id);
            $note->delete();
            $response = array('success'=>true,'message'=>$this->singular.' Deleted!');
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        return response()->json($response);
    }

    public function updateSessionData(Request $request){
        $input = $request->all();
        try {
            $data  =  Session::has('questionaires')?Session::get('questionaires'):[];
            $dbData = Questionaires::where(['patient_id'=>$data['patient_id'],'program_id'=>$data['program_id']])->first();
            if(!empty($dbData)){
                $questions = json_decode($dbData['questions_answers'],true);
                $questions[$input['type']] = $input['data'];
                Questionaires::where(['patient_id'=>$data['patient_id'],'program_id'=>$data['program_id']])->update(['questions_answers'=> json_encode($questions)]);
            }else{

                $program = Programs::find($data['program_id'])->toArray();
                $lastProgramEntry = Questionaires::where('program_id',$data['program_id'])->orderBy('id','desc')->first();

                if(!empty($lastProgramEntry)){
                    $number = explode('-', $lastProgramEntry['serial_no'])[1];
                    $newNumber = (int)$number+1;
                    $serialNo = $program['short_name'].'-'.$newNumber;
                }else{
                    $serialNo = $program['short_name'].'-1001';
                }

                $doctorId = Patients::where('id', $data['patient_id'])->pluck('doctor_id');
               
                $question[$input['type']]  = $input['data'];
                $row = [
                    'program_id' => $data['program_id'],
                    'patient_id' => $data['patient_id'],
                    'doctor_id' => $doctorId[0],
                    'serial_no' => $serialNo,
                    'date_of_service' => Carbon::parse($data['date_of_service']),
                    'questions_answers' => json_encode($question),
                    'created_user' => Auth::id()
                ];
                Questionaires::create($row);
            }
            $data[$input['type']] = $input['data'];
            $data['last_step'] = $input['last_step'];
            Session::put('questionaires',$data); 
            $response =  array('success'=>true,'message'=>'Data Added Successfully');  
        } catch (Exception $e) {
            $response =  array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }
}
