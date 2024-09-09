<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PatientsFileLogModel;
use App\Models\Patients;
use App\Models\User;
use App\Models\Insurances;
use App\Models\PatientsChangesHistoryModel;
use App\Models\Clinic;
use App\Models\InsuranceHistory;

use Carbon\Carbon;
use Auth, Validator, DB;

class CommonFunctionController extends Controller
{
    //public function patientsChangesHistoryCreate(Request $request){
        public function patientsChangesHistoryCreate($previousPatientData, $id, $source){
        
            $currentPatientData = Patients::find($id);
            $logHistory['patient_id']       = $id;
            $logHistory['insurance_id']     = @$currentPatientData['insurance_id'] ?? NULL;
            
            // Convert JSON strings to associative arrays
            $array1 = $previousPatientData->toArray();
            $array2 = $currentPatientData->toArray();
            // Initialize an array to store differences
            $differences = [];
    
            // Iterate through each key in the first JSON object
            foreach ($array1 as $key => $value1) {
                // Check if the key exists in the second JSON object
                if (isset($array2[$key])) {
                    // Compare the values
                    if ($array2[$key] !== $value1) {
                        // Check if the key is 'updated_at', if so, skip it
                        if ($key !== 'updated_at') {
                            $differences[$key] = $array2[$key];
                        }
                        if ($key == 'insurance_id') {
                            $logHistory['insurance_id'] = $array2[$key];
                        }
                        // If values are different, store the previous and new values
                        //$differences[$key] = ['previous' => $value1, 'new' => $array2[$key]];
                        //$differences[$key] = ['new' => $array2[$key]];
                      
                    }
                } 
            }
    
            // Print differences
            $logHistory['differences'] = json_encode($differences);
            $logHistory['created_user'] = Auth::id();
            $logHistory['source'] = $source;
            if($logHistory['differences'] !== '[]'){
                PatientsChangesHistoryModel::updateOrCreate($logHistory);
            }
        }
    public function patientsChangesHistory(Request $request){
       // return "currentPatientData";
        $patient_id = $request->patient_id;
        $find = PatientsChangesHistoryModel::select('id','patient_id' ,'differences','created_at')->where('patient_id',$patient_id)->orderBy('created_at', 'desc')->get()->toArray();
        return $find;
    }

