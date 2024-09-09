<?php



namespace App\Http\Controllers\Api;



use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Http\Resources\PhysiciansCollection;

use App\Http\Resources\SpecialistCollection;

use Illuminate\Support\Facades\Hash;

use App\Models\Physicians;

use App\Models\Specialists;

use Auth, Validator;

use Illuminate\Database\Schema\Blueprint;







class PhysiciansController extends Controller

{

    protected $singular = "Physician";

    protected $plural   = "Physicians";

    protected $action   = "/dashboard/physicians";

    protected $view     = "physicians.";

    
    protected $per_page = '';
	public function __construct(){
	$this->per_page = Config('constants.perpage_showdata');

	}


    public function index(Request $request)

    {

        try {

            $query  = new Physicians();

            if ($request->has('search') && !empty($request->input('search'))) {

                $search = $request->get('search');

                $query = $query->where('first_name', 'like', '%' . $search . '%');

            }



            $roles = Config('constants.roles');

            $row = $query->paginate($this->per_page);

            $total = $row->total();

            $current_page = $row->currentPage();

            $physicians = PhysiciansCollection::collection($row);

            $specialities = Specialists::all();

            $specialities_collection = SpecialistCollection::collection($specialities);

            $key = $request->get('search');



            return response()->json([

                'success' => true,

                'total' => $total,

                'current_page' => $current_page,

                'per_page' => $this->per_page,

                'data' => $physicians,

                'roles' => $roles,

                'specialities' => $specialities_collection

            ]);

        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());

        }

    }



    public function single($id)

    {

        try {

            $physician = Physicians::find($id);

            if (!$physician) {

                return response()->json([

                    "message" => "Physicist not found"

                ]);

            }

            $physician_resource = new PhysiciansCollection($physician);

            $specialities = Specialists::all();

            $specialities_collection = SpecialistCollection::collection($specialities);

            return response()->json([

                'physician' => $physician_resource,

                'specialities' => $specialities_collection

            ]);

        } catch (\Exception $e) {

            $response = array('success' => false, 'message' => $e->getMessage());

        }

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

            'gender_selection' => ['Male', 'Female']

        ];

        $physiciansSpeciality = Specialists::all()->toArray();

        $data['physiciansSpeciality'] = $physiciansSpeciality;

        //dd($data['physiciansSpeciality']);

        return view($this->view . 'create', $data);

    }



    /**

     * Store a newly created resource in storage.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function store(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'first_name'  => 'required',

            'last_name'   => 'required',

            // 'contact_no'  => 'required',

            'gender'      => 'required|string',

            'speciality'  => 'sometimes|required',

        ]);

        if ($validator->fails()) {

            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);

        };

        try {

            $physician = new Physicians();

            $physician->first_name = $request->first_name;

            $physician->mid_name = $request->mid_name;

            $physician->last_name = $request->last_name;

            $physician->role = $request->role;

            $physician->contact_no = $request->contact_no;

            $physician->gender = $request->gender;

            $physician->address = $request->address;

            $physician->speciality = $request->speciality;

            $physician->save();

            return response()->json([

                'success' => true,

                'data' => $physician,

                "message" => "Physician has been added successfully"

            ]);

            // $input['created_user'] = Auth::id();

            // $input['role'] = $request->role;

            // $input['password'] = Hash::make($request->password);

            // Physicians::create($input);

            $response = array('success' => true, 'message' => $this->singular . ' Added Successfully', 'action' => 'reload');

        } catch (\Exception $e) {

            $response = array('success' => false, 'message' => $e->getMessage());

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

            'gender_selection' => ['Male', 'Female']

        ];

        $physiciansSpeciality = Specialists::all();



        $data['physiciansSpeciality'] = $physiciansSpeciality;

        return view($this->view . 'edit', $data);

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

        $validator = Validator::make($request->all(), [

            'first_name'        => 'required',

            'last_name'        => 'required',

            'contact_no'  => 'required',



            'gender'      => 'required|string',

            'speciality'     => 'sometimes|required',

        ]);

        if ($validator->fails()) {

            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);

        };

        $input = $validator->valid();

        try {

            $physician = Physicians::find($id);

            $physician->first_name = $request->first_name;

            $physician->mid_name = $request->mid_name;

            $physician->last_name = $request->last_name;

            $physician->role = $request->role;

            $physician->contact_no = $request->contact_no;

            $physician->gender = $request->gender;

            $physician->address = $request->address;

            $physician->speciality = $request->speciality;

            $physician->update();

            $response = array('success' => true, 'data' => $physician, 'message' => $this->singular . ' Updated Successfully', 'action' => 'reload');

        } catch (\Exception $e) {

            $response = array('success' => false, 'message' => $e->getMessage());

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

            if (!$note) {

                return response()->json([

                    "message" => "There is no user with this id"

                ]);

            } else {

                $note->delete();

                $response = array('success' => true, 'message' => $this->singular . ' Deleted!');

            }

        } catch (\Exception $e) {

            $response = array('success' => false, 'message' => $e->getMessage());

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