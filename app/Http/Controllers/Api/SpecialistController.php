<?php



namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\SpecialistCollection;
use App\Helpers\Utility;

use App\Models\Specialists;
use App\Models\Clinic;

use Auth, Validator;



class SpecialistController extends Controller

{

protected $per_page = '';
public function __construct(){
$this->per_page = Config('constants.perpage_showdata');

}



    public function index(Request $request)

    {

      //protected $per_page = 10;

        try {

            $query  = new Specialists();

            if ($request->has('search') && !empty($request->input('search'))) {

                $search = $request->get('search');

                $query = $query->where('name', 'like', '%' . $search . '%');

            }



            $row = $query->paginate($this->per_page);

            $total = $row->total();

            $current_page = $row->currentPage();

            $specialists = SpecialistCollection::collection($row);

            $clinicList = Clinic::select('id','name')->get()->toArray();

            $key = $request->get('search');



            return response()->json([
                'success' => true,
                'total' => $total,
                'current_page' => $current_page,
                'data' => $specialists,
                'clinic_list' => $clinicList,
            ]);

        } catch (\Exception $e) {

            $response = array('success' => false, 'message' => $e->getMessage());

        }

    }



    public function list(Request $request)

    {

        try {

            $row  = Specialists::all();

            $specialists = SpecialistCollection::collection($row);





            return response()->json([

                'success' => true,

                'data' => $specialists

            ]);

        } catch (\Exception $e) {

            $response = array('success' => false, 'message' => $e->getMessage());

        }

    }



    public function single($id)

    {

        try {

            $specialist = Specialists::find($id);

            if (!$specialist) {

                return response()->json([

                    "message" => "Specialist not found"

                ]);

            }

            $specialist_resource = new SpecialistCollection($specialist);

            return response()->json([

                'specialist' => $specialist_resource

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

            'action'   => $this->action

        ];

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
            'name' => 'required',
            'short_name'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $validator->valid();
        try {
            $input = $request->all();
            $input['clinic_id'] = $request->clinic_id ?? 1;
            Utility::appendRoles($input);
            
            $specialist = Specialists::create($input);

            return response()->json([
                'success' => true,
                'data'  => $specialist,
                "message" => "Data Added Successfully"
            ]);

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

            'row'      => Specialists::find($id)->toArray()

        ];

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

            'name' => 'required',

            'short_name'  => 'sometimes|required'

        ]);

        if ($validator->fails()) {

            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);

        };

        $input = $validator->valid();

        $note = Specialists::find($id);

        try {

            $note->update($input);

            return response()->json([

                'success' => true,

                'data' => $note,

                "message" => "Specialist updated successfully"

            ]);

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

            $note = Specialists::find($id);

            $note->delete();

            return response()->json([

                'success' => true,

                "message" => "Specialist deleted successfully"

            ]);

        } catch (\Exception $e) {

            $response = array('success' => false, 'message' => $e->getMessage());

        }

        return response()->json($response);

    }

    public function lists()

    {

        try {

            $specialists = Specialists::paginate(20);

            $specialists_collection = SpecialistCollection::collection($specialists);

            return response()->json([

                "list" => $specialists_collection

            ]);

        } catch (\Exception $e) {

            $response = array('success' => false, 'message' => $e->getMessage());

        }

    }

    public function filter(Request $request)

    {

        $query  = new Specialists();

        if ($request->has('search') && !empty($request->input('search'))) {

            $search = $request->get('search');

            $query = $query->where('name', 'like', '%' . $search . '%');

        }



        $row = $query->get();



        $key = $request->get('search');



        return response()->json([

            'filtered_data' => $row

        ]);

    }

}