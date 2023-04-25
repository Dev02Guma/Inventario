<?php
namespace App\Http\Controllers;

use App\Models\Articulos;
use Illuminate\Http\Request;
use App\Models\TablaArticulos;
use App\Models\ArticuloKardex;

class InventarioController extends Controller {
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function getHome()
    {        
        $Productos      = Articulos::getArticulos();  
        return view('Inventario.Home', compact('Productos'));
   
    }

    public function postGuardarInventario(Request $request)
    {
        $response = TablaArticulos::GuardarListas($request);
        return response()->json($response);
    }
    public function postKardex(Request $request)
    {
        $response = ArticuloKardex::getKardex($request);
        return response()->json($response);
    }
    public function InitKardex(Request $request)
    {
        ArticuloKardex::InitKardex($request);
    }

    public function getKardex(Request $request)
    {
        $d1 = $request->input('startDate');
        $d2 = $request->input('endDate');

        $Kardex = ArticuloKardex::getReporteKardex($request);
        return view('Inventario.Kardex', compact('Kardex','d1','d2'));
    }

    public function GuardarCantidad(Request $request)
    {
        
        TablaArticulos::GuardarCantidad($request);
        
        return back()->withInput();
    }
}  