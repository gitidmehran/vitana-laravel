<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CcmTasks;
use App\Models\User;

use Validator, Auth, DB;
use Carbon\Carbon;

class CcmTaskController extends Controller
{
    public function __construct(){
	    $this->per_page = Config('constants.perpage_showdata');
	}
    
    /**
     * Method index
     *
     * Fetch all the tasks from database
     * 
     * @param Request $request 
     *
     * @return void
     */
    public function index(Request $request)
    {
        try {
            $roleId = Auth::user()->role;
            $userId = Auth::user()->id;

            $query = CcmTasks::with('coordinators')->filterByUserRole($roleId, $userId)->where('monthly_encounter_id', '126');

            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->get('search');

                $query = $query->whereHas('coordinators', function($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            }

            $ccmtasks = $query->get()->toArray();

            $response = [
                "success" => true,
                "message" => "Task retrieved Successfully",
                "data" => @$ccmtasks ?? [],
            ];
        } catch (\Exception $e) {
            $response = array("success" => false, "message" => $e->getMessage(), "line" => $e->getLine());
        }

        return response()->json($response);
    }


    public function fetchLogs(Request $request)
    {
        try {
            $roleId = Auth::user()->role;
            $userId = Auth::user()->id;
            $monthly_encounter_id = $request->monthly_encounter_id;
            $annual_encounter_id = $request->annual_encounter_id;
            $annual_encounter = $request->annual_encounter;

            $where_clause = [
                'monthly_encounter_id' => $monthly_encounter_id,
            ];
            
            if ($annual_encounter == "1") {
                $where_clause = [
                    'annual_encounter_id' => $annual_encounter_id,
                    'monthly_encounter_id' => NULL,
                ];
            }

            $query = CcmTasks::with('coordinators')->where($where_clause)->filterByUserRole($roleId, $userId);

            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->get('search');

                $query = $query->whereHas('coordinators', function($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            }

            $ccmtasks = $query->get()->toArray();

            $response = [
                "success" => true,
                "message" => "Task retrieved Successfully",
                "data" => @$ccmtasks ?? [],
            ];
        } catch (\Exception $e) {
            $response = array("success" => false, "message" => $e->getMessage(), "line" => $e->getLine());
        }

        return response()->json($response);
    }

    
    /**
     * Method store
     * 
     * Store task details in database
     *
     * @param Request $request
     *
     * @return json
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'ccm_cordinator_id' => 'required',
                    'task_type' => 'required',
                    'task_date' => 'required',
                    'task_time' => 'required',
                ],

                [
                    'ccm_cordinator_id.required' => 'ccm_cordinator_id is missing',
                    'task_type.required' => 'task_type is missing',
                    'task_date.required' => 'task_date is missing',
                    'task_time.required' => 'task_time is missing',
                ]
            );

            if($validator->fails()) {
                $error = $validator->getMessageBag()->first();
                return response()->json(['success'=>false,'errors'=>$error]);
            };

            if ($request->has('date_of_service')) {
                
            }

            $data_to_store = [
                'monthly_encounter_id' => @$request->monthly_encounter_id ?? NULL,
                'annual_encounter_id' => $request->annual_encounter_id,
                'ccm_cordinator_id' => $request->ccm_cordinator_id,
                'date_of_service' => Carbon::parse($request->date_of_service),
                'task_type' => $request->task_type,
                'task_date' => date("Y-m-d", strtotime($request->task_date)),
                'task_time' => $request->task_time,
            ];

            $store = CcmTasks::create($data_to_store);

            $totalTime = "";
            
            if ($request->monthly_encounter == 1) {
                $monthly_encounter_id = $request->monthly_encounter_id;
                $totalTime = CcmTasks::when($monthly_encounter_id, function ($query) use ($monthly_encounter_id) {
                                $query->where('monthly_encounter_id', $monthly_encounter_id);
                            })
                            ->sum(DB::raw("TIME_TO_SEC(task_time)"));
            } else {
                $annual_encounter_id = $request->annual_encounter_id;
                $totalTime = CcmTasks::when($annual_encounter_id, function ($query) use ($annual_encounter_id) {
                    $query->where('annual_encounter_id', $annual_encounter_id);
                })
                ->sum(DB::raw("TIME_TO_SEC(task_time)"));
            }
            


            $totalTime = gmdate("H:i:s", $totalTime);

            $response = [
                'success' => true,
                'message' => 'Task stored successfully',
                'data' => $data_to_store,
                "total_task_time" =>@$totalTime ?? "",
            ];
        } catch (\Exception $e) {
            $response = array("success" => false, "message" => $e->getMessage(), "line" => $e->getLine());
        }

        return response()->json($response);
    }


    /**
     * The function udpate() is a PHP function that updates a task in the CcmTasks table and returns a
     * JSON response indicating the success or failure of the update.
     * 
     * @param request request The  parameter is an instance of the Request class, which
     * contains all the data sent in the HTTP request.
     * @param id The parameter `` is the identifier of the task that needs to be updated. It is used
     * to identify the specific task in the database that needs to be updated.
     * 
     * @return a JSON response.
     */
    public function update(request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'ccm_cordinator_id' => 'required',
                    'task_type' => 'required',
                    'task_date' => 'required',
                    'task_time' => 'required',
                ],

                [
                    'ccm_cordinator_id.required' => 'ccm_cordinator_id is missing',
                    'task_type.required' => 'task_type is missing',
                    'task_date.required' => 'task_date is missing',
                    'task_time.required' => 'task_time is missing',
                ]
            );

            if($validator->fails()) {
                $error = $validator->getMessageBag()->first();
                return response()->json(['success'=>false,'errors'=>$error]);
            };

            $update = [
                'task_type' => @$request->task_type,
                'ccm_cordinator_id' => @$request->ccm_cordinator_id,
                'task_date' => date("Y-m-d", strtotime($request->task_date)),
                'task_time' => @$request->task_time,
            ];

            CcmTasks::where('id', $id)->update($update);

            // getting logs from the database after update
            $logs = $this->fetchLogs($request)->getOriginalContent();

            // preparing param for fetchCoordinators function 
            $encounter_id = ""; 
            if (!empty($request->annual_encounter_id)) {
                $encounter_id = $request->annual_encounter_id;
            } elseif (!empty($request->monthly_encounter_id)) {
                $encounter_id = $request->monthly_encounter_id;
            }
            
            /* Updating request thing to fetch total time for logs */
            $request->request->remove('annual_encounter_id');
            $request->request->remove('monthly_encounter_id');
            $request->request->add(['encounter_id' => $encounter_id]);
            
            $totalTime = $this->fetchCoordinators($request)->getOriginalContent();

            $response = array(
                'success' => true,
                'message' => "Task Update Successfully",
                'data' => @$logs['data'] ?? "",
                'total_task_time' => @$totalTime['total_task_time'] ?? ""
            );

        } catch (\Exception $e) {
            $response = array(
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            );
        }

