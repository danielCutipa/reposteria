<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Cliente;
use App\User;
use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Hash;

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
        if ($this->verifyEmail($request['email'])) {
            Session::flash('message','el Email se encuentra registrado!! Por favor use otro Email.');
            return redirect('cliente/create');
        }
        if ($this->verifyCI($request['ci'])) {
            Session::flash('message','el CI se encuentra registrado!!.');
            return redirect('cliente/create');
        }
        $requestData = $request->all();
        $requestData = array_add($requestData, 'tipo', 'comun');
        $requestData = array_add($requestData, 'rol', 'cliente');
        
        if ($request['direccion'] || $request['telefono'] || $request['celular'] || $request['email']) {
            array_set($array, 'password', Hash::make($request['password']));
            $user = User::create($requestData);
            $requestData = array_add($requestData, 'user_id', $user->id);
        }
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
        $cliente = Cliente::findOrFail($id);
        if ($this->verifyEmail($request['email'], $cliente->user_id)) {
            Session::flash('message','el Email se encuentra registrado!! Por favor use otro Email.');
            return redirect('cliente/'.$cliente->id.'/edit');
        }
        if ($this->verifyCI($request['ci'], $cliente->id)) {
            Session::flash('message','el CI se encuentra registrado!!.');
            return redirect('cliente/'.$cliente->id.'/edit');
        }

        $requestData = $request->all();

        if ($request['direccion'] || $request['telefono'] || $request['celular'] || $request['email']) {
            array_set($array, 'password', Hash::make($request['password']));
            if ($cliente->user_id) {
                // tiene un registro en user, lo modificamos
                $user = User::findOrFail($cliente->user_id);
                $user->update($requestData);
            } else {
                // no tiene registro en user, se crea uno nuevo
                $user = User::create($requestData);
                $requestData = array_add($requestData, 'user_id', $user->id);
            }
        }

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

        return redirect('cliente');
    }

    public function searchByCi(Request $request){
        $cliente = Cliente::where('ci', '=', $request->ci)->first();
        return response()->json(['cliente' => $cliente]);
    }
    
    public function getDataTable()
    {
        $model = Cliente::select(['cliente.id', 'nombre', 'ci', 'users.celular', 'users.email'])
        ->leftJoin('users', 'cliente.user_id', '=', 'users.id');

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

    public function reporteCliente()
    {
        $data = Cliente::select(['nombre', 'ci', 'telefono', 'celular', 'email', 'created_at'])->get();
        return view('reportes.clientes', compact('data'));
    }

    private function verifyEmail($email, $user_id = NULL)
    {
        $existe = $user_id ?
        User::where([['email', '=', $email], ['id', '<>', $user_id]])->first() :
        User::where(['email' => $email])->first();
        
        return $existe ? true : false;
    }

    private function verifyCI($ci, $id = NULL)
    {
        $existe = $id ? 
        Cliente::where([['ci', '=', $ci], ['id', '<>', $id]])->first() : 
        Cliente::where(['ci' => $ci])->first();

        return $existe ? true : false;
    }
}
