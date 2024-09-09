<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Utility;

use App\Http\Controllers\Api\ProgramController;

use App\Models\Patients;
use App\Models\Diagnosis;
use App\Models\SurgicalHistory;
use App\Models\User;
use App\Models\Questionaires;
use App\Models\Insurances;
use App\Models\Medications;
use App\Models\Clinic;
use App\Models\ClinicUser;
use App\Models\Programs;
use App\Models\CareGaps;

use Auth, Validator, DB;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

class CareGapsController extends Controller
{
    protected $per_page = '';

    public function __construct(){
	    $this->per_page = Config('constants.perpage_showdata');
	}
    public function clinicData(){
        try{
        $clinicList = Clinic::select('id', 'name')->get()->toArray();
        $response = [
            'success' => true,
            'message' => 'Clinic Data Retrived Successfully',
            'clinic_list' => $clinicList
        ];
    } catch (\Exception $e) {
        $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
    }

    return response()->json($response);

    }

    /* Getting all Patients */
    public function index(Request $request)
    {
        try {

            $doctor_id = $request->input("doctor_id") ?? '';
            $patient_id = $request->input("patient_id") ?? '';
            $clinic_id = $request->input("clinic_id") ?? '';
            $insurance_id = $request->input("insurance_id") ?? '';
            $bulk_Assign = $request->has('bulk_assign') && (int)$request->bulk_assign == 1 ? 1 : 0;
            $get_assigned = @$request->get_assigned ?? "";
            
            $group_b = $request->input("group_b") ?? '';
            $totalPopulation = $request->input("totalPopulation") ?? '';

            $active = $request->input("active") ?? 1;

            $query = Patients::with('insurance', 'doctor', 'coordinator', 'questionServey','diagnosis','medication','surgical_history');

            if ($request->has('my_patients') && $request->my_patients == 1) {
                $query->where('coordinator_id', Auth::id());
            }


            /* Filters */
            if (!empty($totalPopulation)) {
                $query = $query->where('deleted_at', '=', Null);
            }
            
            if (!empty($group_b)) {
                $query = $query->whereColumn('address','!=','change_address')->orWhereColumn('doctor_id','!=','change_doctor_id')->orWhereNotNull('dod');
            }

            if(!empty($doctor_id)){
                $query->where('doctor_id',$doctor_id);
            }

            if(!empty($insurance_id)){
                $query->where('insurance_id',$insurance_id);
            }

            if(!empty($clinic_id) && count(explode(',', $clinic_id)) == 1 ){
                $query->where('clinic_id',$clinic_id);
            }

            if(!empty($patient_id)){
                $query->where('patient_id',$patient_id);
            }

            if ($active == 2) {
                $query = $query->onlyTrashed();
            }

            // $query = $query->when($bulk_Assign == 1, function($q) {
            //     $query = $q->whereNull('coordinator_id');
            // });

            /*Search patient*/
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->get('search');
                $first_name = '';
                $last_name = '';
                $dob = "";

                if ($search == trim($search) && str_contains($search, ' ')) {
                    $search = explode(' ', $search);
                    $first_name = $search['0'];
                    $last_name = $search['1'];
                }

                if (!is_array($search) && str_contains($search, '/')) {
                    $dob = date('Y-m-d', strtotime($search));
                }

                $query = $query
                            ->when(empty($first_name) && empty($last_name) && empty($dob), function($q) use($search, $get_assigned) {
                                $q->where('first_name', 'like', '%' . $search . '%')
                                    ->orWhere('last_name', 'like', '%' . $search . '%')
                                    ->orWhere('identity', trim($search))
                                    ->orWhereHas('insurance', function($qu) use ($search) {
                                        $qu->where('name', 'like', '%' . $search . '%');
                                    })
                                    ->when($get_assigned === 'true', function ($q) use ($search) {
                                        $q->orWhereHas('coordinator', function ($qy) use ($search) {
                                            $qy->where('first_name', 'like', '%' . $search . '%')
                                            ->orWhere('last_name', 'like', '%' . $search . '%');
                                        });
                                    });
                            })
                            ->when(!empty($dob), function ($q) use ($dob) {
                                $query = $q->where('dob', $dob);
                            })
                            ->when(!empty($first_name) && !empty($last_name), function($q) use ($first_name, $last_name) {
                                $q->where('first_name', 'like', '%' . $first_name . '%')
                                ->where('last_name', 'like', '%' . $last_name . '%');
                            });
            }
            

            if ($bulk_Assign == 0) {
                $result = $query->orderBy('id', 'DESC')->get();
                $query = $query->paginate($this->per_page);
                $total = $query->total();
                $current_page = $query->currentPage();
                $result = $query->toArray();
            } else {
                if ($get_assigned === 'true') {
                    $bulk_Assign = 0;
                }
                $query = $query->OfCoordinatorID($bulk_Assign);
                $query = $query->orderBy('first_name');
                $result = $query->get()->toArray();
            }

            $result = @$result['data'] ?? $result;

            $clinicList = Clinic::select('id', 'name')->get()->toArray();

            $programs = Programs::when(!empty($clinic_id), function($query) use ($clinic_id) {
                $query->whereRaw("FIND_IN_SET('$clinic_id', clinic_id)");
            })->get()->toArray();

            $coordinators = User::where('role', '23')->get()->toArray();

            // return response()->json($result);

            $list = [];
            foreach ($result as $key => $val) {
                $list[] = [
                    'id' => $val['id'],
                    'first_name' => $val['first_name'],
                    'mid_name' => $val['mid_name'],
                    'last_name' => $val['last_name'],
                    'identity' => $val['identity'],
                    'name' => $val['name'],
                    'contact_no' => $val['contact_no'],
                    'doctor_id' => @$val['doctor_id'],
                    'coordinator_id' => @$val['coordinator_id'],
                    'clinic_id' => @$val['clinic_id'],
                    'doctor_name' =>  @$val['doctor']['name'],
                    'coordinator_name' =>  @$val['coordinator']['name'],
                    'insurance_id' => @$val['insurance_id'],
                    'insurance_name' =>  @$val['insurance']['name'],
                    'age' => $val['age'],
                    'gender' => $val['gender'],
                    'address' => $val['address'],
                    'address_2' => $val['address_2'],
                    'dob' => $val['dob'],
                    'cell' => $val['cell'],
                    'email' => $val['email'],
                    'city' => $val['city'],
                    'state' => $val['state'],
                    'zipCode' => $val['zipCode'],
                    'patient_consent' => $val['patient_consent'] === 1 ? true : false,
                    'consent_data' => json_decode($val['consent_data'], true),
                    'diagnosis' => $val['diagnosis'],
                    'medication' => $val['medication'],
                    'surgical_history' => $val['surgical_history'],
                    'family_history' => json_decode($val['family_history'],true),
                    'social_history' => json_decode($val['social_history'],true),
                ];
            }
           

            $insurances = Insurances::all();
            $insuranceList = [];
            foreach ($insurances as $key => $value) {
                $insuranceList[$value->id] = $value->name;
            }

            $doctorsData = User::where("role" ,21)->orWhere("role", 13)->get()->toArray();
            $doctorList = [];
            foreach ($doctorsData as $key => $value) {
                $doctorList[] = ['id' => $value['id'], 'name'=> $value['name']];
            }

            $response = [
                'success' => true,
                'message' => 'Patients Data Retrived Successfully',
                'current_page' => @$current_page ?? "",
                'total_records' => @$total ?? "",
                'per_page'   => $this->per_page,
                'insurances' => (object) $insuranceList,
                'doctors' => $doctorList,
                'data' => $list,
                'clinic_list' => $clinicList,
                'programs_list' => @$programs??[],
                'coordinators' => @$coordinators??[],
                'bulk_assign' => $bulk_Assign,
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'last_name'   => 'required',
            'first_name'  => 'required',
            'contact_no'  => 'required',
            'dob'         => 'required',
            'age'         => 'required',
            'doctor_id'   => 'required',
            'gender'      => 'required|string',
            'disease'    => 'sometimes|required',
            'address'     => 'sometimes|required',
            'insurance_id' => 'sometimes|required',
            'city'     => 'sometimes|required',
            'state'     => 'sometimes|required',
            'zipCode'     => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $validator->valid();
        try {

            if (!empty($input['dob'])) {
                $input['dob'] = date('Y-m-d', strtotime($input['dob']));
            }

            if (!empty($input['dod'])) {
                $input['dod'] = date('Y-m-d', strtotime($input['dod']));
            }
            
            $patient = Patients::orderBy('id', 'desc')->first();
            
            if (!empty($patient)) {
                $str = $patient->identity ?? "00000000";
                $a = +$str;
                $a = str_pad($a + 1, 8, '0', STR_PAD_LEFT);
                $input['identity'] = $a;
            } else {
                $input['identity'] = "00000001";
            }

            /* Patient Family History */
            $family_history = $input['family_history'] ?? [];
            
            $input['family_history'] = json_encode($family_history);
            $input['clinic_id'] = $request->input('clinic_id');
            $input['change_address'] = $request->input('address');
            $input['change_doctor_id'] = $request->input('doctor_id');

            /* Using Utility to add data in clinic User functions */
            $input = Utility::appendRoles($input);

            $patient_dob = str_replace('/', '', Carbon::parse($input['dob'])->format('m/d/Y'));
            $unique_id = strtoupper($input['last_name']).strtoupper($input['first_name']).$patient_dob;

            $input['unique_id'] = $unique_id;
            $input['insurance_provide_pid'] = $request->input('insurance_provide_pid');

            // return response()->json($input);

            /* Gettin id after patient create */
            Patients::create($input)->id;


            /*Rizwan Start add for show data*/
            $active = $request->input("active") ?? 1;
            $list = Patients::with('insurance', 'doctor', 'questionServey');

            if ($active == 2) {
                $list = $list->onlyTrashed();
            }

            $list = $list->orderBy('id', 'DESC')->get()->toArray();

            $response = [
                'success' => true,
                'message' => 'Add New Patients Successfully',
                'data' => $list
            ];
            /*end rizwan end add for show data */
        } catch (\Exception $e) {
           $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }
    
    
    // Edit existing Patient
    public function edit($id)
    {
        try {
            $note = Patients::with('diagnosis')->where('id', $id)->get();
            $note['insurances'] = Insurances::all();

            if ($note) {
                $response = [
                    'success' => true,
                    'message' => 'Sorry Patient not found',
                    'data' => $note
                ];
            }

            $response = [
                'success' => true,
                'message' => 'Patient data Successfully',
                'data' => $note
            ];



        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }

        return response()->json($response);
    }


    /* Update patient Data */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'last_name'  => 'required',
            'first_name' => 'required',
            'contact_no'  => 'required:patients,contact_no,' . $id,
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

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $validator->valid();

        if ($request->has('coordinator_id')) {
            $input['coordinator_id'] = $request->coordinator_id;
        }

        $note = Patients::find($id);

        try {
            $input['dob'] = date('Y-m-d', strtotime($input['dob']));

            $created_user =  Auth::check() ? Auth::id() : 1;
            
            $input = Utility::appendRoles($input);

            /* Patient Family History */
            $family_history = $input['family_history'] ?? [];
            $input['family_history'] = json_encode($family_history);

            $note->update($input);

            $clinicId = [
                'clinic_id' => $note->clinic_id,
            ];

            Questionaires::where('patient_id', $note->id)->update($clinicId);
            
            $response = [
                'success' => true,
                'message' => 'Update Patient Record Successfully',
                'data' => $note
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }

        return response()->json($response);
    }
    

    public function bulkAssign(Request $request)
    {
        try {

            $validator = Validator::make($request->all(),[
                'coordinator_id'  => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
            };

            $input = $request->all();

            $patient_ids = $input['patient_ids'];
            $coordinator_id = $input['coordinator_id'];

            Patients::whereIn('id', $patient_ids)->update(['coordinator_id'=> $coordinator_id]);

            $response = [
                'success' => true,
                'message' => 'Update Patient Record Successfully',
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine()); 
        }

        return response()->json($response);
    }


    /* Setting patient InActive */
    public function destroy($id)
    {
        try {
            $note = Patients::find($id);
            $note->delete();

            Questionaires::where('patient_id', $id)->delete();
            
            $response = [
                'success' => true,
                'message' => 'Patients Deleted Successfully',
                'data' => $note
            ];

        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }


    /* Add New Diagnosis from patient profile*/
    public function addDiagnosis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'condition'   => 'required',
            'description'   => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $request->all();
        try {
            $patient_id = $input['patient_id'];
            $insertData = [
                'patient_id' => $patient_id,
                'created_user' => Auth::check()?Auth::id():1,
                'condition' => $input['condition'],
                'description' => $input['description'],
                'status' => $input['status'],
                'display' => $input['display'],
            ];

            $insert = Diagnosis::create($insertData);

            $patient_diagnosis = Diagnosis::where('patient_id', $patient_id)->get();

            $response = [
                'success' => true,
                'message' => 'Diagnosis Added Successfully',
                'data' => $patient_diagnosis
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }


    /* Update existing diagnosis
    ** get $id as param
    ** only to update status */
    public function updateDiagnosis(Request $request, $id)
    {
        $input = $request->all();

        try {

            if (isset($input['condition'])) {
                unset($input['condition']);
            }
            $patientId = $input['patient_id'];

            Diagnosis::where('id', $id)->update($input);

            $allDiagnosis = Diagnosis::where('patient_id', $patientId)->get()->toArray();

            $response = [
                'success' => true,
                'message' => 'Diagnosis Updated Successfully',
                'data' => $allDiagnosis,
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }

        return response()->json($response);

    }
    

    /* Add new Medication from patient profile */
    public function addMedications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $request->all();
        try {
            $patient_id = $input['patient_id'];
            $insertData = [
                'patient_id' => $patient_id,
                'created_user' => Auth::check()?Auth::id():1,
                'name' => $input['name'],
                'dose' => @$input['dose'] ?? "",
                'status' => @$input['status'] ?? "",
                'condition' => @$input['condition'] ?? "",
                'created_at' => Carbon::now()
            ];

            $insert = Medications::create($insertData);
            $patient_medications = Medications::where('patient_id', $patient_id)->get();

            $response = [
                'success' => true,
                'message' => 'Medication Added Successfully',
                'data' => $patient_medications
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }


    /* Update existing Medication
    ** get $id as param
    ** only to update Dosage and status */
    public function updateMedication(Request $request, $id)
    {
        $input = $request->all();
        try {

            if (isset($input['name'])) {
                unset($input['name']);
            }

            Medications::where('id', $id)->update($input);

            $patientId = $input['patient_id'];
            $allMedications = Medications::where('patient_id', $patientId)->get()->toArray();

            $response = [
                'success' => true,
                'message' => 'Medication Updated Successfully',
                'data' => $allMedications,
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }

        return response()->json($response);

    }


    /* Add New surgery from patient profile */
    public function addSurgeries(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'procedure'   => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $request->all();

        try {
            $patient_id = $input['patient_id'];
            $surgery_date = @$input['date'] ? date('Y-m-d', strtotime($input['date'])) : Null;
            $insertData = [
                'patient_id' => $patient_id,
                'created_user' => Auth::check()?Auth::id():1,
                'procedure' => $input['procedure'],
                'reason' => @$input['reason'] ?? "",
                'date' => $surgery_date,
                'surgeon' => @$input['surgeon'] ?? "",
                'created_at' => Carbon::now()
            ];

            $insert = SurgicalHistory::create($insertData);

            $patient_diagnosis = SurgicalHistory::where('patient_id', $patient_id)->get();

            $response = [
                'success' => true,
                'message' => 'Surgery Added Successfully',
                'data' => $patient_diagnosis
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }


    /* Update existing Surgery
    ** get $id as param
    ** only to update Reason, Facility/Surgeon and Surgury Date */
    public function updateSurgery(Request $request, $id)
    {
        $input = $request->all();
        try {

            if (isset($input['procedure'])) {
                unset($input['procedure']);
            }

            SurgicalHistory::where('id', $id)->update($input);

            $patientId = $input['patient_id'];
            $allSurgeries = SurgicalHistory::where('patient_id', $patientId)->get()->toArray();

            $response = [
                'success' => true,
                'message' => 'Medication Updated Successfully',
                'data' => $allSurgeries,
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }

        return response()->json($response);

    }


    /**
     * update Family history in patient table
     * @param int $id as patient id
     * @return \Illuminate\Http\Response
     */
    public function updateFamilyHistory(Request $request, $id)
    {
        try {
            $input = $request->all();
            $family_history = json_encode($input['family_history']);

            $update_column = [
                'family_history' => $family_history,
            ];

            Patients::where('id', $id)->update($update_column);

            $response = array(
                'success'=> true,
                'message'=> 'Family History Update Successfully',
                'data'=> json_decode($family_history, true),
            );
            
        } catch (\Exception $e) {
            $response = array(
                'status' => 'Failed',
                'success' => false,
                'Error' => $e->getMessage(),
                'Line' => $e->getLine(),
            );
        }

        return response()->json($response);
    }


    /**
     * Add social history to Patient's Table
     * @param int $id as patient id
     * @return \Illuminate\Http\Response
     */
    public function updateSocialHistory(Request $request, $id)
    {
        try {
            $input = $request->all();
            $social_history = json_encode($input['social_history']);

            $update_column = [
                'social_history' => $social_history,
            ];

            Patients::where('id', $id)->update($update_column);

            $response = array(
                'success'=> true,
                'message'=> 'Social History updates successfully',
                'data'=> json_decode($social_history, true),
            );
        } catch (\Exception $e) {
            $response = array(
                'status'=> 'Failed',
                'success'=> false,
                'Error'=> $e->getMessage(),
                'Line'=> $e->getLine(),
            );
        }
        
        return response()->json($response);
    }


    /* Get Patient Enounters */
    public function getEncounters($id)
    {
        try {
            $encounters = Questionaires::with('allMonthlyAssessment')->where('patient_id', $id)->get()->toArray();
            
            $encounterList = [];

            if ($encounters) {

                foreach ($encounters as $key => $value) {

                    if ($value['date_of_service'] != "") {
                        $encounterList[] = $value;
                    }

                    if (!empty($value['all_monthly_assessment'])) {
                        $monthly_encounter = $value['all_monthly_assessment'];
                        foreach ($monthly_encounter as $monthlykey => $monthlyvalue) {
                            $monthlyvalue['parent_id'] = $value['id'];
                            $encounterList[] = $monthlyvalue;
                        }
                    }
                }


                $response = [
                    'success' => true,
                    'message' => 'Encounters Found',
                    'data' => $encounterList
                ];
            } else {
                $response = [
                    'success' => true,
                    'message' => 'No Encounters Found',
                    'data' => $encounterList
                ];
            }
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }

        return response()->json($response);
    }


    /* Get Insureance and Doctors against Clinins  */
    public function getInsuracneandPcp($clinicId)
    {
        $doctorList = User::OfClinicID($clinicId)->where('role', '21')->orWhere('role', '13')->get()->toArray();

        $pcp = [];
        foreach ($doctorList as $key => $value) {
            $pcp[] = ['id' => $value['id'], 'name'=> $value['name']];
        }

        $clinicId = explode(",", $clinicId);
        $insurancesList = Insurances::whereIn('clinic_id', $clinicId)->select('id', 'name')->get()->toArray();

        $response = [
            'insurances' => $insurancesList,
            'doctors' => $pcp,
        ];

        return response()->json($response);

    }


    /**
     * Store Pateint Consent Information
     * Column patient _consent Boolean
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function storePatientConsent(Request $request, $id)
    {
        //return response()->json($request);
        try {
            $consent_given = $request->consent_given;
            $on_date = $request->on_date;
            $coordinator = $request->coordinator;

            $consent_data = [
                'consent_given' => $consent_given,
                'on_date' => $on_date,
                'coordinator' => $coordinator,
            ];

            $update = [
                'patient_consent' => @$consent_given == "Yes" ? true : false,
                'consent_data' => @$consent_data ? json_encode($consent_data) : null,
            ];

            $update = Patients::where('id', $id)->update($update);

            $response = [
                'success' => true,
                'message' => 'Consent Stored',
                'data' => $consent_data,
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line' => getLine());
        }
    }


    public function storeBulkCareGaps(Request $request)
    {
       return response()->json($request);
        try {
            $validator = Validator::make($request->all(),
                [
                    'clinicIds' => 'required',
                    'data'  => 'required',
                ],
                [
                    'clinicIds.required' => 'Please Select a Clinic.',
                    'data.required' => 'No data found to add in patients' //,
                    // 'date_of_service.required' => 'Please Select Date of service.'
                ]
            );

            if($validator->fails()) {
                $error = $validator->getMessageBag()->first();
                return response()->json(['success'=>false,'errors'=>$error]);
            };
            //return response()->json($request->data);
            $value1 = $bulktoStorePatients  =[];
            $clinic_id = $request->clinicIds;
            $data = json_decode(json_encode(array_filter($request->data)) ,true);
            $doctorData = User::where('role', '21')->orWhere('role', '13')->whereRaw("FIND_IN_SET(?, clinic_id) > 0", [$clinic_id])->get()->toArray();
            $insurancesData = Insurances::where('type_id', '1')->where('clinic_id', $clinic_id)->get()->toArray();
            // Start For Patients Add 
            $allPatients = Patients::where('clinic_id', $clinic_id)->get()->toArray();
            $lastPatient = end($allPatients);

            $identity = '';
            $newidentity = '';
            if ($lastPatient) {
                $str = @$lastPatient['identity'] ?? "00000000";
                $a = +$str;
                $a = str_pad($a + 1, 8, '0', STR_PAD_LEFT);
                $identity = $a;
            } else {
                $identity = "00000000";
            }
            // End For Patients Add 
            $bulktoStore = [];
            foreach ($data as $key => $value) {
               
                unset($value['s._no']);
                //start for Doctor Info
                $doctor_name = explode(', ', $value['provider_name']);
                $lastName = strtoupper($doctor_name['0']);
                $firstName = strtoupper($doctor_name['1']);
                $doctor = array_filter($doctorData, function($item) use ($lastName, $firstName) {
                    return( strtoupper($item['last_name']) == $lastName && strtoupper($item['first_name']) == $firstName );
                });
                $doctor = reset($doctor);

                $patient_name = explode(', ', $value['member_name']);
                $patientLastName = strtoupper($patient_name['0']);
                $patientFirstName = strtoupper($patient_name['1']);
                
                $patientDOB = date('Y-m-d', strtotime($value['date_of_birth']));

                $existPatientData = Patients::where('last_name',$patientLastName)->where('first_name',$patientFirstName)->where('dob',$patientDOB)->get()->toArray();
                $lastPatient  = Patients::select('identity')->latest()->first();  
                    return response()->json(str_pad($lastPatient['identity'] + 1, 8, '0', STR_PAD_LEFT)); exit;
                //return response()->json($existPatientData);
                if(!isset($existPatientData) || empty($existPatientData)){
                    
                    if ($lastPatient) {
                        $str = @$lastPatient['identity'] ?? "00000000";
                        $a = +$str;
                        $a = str_pad($a + 1, 8, '0', STR_PAD_LEFT);
                        $identity = $a;
                    } else {
                        $identity = "00000000";
                    }         
                    //return response()->json($existPatientData);
                    // // Start For Patients Add 
                $value1["identity"] = $identity;
                $value1["unique_id"] = $unique_id ?? '';
                $value1["insurance_provide_pid"] = $value["member_id"];
                $value1["mid_name"] = @$value['mid_name'] ?? "";
                $value1["first_name"] = @$patientFirstName ?? "";
                $value1["mid_name"] = @$value['mid_name'] ?? "";
                $value1["last_name"] = @$patientLastName?? "";
                $value1["gender"] = @$value['gender'] ?? "NULL";
                $value1["age"] = @$value['member_age'] ?? '';
                $value1["dob"] = date('Y-m-d', strtotime($value['date_of_birth']));
                $value1["address_2"] = "";
                $value1["change_address"] = @$value['address'] ?? "";
                $value1["change_doctor_id"] = @$doctor['id'] ?? "";
                $value1["clinic_id"] = $clinic_id;
                $value1["created_user"] = Auth::id();
                $value1["disease"] = "";
                $value1["doctor_id"] = @$doctor['id'] ?? NULL;
                $value1["dod"] = NULL;
                $value1["email"] = "";
                $value1["insurance_id"] = @$insurance['id'] ?? NULL;
                $value1["family_history"] = '[]';
                $value1["social_history"] = NULL;
                $value1["patient_consent"] = 0;
                $value1["consent_data"] = "";
                $value1["created_at"] = Carbon::now();
                $value1["updated_at"] = Carbon::now();
                $value1["deleted_at"] = NULL;
                $bulktoStorePatients[] = (array)$value1;
                //$newidentity = $identity;
                
                
                 //return response()->json($addnew);
                // End For Patients Add 
                }
                $value["member_id"];
                $value["high_risk"];

                $value["last_office_visit_(assigned_tin)"] = date('Y-m-d', strtotime(@$value['last_office_visit_(assigned_tin)']));
                $value["last_office_visit_(any_tin)"] = date('Y-m-d', strtotime(@$value['last_office_visit_(any_tin)']));
                
                //$value["date_of_birth"] = date('Y-m-d', strtotime($value['date_of_birth']));
                unset($value['date_of_birth']);
                unset($value['member_age']);
                unset($value['member_name']);
                unset($value['provider_name']);
                //unset($value['member_name']);

                $value["ed_visit"];
                $value["ip_visit"];

                $value["hcc"];
                $value["che"];

                $value["iha"];
                $value["bcs"];

                $value["insurance_id"] = /*$insurance_id ?? */1;
                $value["clinic_id"] = $clinic_id ;
                $value["doctor_id"] = @$doctor['id'] ?? NULL;

                $value["cbp"];
                $value["coa3"];

                $value["coa2"];
                $value["coa4"];

                $value["col"];
                $value["cdc2"];

                $value["cdc4"];
                $value["trce"];

                $value["trcm"];
                $value["created_user"] = Auth::id();
                $value["patient_id"] = @$existPatientData['id'] ?? 1;

                $value["created_at"] = Carbon::now();
                $value["updated_at"] = Carbon::now();
                $value["deleted_at"] = NULL;

                

                $bulktoStore[] = (array)$value;
               // $newidentity = $identity;
            }
//return response()->json($bulktoStore);
            // if (!empty($bulktoStorePatients)) {
            //     Patients::insert($bulktoStorePatients);
            // }
            
            if (!empty($bulktoStore)) {
                //$res = Patients::insert($bulktoStore);
                
                $res = CareGaps::insert($bulktoStore);
                $addnew = Patients::insert($bulktoStorePatients);
                $response = array('success'=>true,'message'=>'CareGaps Addedd successfully');
            } else {
                $response = array('success'=>false,'message'=>'Duplicate CareGaps found');
            }

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }

}