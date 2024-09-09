<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramsCollection;
use App\Models\Programs;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Helpers\Utility;
use Validator;
use Auth;
class ProgramController extends Controller
{
    protected $singular = "Program";
    protected $plural   = "Programs";
    protected $per_page = '';
	
    public function __construct()
    {
	    $this->per_page = Config('constants.perpage_showdata');
	}

    public function index(Request $request)
    {
        try {
            $query  = new Programs();

            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->get('search');
                $query = $query->where('name', 'like', '%' . $search . '%');
            }
            $clinicIds = @$request->input('clinic_id') ?? "";
            $query = $query->when(!empty($clinicIds), function ($query) use ($clinicIds) {
                $query->orWhereRaw("FIND_IN_SET('$clinicIds', clinic_id)");
            });

            $row = $query->paginate($this->per_page);
            $total = $row->total();
            $current_page = $row->currentPage();
            $programs = ProgramsCollection::collection($row);
            
            $key = $request->get('search');
            $clinicList = Clinic::select('id','name')->get()->toArray();

            return response()->json([
                'success' => true,
                'total' => $total,
                'current_page' => $current_page,
                'per_page' => $this->per_page,
                'data' => $programs,
                'clinic_list' => $clinicList 
            ], 200);
            
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }
    
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'short_name'  => 'required'
        ]);
        

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        try {
            $input['created_user'] = Auth::id();
            $input['slug'] = Str::slug($request->name);
            $input['name'] = $request->name;
            $input['short_name'] = $request->short_name;
            $input['clinic_id'] = $request->has('clinic_id') ? implode(',', $request->clinic_id) : 1;
            
            Utility::appendRoles($input);

            $program = Programs::create($input);
            
            return response()->json([
                'success' => true,
                'data' => $program,
                "message" => "Program Added Successfully",
            ]);
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
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
        $note = Programs::find($id);
        
        try {
            $note->name = $request->name;
            $note->short_name = $request->short_name;
            $note->created_user = Auth::id();
            $note->slug = Str::slug($request->name);
            $note->clinic_id = implode(',', $request->clinic_id);
            $note->update();
        
            return response()->json([
                "success" => true,
                "message" => "Program has been edited successfully"
            ], 200);
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }

        return response()->json($response);
    }

    public function destroy($id)

    {

        try {

            $note = Programs::find($id);

            $note->delete();

            return response()->json([

                "success" => true,

                "message" => "Program deleted successfully"

            ], 200);

        } catch (\Exception $e) {

            $response = array('success' => false, 'message' => $e->getMessage());

        }

        return response()->json($response);

    }

}