    public function insuranceStartDateFound(Request $request){
        try{
            $patient_id = $request->patient_id;

            $last =  InsuranceHistory::where('patient_id',$patient_id)->latest('id')->first();

            if(!empty($last)){
                $dateString = $last->insurance_end_date;
                $insurance_start_date = Carbon::parse($dateString)->format('m/d/Y');
            }else{
                $find = Patients::where('id',$patient_id)->first();
                $timestamp = $find->created_at;

                $insurance_start_date = Carbon::parse($timestamp)->format('m/d/Y');
            }

            $response = [
                'insurance_start_date' => $insurance_start_date
            ];

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

    return response()->json($response);
    }

    public function patientInsuranceHistory(Request $request){
        try{
            $patient_id = $request->patient_id;
            $find = InsuranceHistory::where('patient_id',$patient_id)->orderBy('created_at', 'desc')->get()->toArray();

            $response = [
                'success'       => true,
                'message'       => 'Patient Insurance History Found Successfully',
                'data'          => @$find
            ];

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

    return response()->json($response);
    }

    public function inActiveInsuranceHistoryUpdate(Request $request){
       // return "Rizwan";
        try{
            $data['id']             = @$request->id;
            $data['insurance_id']   = @$request->insurance_id;
            $data['member_id']      = @$request->member_id;

            $start_date                     = @$request->insurance_start_date;
            $data['insurance_start_date']   = Carbon::parse($start_date)->format('Y-m-d');
            $end_date                       = @$request->insurance_end_date;
            $data['insurance_end_date']     = Carbon::parse($end_date)->format('Y-m-d');
            $data['insurance_status']       = @$request->insurance_status;

            $find = InsuranceHistory::where('id',$data['id'])->first();

            if (!empty($find)) {
                // Update the insurance_id
                $find->insurance_id         = $data['insurance_id'];
                $find->member_id            = $data['member_id'];
                $find->insurance_start_date = $data['insurance_start_date'];
                $find->insurance_end_date   = $data['insurance_end_date'];
                $find->insurance_status     = $data['insurance_status'];
                $find->save();

            }

            $response = [
                'success'       => true,
                'message'       => 'InActive Insurance History Updated Successfully',
                'data'          => @$find
            ];

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

    return response()->json($response);
    }
     

    

    public function patientUpdateInsurance(Request $request){
       // return $request;
        try{
            $data['patient_id']     = $patient_id   = @$request->patient_id;
            $data['insurance_id']   = $insurance_id = @$request->insurance_id;
            $data['member_id']      = $member_id    = @$request->member_id;

            $data['insurance_start_date']   = @$request->insurance_start_date;
            $data['insurance_end_date']     = @$request->insurance_end_date;
            $data['insurance_status']       = @$request->insurance_status;

            
            $new_insurance_id = @$request->new_insurance_id;
            $new_member_id    = @$request->new_member_id;

            $find = Patients::where('id',$patient_id)->first();
            if ($find) {
                // Update the insurance_id
                $find->insurance_id = $new_insurance_id;
                $find->save();

                if(!empty($new_member_id)){
                    $find->member_id    = $new_member_id;
                }else{
                    $find->member_id    = $member_id;
                }

                $find->save(); // Save the changes
                

                $insuranceHistoryData = InsuranceHistory::create($data);
                // Now you can return the updated record
                //return $find;
                
                $response = [
                    'success'               => true,
                    'message'               => 'Patient Insurance Updated Successfully',
                    'patient_data'          => $find,
                    'insuranceHistoryData'  => $insuranceHistoryData
                ];
            } else {
                $response = [
                    'success'               => false,
                    'message'               => 'Sorry No Patient Found'
                ];
            }           

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

    return response()->json($response);
    }

    
    public function patientsFileLogs(Request $request, $fromCaregaps = "", $filterYear = "" , $fileLogId = "" )
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'clinicIds' => 'required',
                    'insuranceIds' => 'required',
                    'data'  => 'required',
                ],
                [
                    'clinicIds.required' => 'Please select a Clinic.',
                    'insuranceIds.required' => 'Please select an Insurance.',
                    'data.required' => 'No data found to add in patients',
                ]
            ); 
            if($validator->fails()) {
                $error = $validator->getMessageBag()->first();
                return response()->json(['success'=>false,'errors'=>$error]);
            };

            $data['insurance_id']    = $request->insuranceIds;
            $data['clinic_id']       = $request->clinicIds;
            $data['gap_year']       = $request->gap_year;
            $data['file_name']       = $request->file_name;

            $existingPatient    = @$request->existingPatients ?? [];
            $newPatient         = @$request->newPatients ?? [];
            $dobIssue           = @$request->dobIssue ?? [];
            $missingMemberID    = @$request->missingMemberID ?? [];
            $firstNameIssue     = @$request->firstNameIssue ?? [];
            $lastNameIssue      = @$request->lastNameIssue ?? [];
            $insuranceIssue     = @$request->insuranceIssue ?? [];
            $genderIssue        = @$request->genderIssue ?? [];
            $multipleIssue      = @$request->multipleIssue  ?? [];

            $total = array_merge($existingPatient,$newPatient,$dobIssue,$missingMemberID,$firstNameIssue,$lastNameIssue,$insuranceIssue,$genderIssue,$multipleIssue);
            
            $data['existingPatient']    =  json_encode($existingPatient);
            $data['newPatient']         =  json_encode($newPatient);
            $data['missingMemberID']    =  json_encode($missingMemberID);
            $data['lastNameIssue']      =  json_encode($lastNameIssue);
            $data['firstNameIssue']     =  json_encode($firstNameIssue);
            $data['dobIssue']           =  json_encode($dobIssue);
            $data['insuranceIssue']     =  json_encode($insuranceIssue);
            $data['genderIssue']        =  json_encode($genderIssue);
            $data['multipleIssue']     =  json_encode($multipleIssue);
    
            $data['created_user']       = Auth::check() ? Auth::id() : 1;
            
            $srs = array_column($total, 'sr');

            // Counting unique 'sr' values
            $uniqueSrCount = count(array_unique($srs));
            $data['total_records']      =  $uniqueSrCount;

            if (!empty($data)) {
                if(!empty($fileLogId)){
                    PatientsFileLogModel::where('id', $fileLogId)->update([
                        'missingMemberID'   => $data['missingMemberID'],
                        'lastNameIssue'     => $data['lastNameIssue'],
                        'firstNameIssue'    => $data['firstNameIssue'],
                        'dobIssue'          => $data['dobIssue'],
                        'insuranceIssue'    => $data['insuranceIssue'],
                        'genderIssue'       => $data['genderIssue'],
                        'multipleIssue '    => $data['multipleIssue'],
                        // Add more columns as needed
                    ]);
                }else{
                    PatientsFileLogModel::create($data);
                }
                $response = array('success'=>true,'message'=>'Patients File Log Addedd successfully');
            } else {
                $response = array('success'=>false,'message'=>'Face Some Issues');
            }

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }

    public function parserEdit(Request $request, $id)
    { 
        try {
            $patientFileLog  = PatientsFileLogModel::find($id);
            if ($patientFileLog  !== null) {
                $row = $patientFileLog ->toArray();
                $gap_year = $row['gap_year'];
                $clinic_id = $row['clinic_id'];

                $doctors = User::select('id','first_name','mid_name', 'last_name')->where('clinic_id', $clinic_id)
                ->where('role', 21)->orWhere('role', 13)->get()->toArray();
               
                $insuranceData = Insurances::select('id','name','short_name','provider')->where('clinic_id', $clinic_id)->get()->toArray();
                // $list['existingPatient']    = json_decode($row['existingPatient'], true);
                // $list['newPatient']         = json_decode($row['newPatient'], true);
                
                
                $missingMemberID    = $list['missingMemberID']    = json_decode($row['missingMemberID'], true);          
                $lastNameIssue      = $list['lastNameIssue']      = json_decode($row['lastNameIssue'], true);
                $firstNameIssue     = $list['firstNameIssue']     = json_decode($row['firstNameIssue'], true);
                $dobIssue           = $list['dobIssue']           = json_decode($row['dobIssue'], true);
                $insuranceIssue     = $list['insuranceIssue']     = json_decode($row['insuranceIssue'], true);
                $genderIssue        = $list['genderIssue']        = json_decode($row['genderIssue'], true);
                $multipleIssue      = $list['multipleIssue']     = isset( $row['multipleIssue'] ) ? json_decode($row['multipleIssue'], true) : [];

                $total = array_merge($dobIssue,$missingMemberID,$firstNameIssue,$lastNameIssue,$insuranceIssue,$genderIssue,$multipleIssue);
                $uniqueRecords = [];
                
                $uniqueMemberIds = [];
                foreach ($total as $record) {
                    // Check if member ID exists in the list of unique member IDs
                    if (!in_array($record['sr'], $uniqueMemberIds)) {
                        // If not, add member ID to the list of unique member IDs
                        $uniqueMemberIds[] = $record['sr'];
                        // Add the record to the list of unique records
                        $uniqueRecords[] = $record;
                    }
                }
                //return $uniqueRecords;
                foreach ($uniqueRecords as $key => $value) {
                    //return $value['dob'];
                    $member_id = $value['member_id'] ?? '';
                    $dob = date("Y-m-d", strtotime($value['dob']));
                    $dob_ = date("m-d-Y", strtotime($value['dob']));
        
                    // Normalize last name
                    if (strpos($value['last_name'], ' ') !== false) {
                        $value['last_name'] = explode(' ', $value['last_name'], 2)[0];
                    }
        
                    $unique_id = $value['last_name'] . $value['first_name'] . str_replace('-', '', $dob_);
    
                    unset( $value['clinic'] );
                    unset( $value['insurance'] );
    
                    $existPatientData = Patients::with(['insurance', 'doctor', 'clinic'])
                        ->where(function ($query) use ($gap_year, $unique_id, $member_id) {
                            $query->where(['patient_year' => $gap_year, 'unique_id' => $unique_id]);
                            if (!empty($member_id)) {
                                $query->orWhere(['member_id' => $member_id]);
                            }
                        })
                        ->first();
                    
                    $value['group'] = match ($value['groups']) {
                        "Group A1" => 1,
                        "Group A2" => 2,
                        "Group B" => 3,
                        "Group C" => 4,
                        "Unknown Group" => null
                    };
        
                    if (!empty($existPatientData)) {
                        $existing = $existPatientData->toArray();
                    
                        $existing['doctor_name'] = $existing['doctor']['name'] ?? '';
                        $existing['insurance_name'] = $existing['insurance']['name'] ?? '';
                        $existing['clinic_name'] = $existing['clinic']['name'] ?? '';
                    
                        // Map status and groups to their respective labels
                        $existing['status'] = ($existing['status'] == 1) ? "Assigned" : (($existing['status'] == 2) ? "Assignable" : "Unknown");
                        $existing['groups'] = match ($existing['group']) {
                            1 => "Group A1",
                            2 => "Group A2",
                            3 => "Group B",
                            4 => "Group C",
                            default => ""
                        };
                    
                        $value['dob'] = date("Y-m-d", strtotime($value['dob']));
                    
                        // Check for issues and populate results accordingly
                        $issues = [];
                        if ($existing['last_name'] !== $value['last_name']) {
                            $issues[] = "lastNameIssue";
                        } 
                        if ($existing['first_name'] !== $value['first_name']) {
                            $issues[] = "firstNameIssue";
                        } 
                        if ($existing['dob'] !== $dob) {
                            $issues[] = "dobIssue";
                        } 
                        if ($existing['insurance']['name'] !== $value['insurance_name']) {
                            $issues[] = "insuranceIssue";
                        } 
                        if (empty($member_id)) {
                            $issues[] = "missingMemberID";
                        } 
                        if (strtolower( $existing['gender'] ) !== strtolower( $value['gender'] )) {
                            $issues[] = "genderIssue";
                        } 
                    
                        if (!empty($issues)) {
                            $k = $issues[0];
                            if( count( $issues ) > 1 ) {
                                $k = 'multipleIssue';
                            }

                            $results[$k][] = array_merge($existing,['tab_name' => $k, 'description' => implode(", ", $issues)]);
                            $results[$k][] = array_merge($value,['tab_name' => $k, 'description' => implode(", ", $issues)]);
                        } else {
                            $results['existingPatient'][] = $this->mergeArraysNotExistingKeys(array_merge($existing,['tab_name' => 'existingPatient']),$value);
                            //$results['existingPatient'][] = array_merge($value,['tab_name' => 'existingPatient']);
                        }
                    } else {
                        $value['dob'] = date("Y-m-d", strtotime($value['dob']));
                        $results['newPatient'][] = $value;
                    }
                        
                }
                if (empty($results['newPatient']) && empty($results['existingPatient'])) {
                    $status = "warning";
                    $success = true;
                    $message = "Edit form open successfully";
                } elseif (!empty($results['missingMemberID']) || !empty($results['lastNameIssue']) || !empty($results['firstNameIssue']) || !empty($results['dobIssue']) || !empty($results['insuranceIssue']) || !empty($results['genderIssue'])) {
                    $status = "error";
                    $success = true;
                    $message = "Data analysis completed with errors. Please check the issues.";
                }else{
                    $status = "error";
                    $success = false;
                    $message = "Sorry";
                }
        
               // return $results;
            }
            $response = [
                        'success'       => $success,
                        'message'       => $message,
                        'status'        => $status,
                        'doctors'       => $doctors,
                        'insuranceData' => $insuranceData,
                        'data'          => @$results
                    ];

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }
    // public function parserEditrizwan(Request $request, $id)
    // { 
    //     try {
    //         $patientFileLog  = PatientsFileLogModel::find($id);
    //         if ($patientFileLog  !== null) {
    //             $row = $patientFileLog ->toArray();
    //             $gap_year = $row['gap_year'];

    //             $list['missingMemberID']    = json_decode($row['missingMemberID'], true);
    //             $list['existingPatient']    = json_decode($row['existingPatient'], true);
    //             $list['newPatient']         = json_decode($row['newPatient'], true);
    //             $list['lastNameIssue']      = json_decode($row['lastNameIssue'], true);
    //             $list['firstNameIssue']     = json_decode($row['firstNameIssue'], true);
    //             $list['dobIssue']           = json_decode($row['dobIssue'], true);
    //             $list['insuranceIssue']     = json_decode($row['insuranceIssue'], true);
    //             $list['genderIssue']        = json_decode($row['genderIssue'], true);

    //             foreach($list['missingMemberID'] as $miss){
                  
    //                 $unique_id = $miss['unique_id'];

                
    //                 $existPatientData = Patients::where(['patient_year' => $gap_year, 'unique_id' => $unique_id])->first();

                    

    //                 if (!empty($existPatientData)) {
    //                     $existing = $existPatientData->toArray();
        
    //                     $existing['doctor_name'] = $existing['doctor']['name'] ?? '';
    //                     $existing['insurance_name'] = $existing['insurance']['name'] ?? '';
    //                     $existing['clinic_name'] = $existing['clinic']['name'] ?? '';
        
    //                     // Map status and groups to their respective labels
    //                     $existing['status'] = ($existing['status'] == 1) ? "Assigned" : (($existing['status'] == 2) ? "Assignable" : "Unknown");
    //                     $existing['group'] = match ($existing['group']) {
    //                         1 => "Group A1",
    //                         2 => "Group A2",
    //                         3 => "Group B",
    //                         4 => "Group C",
    //                         default => "Unknown Group"
    //                     };
    //                 $results['missingMemberID'][] = array_merge($existing,['tab_name' => 'missingMemberID']);
    //                 $results['missingMemberID'][] = array_merge($miss,['tab_name' => 'missingMemberID']);
    //                 }
    //             }
    //             foreach($list['lastNameIssue'] as $last){
                  
    //                 $unique_id = $last['unique_id'];

                
    //                 $existPatientData = Patients::where(['patient_year' => $gap_year, 'unique_id' => $unique_id])->first();

                    

    //                 if (!empty($existPatientData)) {
    //                     $existing = $existPatientData->toArray();
        
    //                     $existing['doctor_name'] = $existing['doctor']['name'] ?? '';
    //                     $existing['insurance_name'] = $existing['insurance']['name'] ?? '';
    //                     $existing['clinic_name'] = $existing['clinic']['name'] ?? '';
        
    //                     // Map status and groups to their respective labels
    //                     $existing['status'] = ($existing['status'] == 1) ? "Assigned" : (($existing['status'] == 2) ? "Assignable" : "Unknown");
    //                     $existing['group'] = match ($existing['group']) {
    //                         1 => "Group A1",
    //                         2 => "Group A2",
    //                         3 => "Group B",
    //                         4 => "Group C",
    //                         default => "Unknown Group"
    //                     };
    //                 $results['missingMemberID'][] = array_merge($existing,['tab_name' => 'missingMemberID']);
    //                 $results['missingMemberID'][] = array_merge($miss,['tab_name' => 'missingMemberID']);
    //                 }
    //             }
    //         }
    //         $response = [
    //                     'success' => true,
    //                     'message'=>'You have ',
    //                     'data' => $results
    //                 ];

    //     } catch (\Exception $e) {
    //         $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
    //     }

    //     return response()->json($response);
    // }

    // analyse data
    public function analyse(Request $request) {
       
        try {
            $data = $request->data;
            $gap_year = $request->gap_year;
    
            $results = [
                "missingMemberID"   => [],
                "existingPatient"   => [],
                "newPatient"        => [],
                "lastNameIssue"     => [],
                "firstNameIssue"    => [],
                "dobIssue"          => [],
                "insuranceIssue"    => [],
                "genderIssue"       => [],
                "groups"             => [],
                "status"            => "completed",
                "success"           => true,
                "message"           => "Data analysis completed successfully."
            ];
    
            foreach ($data as $key => $value) {
                $member_id = $value['member_id'] ?? '';
                $dob = date("Y-m-d", strtotime($value['dob']));
                $dob_ = date("m-d-Y", strtotime($value['dob']));
                $value['name'] = $value['patient_full_name'];
                unset( $value['patient_full_name'] );
    
                // Normalize last name
                if (strpos($value['last_name'], ' ') !== false) {
                    $value['last_name'] = explode(' ', $value['last_name'], 2)[0];
                }
    
                $unique_id = $value['last_name'] . $value['first_name'] . str_replace('-', '', $dob_);
                
                               
                if(!empty($value['doctor_id'])){
                    if (strstr($value['doctor_id'], ',')) {
                        $doctor_name = preg_split('/[\s,]+/', $value['doctor_id'] );
                        $first = $doctor_name[1];
                        $last = $doctor_name[0];
                    } else {
                       $doctor_name = explode( ' ',$value['doctor_id'] );
                        $first = $doctor_name[0];
                        $last = $doctor_name[1];
                    }
                    $doctor = User::with('clinic')->where(['first_name' => $first, 'last_name' => $last])->get()->first();
                    
                    $value['doctor_id'] = isset( $doctor['id'] ) ? intval( $doctor['id'] ) : '';
                    $value['doctor_name'] = $doctor['name'] ?? '';

                    $value['clinic_id'] = isset( $doctor['clinic_id'] ) ? intval( $doctor['clinic_id'] ) : '';
                    $value['clinic_name'] = $doctor['clinic']['name'] ?? '';
                }
                
                
                $insuranceDropDown = $request->insuranceIds;
                if(!empty($value['insurance'])){
                    $insurance = Insurances::where('id',$insuranceDropDown)->get()->first();
                
                    $value['insurance_id'] = isset( $insurance['id'] ) ? intval( $insurance['id'] ) : '';
                    $value['insurance_name'] = $insurance['name'] ?? '';
                }
                unset( $value['clinic'] );
                unset( $value['insurance'] );

                $existPatientData = Patients::with(['insurance', 'doctor', 'clinic'])
                    ->where(function ($query) use ($gap_year, $unique_id, $member_id) {
                        $query->where(['patient_year' => $gap_year, 'unique_id' => $unique_id]);
                        if (!empty($member_id)) {
                            $query->orWhere(['member_id' => $member_id]);
                        }
                    })
                    ->first();

                $value['group'] = match ($value['groups']) {
                    "Group A1" => 1,
                    "Group A2" => 2,
                    "Group B" => 3,
                    "Group C" => 4,
                    "Unknown Group" => null
                };
    
                if (!empty($existPatientData)) {
                    $existing = $existPatientData->toArray();
    
                    $existing['doctor_name'] = $existing['doctor']['name'] ?? '';
                    $existing['insurance_name'] = $existing['insurance']['name'] ?? '';
                    $existing['clinic_name'] = $existing['clinic']['name'] ?? '';

                    // Inside your controller method
                    $patientId = $existPatientData->id; // Assuming $existPatientData is already defined
                    $awvGapValues = $this->getAwvGapValuesForPatient($patientId);

                    $existing['awv_gap'] = $awvGapValues ?? $value['awv_gap'];
    
                    // Map status and groups to their respective labels
                    $existing['status'] = ($existing['status'] == 1) ? "Assigned" : (($existing['status'] == 2) ? "Assignable" : NULL);
                    $existing['groups'] = match ($existing['group']) {
                        1 => "Group A1",
                        2 => "Group A2",
                        3 => "Group B",
                        4 => "Group C",
                        default => ""
                    };
                        
                    $value['dob'] = date("Y-m-d", strtotime($value['dob']));
                    // // Convert DOB to desired format "d/m/y"
                    // $value['dob'] = date("m/d/Y", strtotime($value['dob']));
                    // $existDOB = $existing['dob'];

                    // Check for issues and populate results accordingly
                    $issues = [];
                    if ($existing['last_name'] !== $value['last_name']) {
                        $issues[] = "lastNameIssue";
                    } 
                    if ($existing['first_name'] !== $value['first_name']) {
                        $issues[] = "firstNameIssue";
                    } 
                    if ($existing['dob'] !== $dob) {
                        $issues[] = "dobIssue";
                    } 
                    if ($existing['insurance']['name'] !== $value['insurance_name']) {
                        $issues[] = "insuranceIssue";
                    } 
                    if (empty($member_id)) {
                        $issues[] = "missingMemberID";
                    } 
                    if (strtolower( $existing['gender'] ) !== strtolower( $value['gender'] )) {
                        $issues[] = "genderIssue";
                    } 
                
                    if (!empty($issues)) {
                        $k = $issues[0];
                        if( count( $issues ) > 1 ) {
                            $k = 'multipleIssue';
                        }

                        $results[$k][] = array_merge($existing,['tab_name' => $k, 'description' => implode(", ", $issues)]);
                        $results[$k][] = array_merge($value,['tab_name' => $k, 'description' => implode(", ", $issues)]);
                    } else {
                        $results['existingPatient'][] = $this->mergeArraysNotExistingKeys(array_merge($existing,['tab_name' => 'existingPatient']),$value);
                        //$results['existingPatient'][] = array_merge($value,['tab_name' => 'existingPatient']);
                    }
                } else {
                    $value['dob'] = date("Y-m-d", strtotime($value['dob']));
                    $results['newPatient'][] = $value;
                }
            }
    
            if (empty($results['newPatient']) && empty($results['existingPatient'])) {
                $results['status'] = "warning";
                $results['success'] = true;
                $results['message'] = "No new or existing patients found for analysis.";
            } elseif (!empty($results['missingMemberID']) || !empty($results['lastNameIssue']) || !empty($results['firstNameIssue']) || !empty($results['dobIssue']) || !empty($results['insuranceIssue']) || !empty($results['genderIssue'])) {
                $results['status'] = "error";
                $results['success'] = true;
                $results['message'] = "Data analysis completed with errors. Please check the issues.";
            }
    
            return $results;
        } catch (\Exception $e) {
            $line = $e->getLine();
            return [
                "status"  => "error",
                "success" => false,
                "message" => "An error occurred during data analysis: " . $e->getMessage(),
                "line"    =>  $line
            ];
        }
    }

    public function mergeArraysNotExistingKeys($arr1, $arr2) {
        // Filter out keys from $arr2 that already exist in $arr1
        $filteredArr2 = array_filter($arr2, function($key) use ($arr1) {
            return !array_key_exists($key, $arr1);
        }, ARRAY_FILTER_USE_KEY);
    
        // Merge the filtered array with $arr1
        return array_merge($arr1, $filteredArr2);
    }

    // Define a method to get AWV gap values for a specific patient
    public function getAwvGapValuesForPatient($patientId)
    {
        // Load the patient using eager loading
        $patient = Patients::with([
            'careGapsData',
            'careGapsDataHumana',
            'careGapsDataMedicareArizona',
            'careGapsDataAetnaMedicare',
            'careGapsDataAllwellMedicare',
            'careGapsDataHealthchoiceArizona',
            'careGapsDataUnitedHealthcare'
        ])->find($patientId);

        // Initialize an array to store the AWV gap values
        $awvGapValues = [];

        // // Check if the patient exists
        // if ($patient) {
        //     // Extract AWV gap values for each related model
        //     $awvGapValues = [
        //         'careGapsData' => $patient->careGapsData ? $patient->careGapsData->awv_gap : null,
        //         'careGapsDataHumana' => $patient->careGapsDataHumana ? $patient->careGapsDataHumana->awv_gap : null,
        //         'careGapsDataMedicareArizona' => $patient->careGapsDataMedicareArizona ? $patient->careGapsDataMedicareArizona->awv_gap : null,
        //         'careGapsDataAetnaMedicare' => $patient->careGapsDataAetnaMedicare ? $patient->careGapsDataAetnaMedicare->awv_gap : null,
        //         'careGapsDataAllwellMedicare' => $patient->careGapsDataAllwellMedicare ? $patient->careGapsDataAllwellMedicare->awv_gap : null,
        //         'careGapsDataHealthchoiceArizona' => $patient->careGapsDataHealthchoiceArizona ? $patient->careGapsDataHealthchoiceArizona->awv_gap : null,
        //         'careGapsDataUnitedHealthcare' => $patient->careGapsDataUnitedHealthcare ? $patient->careGapsDataUnitedHealthcare->awv_gap : null,
        //     ];

        //     // Filter out null values
        //     $awvGapValues = array_filter($awvGapValues, function ($value) {
        //         return $value !== null;
        //     });
        // }

        // Check if the patient exists
        if ($patient) {
            // Extract AWV gap values for each related model
            $awvGapValues = [
                $patient->careGapsData ? $patient->careGapsData->awv_gap : null,
                $patient->careGapsDataHumana ? $patient->careGapsDataHumana->awv_gap : null,
                $patient->careGapsDataMedicareArizona ? $patient->careGapsDataMedicareArizona->awv_gap : null,
                $patient->careGapsDataAetnaMedicare ? $patient->careGapsDataAetnaMedicare->awv_gap : null,
                $patient->careGapsDataAllwellMedicare ? $patient->careGapsDataAllwellMedicare->awv_gap : null,
                $patient->careGapsDataHealthchoiceArizona ? $patient->careGapsDataHealthchoiceArizona->awv_gap : null,
                $patient->careGapsDataUnitedHealthcare ? $patient->careGapsDataUnitedHealthcare->awv_gap : null,
            ];

            // Filter out null values and re-index the array
            $awvGapValues = array_values(array_filter($awvGapValues));
        }

        return reset($awvGapValues);
    }
    
}
