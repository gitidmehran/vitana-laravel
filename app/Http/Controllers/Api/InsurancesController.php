<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InsuranceCollection;
use App\Http\Resources\ProgramsCollection;
use Illuminate\Http\Request;
use App\Models\Insurances;
use App\Models\Clinic;
use App\Models\Programs;
use App\Helpers\Utility;
use Auth, Validator, Str;

class InsurancesController extends Controller
{
    protected $singular = "Insurance";
    protected $plural   = "Insurances";
    protected $action   = "/dashboard/insurances";
    protected $view     = "insurances.";


    protected $per_page = '';
    public function __construct(){
       $this->per_page = Config('constants.perpage_showdata');
   }


   public function index(Request $request)
   {
    try {
        $query  =  Insurances::with('clinic');
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->get('search');
            $query = $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->has('clinic_id') && !empty($request->input('clinic_id'))) {
            $clinicIds = explode(',', $request->input('clinic_id'));
            $query = $query->whereIn('clinic_id', $clinicIds);
        }

        $row = $query->paginate($this->per_page);
        $total = $row->total();
        $current_page = $row->currentPage();
        $list = $row->toArray();

        // $insurances = InsuranceCollection::collection($row);
        $insurances = [];
        if(!empty($list['data'])){
            foreach ($list['data'] as $key => $value) {
                $insurances[] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'short_name' => $value['short_name'],
                    'clinic_id' => $value['clinic_id'] ?? '',
                    'type_id' => $value['type_id'] ?? '',
                    'provider' => $value['provider'] ?? '',
                    'clinic' => $value['clinic']['name'] ?? '',
                ];
            }
        }
        $clinicList = Clinic::select('id','name')->get()->toArray();
        return response()->json([
            'success' => true,
            'total' => $total,
            'current_page' => $current_page,
            'per_page' => $this->per_page,
            'data' => $insurances,
            'clinic_list' => $clinicList,
        ]);
    } catch (\Exception $e) {
        $response = array('success' => false, 'message' => $e->getMessage());
    }
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
            'short_name'  => 'required',
            'type_id' => 'required',
            'provider' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };
        $input = $validator->valid();
        
        try {
            $input['clinic_id'] = $request->clinic_id ?? 1;
            Utility::appendRoles($input);
            
            $insurance = Insurances::create($input);
            $response = [
                'success' => true,
                'message' => "Insurance Added Successfully",
                'data' => $insurance
            ];
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
        $note = Insurances::find($id);
        try {

            $note->update($input);
            $response = array('success' => true, 'data' => $note, 'message' => $this->singular . ' Updated Successfully', 'action' => 'reload');
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
            $note = Insurances::find($id);
            $note->delete();
            $response = array('success' => true, 'message' => $this->singular . ' Deleted!');
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }
}