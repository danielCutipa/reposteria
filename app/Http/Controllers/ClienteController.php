<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('cliente.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('cliente.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $requestData = $request->all();
        $requestData = array_add($requestData, 'tipo', 'comun');
        $cliente = Cliente::create($requestData);
        if ($request->key == "pasteleria_el_amor_es_dulce") {
            return response()->json([
                'message' => 'Usuario registrado', 
                'id' => $cliente->id,
                '_token' => csrf_token(),
            ]);
        }
        return redirect('cliente');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $cliente = Cliente::findOrFail($id);
        return view('cliente.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id, $key = NULL)
    {
        $cliente = Cliente::findOrFail($id);
        if ($key == "pasteleria_el_amor_es_dulce") {
            return response()->json(['cliente' => $cliente]);
        }
        return view('cliente.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $requestData = $request->all();
        
        $cliente = Cliente::findOrFail($id);
        $cliente->update($requestData);
        if ($request->key == "pasteleria_el_amor_es_dulce") {
            return response()->json(['message' => 'Usuario modificado']);
        }
        return redirect('cliente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        Cliente::destroy($id);

        return redirect('cliente')->with('flash_message', 'Cliente deleted!');
    }

    public function searchByCi(Request $request){
        $cliente = Cliente::where('ci', '=', $request->ci)->first();
        return response()->json(['cliente' => $cliente]);
    }
    
    public function getDataTable()
    {
        $model = Cliente::select(['id', 'nombre', 'ci', 'direccion', 'telefono', 'celular', 'email']);

        return datatables()->of($model)
            ->addColumn('action', function ($model) {
                return 
                '<a href="/cliente/'.$model->id.'" class="btn btn-info btn-sm waves-effect waves-light" title="Ver"><i class="far fa-eye"></i></a>
                <a href="/cliente/'.$model->id.'/edit" class="btn btn-primary btn-sm waves-effect waves-light" title="Editar"><i class="far fa-edit"></i></a>
                <a href="/cliente/'.$model->id.'" class="btn btn-danger btn-sm waves-effect waves-light" title="Eliminar"><i class="far fa-trash-alt"></i></a>';
            })
            ->editColumn('id', 'ID: {{$id}}')
            ->make(true);
    }
}
