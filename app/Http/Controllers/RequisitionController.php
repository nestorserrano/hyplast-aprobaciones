<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\Product;
use App\Models\Machine;
use App\Models\User;
use App\Helpers\SchemaHelper;
use App\Models\Binnacle;
use App\Models\Extruder;
use App\Models\Subproduct;
use App\Models\Storage;
use App\Models\Supplie;
use App\Models\ProductionOrder;
use Auth;
use DB;
use Illuminate\Support\Str;
use App\Http\Requests\StoreRequisitionRequest;
use App\Http\Requests\UpdateRequisitionRequest;
use App\Traits\CaptureIpTrait;
use App\Traits\HasPermissionChecks;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Validator;
use RealRashid\SweetAlert\Facades\Alert;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class RequisitionController extends Controller
{
    use HasPermissionChecks;

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->abortIfCannot('requisitions.view');

        DB::select("exec refresh_requisitions");

        $schema = SchemaHelper::getSchema();
        $softlandDB = config('database.connections.softland.database');

        $paginationEnabled = config('hyplast.enablePagination');
        if ($paginationEnabled) {
            $requisitions = DB::table('requisitions')
                ->leftJoin("{$softlandDB}.{$schema}.ARTICULO as products", "products.ARTICULO", "=", "requisitions.product_id")
                ->leftJoin("{$softlandDB}.{$schema}.CLIENTE as clients", "clients.CLIENTE", "=", "requisitions.client_code")
                ->select(
                    "requisitions.*",
                    "products.ARTICULO as product_code",
                    "products.DESCRIPCION as product_name",
                    "clients.NOMBRE as client_name"
                )
                ->where('requisitions.finished', '=', 'false')
                ->paginate(config('hyplast.paginateListSize'));
        } else {
            $requisitions = DB::table('requisitions')
                ->leftJoin("{$softlandDB}.{$schema}.ARTICULO as products", "products.ARTICULO", "=", "requisitions.product_id")
                ->leftJoin("{$softlandDB}.{$schema}.CLIENTE as clients", "clients.CLIENTE", "=", "requisitions.client_code")
                ->select(
                    "requisitions.*",
                    "products.ARTICULO as product_code",
                    "products.DESCRIPCION as product_name",
                    "clients.NOMBRE as client_name"
                )
                ->where('requisitions.finished', '=', 'false')
                ->get();
        }

        return View('requisitions.home', compact('requisitions'));
    }

    public function usedrequisition(Request $request)
    {
        $schema = SchemaHelper::getSchema();
        $softlandDB = config('database.connections.softland.database');

        $paginationEnabled = config('hyplast.enablePagination');
        if ($paginationEnabled) {
            $requisitions = DB::table('requisitions')
                ->leftJoin("{$softlandDB}.{$schema}.ARTICULO as products", "products.ARTICULO", "=", "requisitions.product_id")
                ->leftJoin("{$softlandDB}.{$schema}.CLIENTE as clients", "clients.CLIENTE", "=", "requisitions.client_code")
                ->select(
                    "requisitions.*",
                    "products.ARTICULO as product_code",
                    "products.DESCRIPCION as product_name",
                    "clients.NOMBRE as client_name"
                )
                ->where('requisitions.finished', '=', 'true')
                ->paginate(config('hyplast.paginateListSize'));
        } else {
            $requisitions = DB::table('requisitions')
                ->leftJoin("{$softlandDB}.{$schema}.ARTICULO as products", "products.ARTICULO", "=", "requisitions.product_id")
                ->leftJoin("{$softlandDB}.{$schema}.CLIENTE as clients", "clients.CLIENTE", "=", "requisitions.client_code")
                ->select(
                    "requisitions.*",
                    "products.ARTICULO as product_code",
                    "products.DESCRIPCION as product_name",
                    "clients.NOMBRE as client_name"
                )
                ->where('requisitions.finished', '=', 'true')
                ->get();
        }

        return View('requisitions.used', compact('requisitions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->abortIfCannot('requisitions.create');

        $schema = SchemaHelper::getSchema();
        $clients = DB::connection('softland')
            ->table("{$schema}.CLIENTE")
            ->where('ACTIVO', 'S')
            ->select('CLIENTE as code', 'NOMBRE as name')
            ->orderBy('NOMBRE')
            ->get();

        $products = Product::all();
        $categories = Subproduct::where('Tipo','=','T')->orderby('DESCRIPCION')->get();
        $orderes = ProductionOrder::all();
        //$machines = Machine::all();
        $users = User::all();

        return view('requisitions.create', compact('clients','products','users','categories','orderes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function register(Request $request)
    {
        $this->abortIfCannot('requisitions.create');

        $schema = SchemaHelper::getSchema();
        $softlandDB = config('database.connections.softland.database');

        // Obtener producto de Softland con U_CODIGO_LAMINA
        $product1 = DB::connection('softland')
            ->table("{$schema}.ARTICULO")
            ->select('ARTICULO', 'PESO_NETO as net_weight', 'U_CODIGO_LAMINA')
            ->where('ARTICULO', $request->input('product'))
            ->first();

        $supplies1=$request->get('supplies');

        //dd($supplies1);

        DB::beginTransaction();

    	try {

            $requisition = new Requisition();
            $requisition->product_id = $request->product;
            $requisition->id_article = $product1->U_CODIGO_LAMINA ?? null;
            $requisition->client_code = $request->client;
            $requisition->requested = $request->requested;
            $requisition->total_weight = ($product1->net_weight * $request->requested)+ (($product1->net_weight * $request->requested) *0.10);
            $requisition->manufactured = 0;
            $requisition->manufactured_weight = 0;
            $requisition->status_id = 1;
            $requisition->user_id = Auth::User()->id;
            $requisition->notes = $request->get('notes');
           // $requisition->orden_produccion = $request->get('order');
            $requisition->orden_produccion = 0;
            $requisition->priority = $request->get('priority');
            $requisition->shifts = $request->get('shifts');
            $requisition->save();

            // Generar código de barras único con ID correlativo (max 18 dígitos para bigint)
            $date = Carbon::now();
            $dateCode = $date->format('ymdHi'); // 10 dígitos: AAMMDDHHMM (sin segundos)
            $userId = str_pad(Auth::User()->id, 3, "0", STR_PAD_LEFT); // 3 dígitos para user ID
            $correlativo = str_pad($requisition->id, 5, "0", STR_PAD_LEFT); // 5 dígitos correlativo basado en ID
            $codebar = $dateCode . $userId . $correlativo; // Total: 18 dígitos

            // Actualizar el codebar de la requisición
            $requisition->codebar = $codebar;
            $requisition->save();

            // Insertar en tabla pivot usando conexión correcta (sqlsrv)
            DB::table('products_requisitions')->insert([
                'requisition_id' => $requisition->id,
                'product_id' => $request->input('product'),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if(!is_null($supplies1)) {
                // Insertar directamente en la tabla pivot ya que supplie_id ahora es varchar
                for($i=0;$i<count($supplies1);$i++)
                {
                    DB::table('requisitions_supplies')->insert([
                        'requisition_id' => $requisition->id,
                        'supplie_id' => $supplies1[$i]['supplie'], // código del artículo (varchar)
                        'quantity' => $supplies1[$i]['required'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

        }
        catch(ValidationException $e)
        {
            DB::rollback();
            $success = false;
            $message = "Ocurrió un error agregando la formulación e insumos.";
            Alert::success('¡Lo sentimos!',$message);
            return Redirect::to('/requisitions/create')
                ->withErrors( $e->getErrors() )
                ->withInput();
        }
        catch(\Exception $e)
        {
            DB::rollback();
            throw $e;
            $success = false;
            $message = "Ocurrió un error creando la Orden de Producción, verifique la información";
            Alert::success('¡Lo sentimos!',$message);
        }

   		DB::commit();

        $success = true;
        $message = "Requisición creada correctamente";

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);

    }

    public function show(Requisition $requisition)
    {
        DB::select("exec refresh_requisitions");

        $schema = SchemaHelper::getSchema();
        $softlandDB = config('database.connections.softland.database');

        // Obtener datos con JOIN a Softland y status
        $data = DB::table('requisitions')
            ->leftJoin("{$softlandDB}.{$schema}.ARTICULO as products", "products.ARTICULO", "=", "requisitions.product_id")
            ->leftJoin("{$softlandDB}.{$schema}.ARTICULO as coils", "coils.ARTICULO", "=", "requisitions.id_article")
            ->leftJoin("{$softlandDB}.{$schema}.CLIENTE as clients", "clients.CLIENTE", "=", "requisitions.client_code")
            ->leftJoin("status", "status.id", "=", "requisitions.status_id")
            ->select(
                "requisitions.*",
                "products.ARTICULO as product_code",
                "products.DESCRIPCION as product_name",
                "products.PESO_NETO as product_peso_neto",
                "coils.ARTICULO as coil_code",
                "coils.DESCRIPCION as coil_description",
                "clients.NOMBRE as client_name",
                "status.name as status_name"
            )
            ->where('requisitions.id', $requisition->id)
            ->first();

        if (!$data) {
            Alert::error('Error', 'Orden de producción no encontrada');
            return redirect('requisitions');
        }

        // Obtener supplies usando el método personalizado del modelo
        $suppliesData = $requisition->supplies();

        // Crear objeto compatible con la vista que espera $supplies->supplies
        $supplies = (object)[
            'supplies' => $suppliesData
        ];

        // Obtener todos los rollos fabricados relacionados con esta requisición
        $extruders = DB::table('extruders')
            ->leftJoin("{$softlandDB}.{$schema}.ARTICULO as products", "products.ARTICULO", "=", "extruders.product_id")
            ->leftJoin("{$softlandDB}.{$schema}.ARTICULO as coil_article", "coil_article.ARTICULO", "=", "extruders.id_article")
            ->leftJoin("{$softlandDB}.{$schema}.CLASIFICACION as materials", "materials.CLASIFICACION", "=", "extruders.material_id")
            ->leftJoin("{$softlandDB}.{$schema}.CLASIFICACION as colors", "colors.CLASIFICACION", "=", "extruders.color_id")
            ->leftJoin("{$softlandDB}.{$schema}.U_OPERADORES as operator_init", "operator_init.U_CODIGO", "=", "extruders.operator_id")
            ->leftJoin("{$softlandDB}.{$schema}.U_OPERADORES as operator_end", "operator_end.U_CODIGO", "=", "extruders.operator_finish")
            ->select(
                "extruders.*",
                "products.DESCRIPCION as product_name",
                "coil_article.ARTICULO as coil_code",
                "coil_article.DESCRIPCION as coil_name",
                "materials.DESCRIPCION as material_name",
                "colors.DESCRIPCION as color_name",
                "operator_init.U_DESCRIP as operator_init_name",
                "operator_end.U_DESCRIP as operator_finish_name"
            )
            ->where('extruders.requisition_id', $requisition->id)
            ->orderBy('extruders.finished', 'desc')
            ->orderBy('extruders.id', 'desc')
            ->get();

        // Obtener todos los bultos fabricados relacionados con esta requisición
        $storages = DB::table('storages')
            ->leftJoin("{$softlandDB}.{$schema}.ARTICULO as products", "products.ARTICULO", "=", "storages.product_id")
            ->leftJoin("users as user_prod", "user_prod.id", "=", "storages.user_production")
            ->leftJoin("users as user_store", "user_store.id", "=", "storages.user_storage")
            ->select(
                "storages.*",
                "products.ARTICULO as product_code",
                "products.DESCRIPCION as product_name",
                DB::raw("COALESCE(products.U_CAJAS_PALETA, products.U_CAJAS_CAMADA * products.U_CAMADA_PALETA, 0) as boxes_per_pallet"),
                "user_prod.name as user_production_name",
                "user_store.name as user_storage_name"
            )
            ->where('storages.requisition_id', $requisition->id)
            ->orderBy('storages.id', 'desc')
            ->get();

        return view('requisitions.show', compact('data','supplies','extruders','storages'))->with('requisition', $data);
    }


    public function edit(Requisition $requisition)
    {
        $schema = SchemaHelper::getSchema();

        // Cargar la relación product
        $requisition->load('product');

        $clients = DB::connection('softland')
            ->table("{$schema}.CLIENTE")
            ->where('ACTIVO', 'S')
            ->select('CLIENTE as code', 'NOMBRE as name')
            ->orderBy('NOMBRE')
            ->get();

        // Obtener categorías desde Softland (CLASIFICACION_4 = Categoría)
        $categories = DB::connection('softland')
            ->table("{$schema}.CLASIFICACION")
            ->where('AGRUPACION', '4')
            ->select('CLASIFICACION', 'DESCRIPCION')
            ->orderBy('DESCRIPCION')
            ->get();

        // Filtrar productos activos y tipo Terminado
        $products = Product::where('TIPO', 'T')
            ->where('ACTIVO', 'S')
            ->orderBy('DESCRIPCION')
            ->get();
        $users = User::all();
        $data =
        [
            'clients'       => $clients,
            'products'      => $products,
            'users'         => $users,
            'categories'    => $categories,
            'requisition'   => $requisition,
        ];

        return view('requisitions.edit')->with($data);
    }


    public function update(Request $request, Requisition $requisition)
    {
        // Buscar producto por ARTICULO (PK en Softland)
        $product = Product::where('ARTICULO', $request->input('product'))->first();

        if (!$product) {
            Alert::error('Error', 'Producto no encontrado');
            return back()->withInput();
        }

        $requerido = $request->input('requested');
        // Calcular peso total usando PESO_NETO de Softland
        $total = ($product->PESO_NETO * $requerido * 2) + (($product->PESO_NETO * $requerido * 2) * 0.10);

        $validator = Validator::make($request->all(),
        [
            'client'                => 'required',
            'product'               => 'required',
            'requested'             => 'required|numeric',
        ],
        [
            'client.required'        => trans('hyplast.clientRequired'),
            'product.required'       => trans('hyplast.productRequired'),
            'requested.required'     => trans('hyplast.requestedRequired'),
        ]);


        if ($validator->fails()) {
            $message = "Error Validando los Campos, Verifique";
            Alert::error('Error',$message);
            return back()->withErrors($validator)->withInput();
        }

        $requisition->product_id = strip_tags($request->input('product'));
        $requisition->client_code = strip_tags($request->input('client'));
        $requisition->requested = strip_tags($request->input('requested'));
        $requisition->total_weight = $total;

        $requisition->manufactured = 0;
        $requisition->status_id = 1;


        $requisition->save();

        $success = true;
        $message = "Requisición actualizada correctamente";


        Alert::success('¡Felicidades!',$message);

        return back()->with('success', trans('hyplast.createSuccess'));
    }

    public function approve($id)
    {
        $this->abortIfCannot('requisitions.approve');

        try {
            $requisition = Requisition::findOrFail($id);

            // Verificar que no esté ya aprobada
            if ($requisition->approved_by) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta requisición ya fue aprobada anteriormente.'
                ], 200);
            }

            // Aprobar la requisición
            $requisition->approved_by = auth()->id();
            $requisition->approved_at = now();
            $requisition->save();

            return response()->json([
                'success' => true,
                'message' => 'La requisición/orden de producción ha sido aprobada correctamente.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al aprobar requisición: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No se pudo aprobar la requisición.'
            ], 500);
        }
    }

    public function destroy(Requisition $requisition)
    {
        //
    }

    public function delete($id)
    {
        $requisition = Requisition::find($id);

        $requisition->products()->detach($requisition->product_id);


        $delete = Requisition::where('id', $id)->delete();
        if ($delete == 1) {
            $success = true;
            $message = "Orden eliminada Correctamente";
        } else {
            $success = true;
            $message = "Esta Orden no puede ser Eliminada ya que inició su producción.";
       }
        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function print($id)
    {
        $schema = SchemaHelper::getSchema();
        $softlandDB = config('database.connections.softland.database');

        // Obtener datos con JOIN a Softland incluyendo todos los campos del producto
        $requisition = DB::table('requisitions')
            ->leftJoin("{$softlandDB}.{$schema}.ARTICULO as products", "products.ARTICULO", "=", "requisitions.product_id")
            ->leftJoin("{$softlandDB}.{$schema}.ARTICULO as lamina", "lamina.ARTICULO", "=", "products.U_CODIGO_LAMINA")
            ->leftJoin("{$softlandDB}.{$schema}.CLIENTE as clients", "clients.CLIENTE", "=", "requisitions.client_code")
            ->leftJoin("status", "status.id", "=", "requisitions.status_id")
            ->leftJoin("users", "users.id", "=", "requisitions.user_id")
            ->leftJoin("users as approver", "approver.id", "=", "requisitions.approved_by")
            ->leftJoin("{$softlandDB}.{$schema}.CLASIFICACION as material", function($join) use ($schema) {
                $join->on("products.CLASIFICACION_5", "=", "material.CLASIFICACION")
                     ->where("material.AGRUPACION", "=", 5);
            })
            ->leftJoin("{$softlandDB}.{$schema}.CLASIFICACION as color", function($join) use ($schema) {
                $join->on("products.CLASIFICACION_6", "=", "color.CLASIFICACION")
                     ->where("color.AGRUPACION", "=", 6);
            })
            ->select(
                "requisitions.*",
                "products.U_CODIGO_LAMINA as product_code",
                "products.DESCRIPCION as product_name",
                "lamina.DESCRIPCION as lamina_description",
                "products.U_ONZAS",
                "products.U_DIAMETRO",
                "products.U_NAVE",
                "products.U_MAQUINA",
                "products.U_CAVIDADES",
                "products.U_CAJA",
                "products.U_CAJA_MED",
                "products.PESO_NETO as net_weight",
                "products.U_CANT_PAQ",
                "products.U_PAQ_CAJA",
                "products.U_UNID_CAJA",
                "products.U_CAJAS_CAMADA",
                "products.U_CAMADA_PALETA",
                "products.U_CAJAS_PALETA",
                "clients.NOMBRE as client_name",
                "status.name as status_name",
                "material.DESCRIPCION as material_name",
                "color.DESCRIPCION as color_name",
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as user_full_name"),
                "users.first_name as user_first_name",
                "users.last_name as user_last_name",
                "approver.first_name as approver_first_name",
                "approver.last_name as approver_last_name",
                "requisitions.approved_at"
            )
            ->where('requisitions.id', $id)
            ->first();

        // Obtener supplies usando el método del modelo Requisition
        $requisitionModel = Requisition::find($id);
        $requisition->supplies = $requisitionModel ? $requisitionModel->supplies() : collect();

        if (ISSET($requisition->date_fabrication_init))
        {
            $inicio = Carbon::parse($requisition->date_fabrication_init);
            $fin = $requisition->date_fabrication_finish ? Carbon::parse($requisition->date_fabrication_finish) : null;
            $fecha = Carbon::now();

            if (is_null($requisition->date_fabrication_finish)) {
                $intervaloH = 'Iniciado ' . $inicio->diffForHumans([
                    'parts' => 2,
                    'join' => ' y '
                ]);

            } else {
                $intervaloH = 'Finalizado ' . $fin->diffForHumans([
                    'parts' => 2,
                    'join' => ' y '
                ]);

            }

        }
        else
        {
            $intervaloH = 'No iniciado';
        }
        //dd($requisition);
         $view = \View::make('requisitions.print.printorder', compact('requisition','intervaloH'))->render();
         $pdf = \App::make('dompdf.wrapper');
         $name = 'OP-' . $id . '.pdf';
         $pdf = \PDF::loadHTML($view);
         return $pdf->stream($name);

    }



    public function reqmachine($id)
    {
        try {
            $requisition = Requisition::findOrFail($id);

            if($requisition->manufactured_weight > 0 || $requisition->manufactured > 0 || $requisition->status_id < 1)
            {
                $schema = \App\Helpers\SchemaHelper::getSchema();

                // Obtener códigos de máquinas desde U_MAQUINAS_ARTICULOS
                $machineCodes = \DB::connection('softland')
                    ->table("{$schema}.U_MAQUINAS_ARTICULOS")
                    ->where('ARTICULO', $requisition->product_id)
                    ->pluck('U_CODIGO');

                if ($machineCodes->isEmpty()) {
                    return [
                        'message' => 'El producto no tiene máquinas asignadas en Softland',
                        'success' => false,
                    ];
                }

                // Obtener datos de las máquinas desde U_MAQUINAS
                $machines = \DB::connection('softland')
                    ->table("{$schema}.U_MAQUINAS")
                    ->whereIn('U_CODIGO', $machineCodes)
                    ->select('U_CODIGO', 'U_DESCRIP')
                    ->get();

                if ($machines->isEmpty()) {
                    return [
                        'message' => 'No se encontraron las máquinas en el sistema',
                        'success' => false,
                    ];
                }

                // Formatear datos para el frontend
                $machinesFormatted = $machines->map(function($machine) {
                    return [
                        'id' => $machine->U_CODIGO,
                        'name' => $machine->U_DESCRIP
                    ];
                });

                return [
                    'message' => $machinesFormatted,
                    'success' => true,
                ];
            }
            else
            {
                return [
                    'message' => "Esta Orden no ha comenzado...",
                    'success' => false,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error en reqmachine: ' . $e->getMessage());
            return [
                'message' => 'Error al cargar las máquinas: ' . $e->getMessage(),
                'success' => false,
            ];
        }
    }

    public function categoryproducts($id)
    {

        $category_id = $id;
        $schema = \App\Helpers\SchemaHelper::getSchema();
        // Buscar la CLASIFICACION (código) y asegurarse que AGRUPACION=4
        $clasificacion = \DB::connection('softland')
            ->table("{$schema}.CLASIFICACION")
            ->where('AGRUPACION', 4)
            ->where('clasificacion', $category_id)
            ->first();
        if (!$clasificacion) {
            return response()->json(['success' => false, 'message' => 'Categoría no encontrada', 'products' => []]);
        }
        $clasificacion_codigo = $clasificacion->CLASIFICACION;
        // Buscar artículos con CLASIFICACION_4 igual al código de la categoría
        $productos = \DB::connection('softland')
            ->table("{$schema}.ARTICULO")
            ->select('ARTICULO as code', 'DESCRIPCION as name')
            ->where('CLASIFICACION_4', $clasificacion_codigo)
            ->where('TIPO', 'T')
            ->where('ACTIVO', 'S')
            ->orderBy('DESCRIPCION')
            ->get();
        return $productos;
    }

    public function requisitions_coils($id, $codebar)
    {
        $extruder = Extruder::where('codebar','=',$codebar)->first();

        if(!$extruder){
            $message = "Bobina no encontrada, verifique el código de barras, se recomienda verificar";
            $success = false;
        }
        else
        {
            if( $extruder->user_used != null) {
                $message = "Bobina utilizada en otra máquina, verifique el código de Barras";
                $success = false;
            } elseif($extruder->requisition_id != $id)
            {
                $message = "Bobina no pertenece a esta orden de Producción";
                $success = false;
            } elseif(is_null($extruder->quality_active) || is_null($extruder->user_create_quality)) {
                $message = "Bobina no está Aprobada por el Departamento de Calidad";
                $success = false;
            }
            else {
                $message="";
                $success = true;
            }
        }

        $data = [
            'extruder'         => $extruder,
            'message'          => $message,
            'success'          => $success,
        ];

        return $data;
    }

    public function assign_coil($id, $machine, $codebar)
    {
        DB::beginTransaction();

    	try {
            $extruder = Extruder::where('codebar', '=', $codebar)->first();

            if (!$extruder) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Bobina no encontrada'
                ]);
            }

            $extruder->date_used =  Carbon::now();
            $extruder->user_used = Auth::User()->id;
            $extruder->status = false;
            $extruder->save();

            // Buscar máquina por U_CODIGO (varchar) en lugar de id
            $machine2 = Machine::where('U_CODIGO', $machine)->first();

            if (!$machine2) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Máquina no encontrada'
                ]);
            }

            if($machine2->actual_incident_id != null){
                $binnacle2 = Binnacle::where('id', '=', $machine2->actual_incident_id)
                    ->where('machine_id', '=', $machine)
                    ->first();

                if ($binnacle2) {
                    $binnacle2->date_incident_finish = Carbon::now();
                    $binnacle2->save();
                }

                $binnacle = Binnacle::create([
                    'case'                  => 'Asignación de Bobina para la Requisición OP-' . $id,
                    'message'               => 'Se entrega bobina ' . $codebar . ' Máquina: ' . $machine . ' - ' . $machine2->U_DESCRIP,
                    'machine_id'            => $machine,
                    'date_incident_init'    => Carbon::now(),
                    'incident_id'           => 18,
                    'status'                => 1,
                ]);
                $binnacle->save();

                $machine2->actual_incident_id = $binnacle->id;
                $machine2->status = false;
                $machine2->save();
            }
            else
            {
                $binnacle = Binnacle::create([
                    'case'                  => 'Asignación de Bobina para la Requisición OP-' . $id,
                    'message'               => 'Se entrega bobina ' . $codebar . ' Máquina: ' . $machine . ' - ' . $machine2->U_DESCRIP,
                    'machine_id'            => $machine,
                    'date_incident_init'    => Carbon::now(),
                    'incident_id'           => 18,
                    'status'                => 1,
                ]);
                $binnacle->save();

                $machine2->actual_incident_id = $binnacle->id;
                $machine2->actual_incident_name = "Cambio de Bobina";
                $machine2->status = false;
                $machine2->save();
            }

            // Crear relación en extruders_machines
            DB::table('extruders_machines')->insert([
                'extruder_id' => $extruder->id,
                'machine_id' => $machine,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bobina Asignada a la Máquina seleccionada',
            ]);
        }
        catch(ValidationException $e)
        {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . $e->getMessage()
            ]);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar bobina: ' . $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bobina Asignada a la Máquina seleccionada',
        ]);
    }

    public function cancelreq($id, $status)
    {
        $requisition = Requisition::where('id', '=', $id)->first();
        $requisition->date_fabrication_finish =  Carbon::now();
        $requisition->user_close = Auth::User()->id;
        $requisition->status_id = $status;
        $requisition->finished = 1;
        $requisition->save();

        $success = true;
        $message = "Orden Finalizada Correctamente. Puede Visualizarla pulsando el Botón Finalizadas";

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);

    }

    public function requisitions_register($id)
    {
        $schema = SchemaHelper::getSchema();
        $softlandDB = config('database.connections.softland.database');

        $requisition = Requisition::find($id);

        // Consultar producto de Softland
        $product = DB::connection('softland')
            ->table("{$schema}.ARTICULO")
            ->select(
                'ARTICULO as code',
                'DESCRIPCION as name',
                DB::raw('CAST(U_CAJAS_CAMADA AS INT) as box_litter'),
                DB::raw('CAST(U_CAMADA_PALETA AS INT) as platform_litter'),
                DB::raw('CAST(U_CAJAS_PALETA AS INT) as dunnage_size')
            )
            ->where('ARTICULO', $requisition->product_id)
            ->first();

        // Consultar máquinas que fabrican este producto desde Softland
        $machines = DB::connection('softland')
            ->table("{$schema}.U_MAQUINAS_ARTICULOS")
            ->join("{$schema}.U_MAQUINAS", 'U_MAQUINAS_ARTICULOS.U_CODIGO', '=', 'U_MAQUINAS.U_CODIGO')
            ->select(
                'U_MAQUINAS.U_CODIGO as code',
                'U_MAQUINAS.U_DESCRIP as name',
                'U_MAQUINAS.U_CATEGORIA_MAQ as category_machine_id'
            )
            ->where('U_MAQUINAS_ARTICULOS.ARTICULO', $requisition->product_id)
            ->get();

        $data = [
            'product'   => $product,
            'machines'  => $machines,
        ];

        return response()->json($data);
    }

    public function register_storage(Request $request)
    {
        $schema = SchemaHelper::getSchema();
        $softlandDB = config('database.connections.softland.database');

        $id = $request->input('id');
        $machine = $request->input('machine');
        $quantity = $request->input('quantity');

        $requisition = Requisition::find($id);

        // Usar el codebar de la requisición como lote
        $codebar = $requisition->codebar;
        $batch = $codebar; // El batch es el mismo que el codebar de la requisición

        // Consultar producto de Softland
        $product = DB::connection('softland')
            ->table("{$schema}.ARTICULO")
            ->select(
                'ARTICULO as code',
                'CODIGO_BARRAS_INVT as barcode',
                'BULTOS as box_litter',
                'U_CAMADA_PALETA as platform_litter',
                'U_PESO_UNIDAD as unit_weight',
                'PESO_NETO as net_weight',
                'PESO_BRUTO as gross_weight'
            )
            ->where('ARTICULO', $requisition->product_id)
            ->first();

        // Consultar máquina desde Softland
        $machine_cat = DB::connection('softland')
            ->table("{$schema}.U_MAQUINAS")
            ->select('U_DESCRIP as name')
            ->where('U_CODIGO', $machine)
            ->first();

        // VALIDACIÓN: Verificar que hay suficientes bobinas disponibles
        $peso_necesario = (float)($product->net_weight ?? 0) * (int)$quantity;

        $peso_disponible = DB::table('extruders')
            ->where('requisition_id', $id)
            ->where('status', true)           // Disponible (no consumida)
            ->where('finished', true)         // Terminada
            ->where('quality_active', true)   // Pasó calidad
            ->sum('weight');

        if ($peso_disponible < $peso_necesario) {
            $faltante = $peso_necesario - $peso_disponible;
            return response()->json([
                'success' => false,
                'message' => "No hay suficientes bobinas disponibles.\n" .
                            "Peso necesario: " . number_format($peso_necesario, 2) . " kg\n" .
                            "Peso disponible: " . number_format($peso_disponible, 2) . " kg\n" .
                            "Faltante: " . number_format($faltante, 2) . " kg"
            ]);
        }

        DB::beginTransaction();

    	try {

            $binnacle2 = Binnacle::where('machine_id', '=', $machine)->first();
            if ($binnacle2) {
                $binnacle2->date_incident_finish = Carbon::now();
                $binnacle2->save();
            }

            $store = Storage::create([

                'date_production'               => Carbon::now(),
                'codebar'                       => $codebar,
                'batch'                         => $batch,
                'box_litter'                    => (int)($product->box_litter ?? 0),
                'platform_litter'               => (int)($product->platform_litter ?? 0),
                'quantity'                      => (int)$quantity,
                'unit_weight'                   => (float)($product->unit_weight ?? 0),
                'net_weight'                    => (float)($product->net_weight ?? 0),
                'gross_weight'                  => (float)($product->gross_weight ?? 0),
                'total_weight'                  => (float)(($product->net_weight ?? 0) * $quantity),
                'requisition_id'                => $requisition->id,
                'product_id'                    => $requisition->product_id,
                'machine_id'                    => (int)$machine,
                'user_production'               => Auth::User()->id,
            ]);

            $store->save();


            $binnacle = Binnacle::create([
                'case'                  => 'Se registra producción para la Orden OP-' . $requisition->id,
                'message'               => 'Se registra el ingreso de producción de ' . $quantity . ' bultos pertenecientes a la OP-' . $requisition->id . ' Código de Barras: ' . $codebar . ' Máquina: ' . ($machine_cat->name ?? 'N/A'),
                'machine_id'            => $machine,
                'date_incident_init'    => Carbon::now(),
                'date_incident_finish'  => Carbon::now(),
                'incident_id'           => 40,
                'status'                => 1,
            ]);
            $binnacle->save();

        }

        catch(ValidationException $e)
        {
            DB::rollback();
	        return Redirect::to('/requisitions')
		        ->withErrors( $e->getErrors() )
		        ->withInput();
        }
        catch(\Exception $e)
        {
	        DB::rollback();
	        throw $e;
        }

        DB::commit();


        $success = true;
        $message = "Registro realizado correctamente. Seleccione el registro en la Sección almacen e Imprima la Etiqueta";

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function orderproduct($id)
    {
        $schema = SchemaHelper::getSchema();
        $softlandDB = config('database.connections.softland.database');

        $orderes = ProductionOrder::where('ORDEN_PRODUCCION', '=', $id)->first();

        // Consultar producto de Softland
        $product = DB::connection('softland')
            ->table("{$schema}.ARTICULO")
            ->select('DESCRIPCION as name')
            ->where('ARTICULO', $orderes->ARTICULO)
            ->get();

        return $product;
    }

}