        return response()->json($response);
    }


    /**
     * The function deletes a task with the given ID and returns a JSON response indicating whether the
     * deletion was successful or not.
     * 
     * @param id The parameter "id" is the unique identifier of the task that you want to delete. It is
     * used to find the task in the database and delete it.
     * 
     * @return a JSON response.
     */
    public function delete($id)
    {
        try {
            $note = CcmTasks::find($id);
            $note->delete();
            
            $response = [
                'success' => true,
                'message' => 'Task Deleted Successfully',
                'data' => $note
            ];

        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), $e->getLine());
        }
        return response()->json($response);
    }

    
    /**
     * Method fetchcoordinators
     *
     * @param Request $request
     *
     * @return json
     */
    public function fetchCoordinators(Request $request)
    {
        try {
            
            $coordinators = User::all();
            $totalTime = "";
            $encounter_id = @$request->encounter_id ?? "";

            if ($request->monthly_encounter == 1) {
                $totalTime = CcmTasks::where('monthly_encounter_id', $encounter_id)->sum(DB::raw("TIME_TO_SEC(task_time)"));
                $totalTime = gmdate("H:i:s", $totalTime);
            } else {
                $totalTime = CcmTasks::where('annual_encounter_id', $encounter_id)->sum(DB::raw("TIME_TO_SEC(task_time)"));
                $totalTime = gmdate("H:i:s", $totalTime);
            }
            
            $response = [
                "success" => true,
                "message" => "Task retrieved Successfully",
                "data" => @$coordinators ?? [],
                "total_task_time" =>@$totalTime ?? "",
            ];
        } catch (\Exception $e) {
            $response = array("success" => false, "message" => $e->getMessage(), "line" => $e->getLine());
        }

        return response()->json($response);
    }
}
