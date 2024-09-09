<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Specialists;
use Auth, Validator;

class SpecialistsController extends Controller
{
    protected $singular = "Specialists";
    protected $plural   = "Specialists";
    protected $action   = "/dashboard/specialists";
    protected $view     = "specialists.";

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            'singular' => $this->singular,
            'plural'   => $this->plural,
            'action'   => $this->action,
            'page_title' => $this->singular . ' List'
        ];
        $list = Specialists::select('id', 'name', 'short_name')->get()->toArray();
        $data['list'] = $list;
        return view($this->view . 'list', $data);
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
            $input['created_user'] = Auth::id();
            Specialists::create($input);
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
            $response = array('success' => true, 'message' => $this->singular . ' Updated Successfully', 'action' => 'reload');
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
            $response = array('success' => true, 'message' => $this->singular . ' Deleted!');
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }
}
