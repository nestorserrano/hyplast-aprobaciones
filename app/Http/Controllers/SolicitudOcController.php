<?php

namespace App\Http\Controllers;

use App\Models\SolicitudOc;
use App\Models\SolicitudOcLinea;
use App\Models\SolicitudOcAuditoria;
use App\Models\SolicitudOcLineaAuditoria;
use App\Models\Departamento;
use App\Models\User;
use App\Models\Conjunto;
use App\Traits\HasPermissionChecks;
use App\Helpers\ButtonHelper;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;

class SolicitudOcController extends Controller
{
    use HasPermissionChecks;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Verificar permiso usando el trait (funciona con roles)
        if (!$this->checkPermission('solicitudes.view')) {
            Alert::error('Acceso Denegado', 'No tienes permiso para ver solicitudes de compra.');
            return redirect()->route('home');
        }

        // Obtener departamentos para filtro
        $departamentos = Departamento::where('ACTIVO', 'S')
            ->orderBy('DESCRIPCION')
            ->get();

        // Obtener filtro desde URL si existe
        $filtroEstado = $request->get('estado', null);

        return view('pages.solicitud_oc.index', compact('departamentos', 'filtroEstado'));
    }

    /**
     * Obtener contadores de solicitudes para dashboard
     */
    public function getCounters()
    {
        if (!$this->checkPermission('solicitudes.view')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $total = SolicitudOc::count();
        $aprobadas = SolicitudOc::whereNotNull('AUTORIZADA_POR')->count();
        $porAprobar = SolicitudOc::whereNull('AUTORIZADA_POR')->count();

        return response()->json([
            'total' => $total,
            'aprobadas' => $aprobadas,
            'por_aprobar' => $porAprobar
        ]);
    }

    /**
     * Get data for DataTables
     */
    public function data(Request $request)
    {
        $query = SolicitudOc::with([
            'usuarioSolicitante',
            'departamento',
            'centroCosto',
            'usuarioAutoriza',
            'lineas.articulo'
        ]);

        // Filtro por departamento
        if ($request->filled('departamento')) {
            $query->where('DEPARTAMENTO', $request->departamento);
        }

        // Filtro por estado de aprobación
        if ($request->filled('aprobada')) {
            if ($request->aprobada == '1') {
                $query->whereNotNull('AUTORIZADA_POR');
            } elseif ($request->aprobada == '0') {
                $query->whereNull('AUTORIZADA_POR');
            }
        }

        // Filtro por prioridad
        if ($request->filled('prioridad')) {
            $query->where('PRIORIDAD', $request->prioridad);
        }

        // Filtro por fecha
        if ($request->filled('fecha_desde')) {
            $query->where('FECHA_SOLICITUD', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('FECHA_SOLICITUD', '<=', $request->fecha_hasta);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('solicitud', function ($row) {
                return $row->SOLICITUD_OC;
            })
            ->addColumn('fecha', function ($row) {
                return Carbon::parse($row->FECHA_SOLICITUD)->format('d/m/Y');
            })
            ->addColumn('solicitante', function ($row) {
                return $row->usuarioSolicitante ? $row->usuarioSolicitante->NOMBRE : $row->USUARIO;
            })
            ->addColumn('departamento', function ($row) {
                return $row->departamento ? $row->departamento->DESCRIPCION : $row->DEPARTAMENTO;
            })
            ->addColumn('centro_costo', function ($row) {
                return $row->COMENTARIO ?: $row->NOTAS ?: '-';
            })
            ->addColumn('prioridad', function ($row) {
                $badge = $row->prioridad_badge;
                $nombre = $row->prioridad_nombre;

                // Iconos según prioridad
                $iconos = [
                    'A' => '<i class="fas fa-ambulance"></i>',
                    'M' => '<i class="fas fa-exclamation-triangle"></i>',
                    'Z' => '<i class="fas fa-check-circle"></i>',
                ];

                $icono = $iconos[$row->PRIORIDAD] ?? '<i class="fas fa-circle"></i>';

                return '<span class="badge ' . $badge . '">' . $icono . ' ' . $nombre . '</span>';
            })
            ->addColumn('total_lineas', function ($row) {
                return $row->lineas->count();
            })
            ->addColumn('estado', function ($row) {
                if ($row->esta_autorizada) {
                    $autorizada_por = $row->usuarioAutoriza ? $row->usuarioAutoriza->NOMBRE : $row->AUTORIZADA_POR;
                    $fecha = Carbon::parse($row->FECHA_AUTORIZADA)->format('d/m/Y H:i');
                    return '<span class="badge badge-success" data-toggle="tooltip" title="Aprobada por ' . $autorizada_por . ' el ' . $fecha . '">Aprobada</span>';
                } else {
                    return '<span class="badge badge-warning">Pendiente</span>';
                }
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group" role="group">';

                // Botón ver detalle
                $btn .= '<button type="button" class="btn btn-info btn-sm mr-1 view-details" data-id="' . $row->SOLICITUD_OC . '" data-toggle="tooltip" title="Ver Detalle" style="width:70px;"><i class="fas fa-eye"></i> Ver</button>';

                // Botón aprobar (solo si no está aprobada)
                if (!$row->esta_autorizada) {
                    if ($this->checkPermission('solicitudes.approve')) {
                        $btn .= '<button type="button" class="btn btn-success btn-sm mr-1 approve-request" data-id="' . $row->SOLICITUD_OC . '" data-toggle="tooltip" title="Aprobar Solicitud" style="width:110px;"><i class="fas fa-stamp"></i> Aprobar</button>';
                    }

                    // Botón eliminar (solo si no está aprobada y tiene permiso)
                    if ($this->checkPermission('solicitudes.delete')) {
                        $btn .= '<button type="button" class="btn btn-danger btn-sm mr-1 delete-request" data-id="' . $row->SOLICITUD_OC . '" data-toggle="tooltip" title="Eliminar Solicitud" style="width:80px;"><i class="fas fa-trash"></i> Eliminar</button>';
                    }
                } else {
                    // Botón desaprobar (solo si está aprobada)
                    if ($this->checkPermission('solicitudes.unapprove')) {
                        $btn .= '<button type="button" class="btn btn-warning btn-sm mr-1 unapprove-request" data-id="' . $row->SOLICITUD_OC . '" data-toggle="tooltip" title="Desaprobar Solicitud" style="width:110px;"><i class="fas fa-ban"></i> Desaprobar</button>';
                    }
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['prioridad', 'estado', 'action'])
            ->make(true);
    }

    /**
     * Get detail of a specific request
     */
    public function show($id)
    {
        // Verificar permiso
        if (!$this->checkPermission('solicitudes.show')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver detalles de solicitudes.'
            ], 403);
        }

        $solicitud = SolicitudOc::with([
            'usuarioSolicitante',
            'departamento',
            'centroCosto',
            'usuarioAutoriza',
            'lineas.articulo',
            'lineas.cuentaContable',
            'lineas.centroCosto'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $solicitud
        ]);
    }

    /**
     * Approve a purchase request
     */
    public function approve(Request $request)
    {
        try {
            // Verificar permiso
            if (!$this->checkPermission('solicitudes.approve')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para aprobar solicitudes de compra.'
                ], 403);
            }

            $solicitudId = $request->input('solicitud_id');

            // Obtener usuario autenticado
            $user = auth()->user();

            // Verificar que el usuario tenga un softland_user asignado
            if (empty($user->softland_user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Su usuario no tiene un usuario de Softland asignado. Contacte al administrador.'
                ]);
            }

            // Obtener la solicitud
            $solicitud = SolicitudOc::findOrFail($solicitudId);

            // Verificar si ya está aprobada
            if ($solicitud->esta_autorizada) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitud ya ha sido aprobada.'
                ]);
            }

            // Usar transacción para asegurar la consistencia
            DB::transaction(function () use ($solicitud, $user) {
                $solicitud->AUTORIZADA_POR = $user->softland_user;
                $solicitud->FECHA_AUTORIZADA = Carbon::now();
                $solicitud->ESTADO = 'E'; // Cambiar estado a 'E' al aprobar
                $solicitud->save();

                // Actualizar el estado de todas las líneas a 'E' (no asignada)
                DB::connection('softland')
                    ->table('C01.SOLICITUD_OC_LINEA')
                    ->where('SOLICITUD_OC', $solicitud->SOLICITUD_OC)
                    ->update(['ESTADO' => 'E']);
            });

            Alert::success('Éxito', 'La solicitud ha sido aprobada correctamente.');

            return response()->json([
                'success' => true,
                'message' => 'Solicitud aprobada correctamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar la solicitud: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Unapprove a purchase request (set AUTORIZADA_POR and FECHA_AUTORIZADA to null)
     */
    public function unapprove(Request $request)
    {
        try {
            // Verificar permiso
            if (!$this->checkPermission('solicitudes.unapprove')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para desaprobar solicitudes de compra.'
                ], 403);
            }

            $solicitudId = $request->input('solicitud_id');

            // Obtener usuario autenticado
            $user = auth()->user();

            // Verificar que el usuario tenga un softland_user asignado
            if (empty($user->softland_user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Su usuario no tiene un usuario de Softland asignado. Contacte al administrador.'
                ]);
            }

            // Obtener la solicitud
            $solicitud = SolicitudOc::findOrFail($solicitudId);

            // Verificar si no está aprobada
            if (!$solicitud->esta_autorizada) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitud no está aprobada.'
                ]);
            }

            // Usar transacción
            DB::transaction(function () use ($solicitud) {
                $solicitud->AUTORIZADA_POR = null;
                $solicitud->FECHA_AUTORIZADA = null;
                $solicitud->save();
            });

            Alert::success('Éxito', 'La solicitud ha sido desaprobada correctamente.');

            return response()->json([
                'success' => true,
                'message' => 'Solicitud desaprobada correctamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desaprobar la solicitud: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a purchase request (copy to audit table and delete from Softland)
     */
    public function destroy(Request $request)
    {
        try {
            // Verificar permiso
            if (!$this->checkPermission('solicitudes.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar solicitudes de compra.'
                ], 403);
            }

            $solicitudId = $request->input('solicitud_id');

            // Obtener usuario autenticado
            $user = auth()->user();

            // Verificar que el usuario tenga un softland_user asignado
            if (empty($user->softland_user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Su usuario no tiene un usuario de Softland asignado. Contacte al administrador.'
                ]);
            }

            // Obtener la solicitud con todas sus líneas
            $solicitud = SolicitudOc::with('lineas')->findOrFail($solicitudId);

            // Verificar si está aprobada - no se puede eliminar si está aprobada
            if ($solicitud->esta_autorizada) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar una solicitud aprobada. Primero debe desaprobarla.'
                ]);
            }

            // Usar transacción para asegurar la consistencia
            DB::transaction(function () use ($solicitud, $user) {
                // 1. Copiar encabezado a tabla de auditoría
                $auditoria = SolicitudOcAuditoria::create([
                    'SOLICITUD_OC' => $solicitud->SOLICITUD_OC,
                    'DEPARTAMENTO' => $solicitud->DEPARTAMENTO,
                    'FECHA_SOLICITUD' => $solicitud->FECHA_SOLICITUD,
                    'FECHA_REQUERIDA' => $solicitud->FECHA_REQUERIDA,
                    'AUTORIZADA_POR' => $solicitud->AUTORIZADA_POR,
                    'FECHA_AUTORIZADA' => $solicitud->FECHA_AUTORIZADA,
                    'PRIORIDAD' => $solicitud->PRIORIDAD,
                    'LINEAS_NO_ASIG' => $solicitud->LINEAS_NO_ASIG,
                    'ESTADO' => $solicitud->ESTADO,
                    'COMENTARIO' => $solicitud->COMENTARIO,
                    'FECHA_HORA' => $solicitud->FECHA_HORA,
                    'USUARIO' => $solicitud->USUARIO,
                    'USUARIO_CANCELA' => $solicitud->USUARIO_CANCELA,
                    'FECHA_HORA_CANCELA' => $solicitud->FECHA_HORA_CANCELA,
                    'RUBRO1' => $solicitud->RUBRO1,
                    'RUBRO2' => $solicitud->RUBRO2,
                    'RUBRO3' => $solicitud->RUBRO3,
                    'RUBRO4' => $solicitud->RUBRO4,
                    'RUBRO5' => $solicitud->RUBRO5,
                    'RowPointer' => $solicitud->RowPointer,
                    'NoteExistsFlag' => $solicitud->NoteExistsFlag,
                    'RecordDate' => $solicitud->RecordDate,
                    'CreatedBy' => $solicitud->CreatedBy,
                    'UpdatedBy' => $solicitud->UpdatedBy,
                    'CreateDate' => $solicitud->CreateDate,
                    'fecha_eliminacion' => Carbon::now(),
                    'usuario_eliminacion_id' => $user->id,
                    'softland_user_eliminacion' => $user->softland_user,
                    'accion' => 'ELIMINADO'
                ]);

                // 2. Copiar líneas a tabla de auditoría
                foreach ($solicitud->lineas as $linea) {
                    SolicitudOcLineaAuditoria::create([
                        'SOLICITUD_OC' => $linea->SOLICITUD_OC,
                        'SOLICITUD_OC_LINEA' => $linea->SOLICITUD_OC_LINEA,
                        'USUARIO_CANCELA' => $linea->USUARIO_CANCELA,
                        'ARTICULO' => $linea->ARTICULO,
                        'DESCRIPCION' => $linea->DESCRIPCION,
                        'CANTIDAD' => $linea->CANTIDAD,
                        'SALDO' => $linea->SALDO,
                        'ESTADO' => $linea->ESTADO,
                        'COMENTARIO' => $linea->COMENTARIO,
                        'FECHA_REQUERIDA' => $linea->FECHA_REQUERIDA,
                        'UNIDAD_DISTRIBUCIO' => $linea->UNIDAD_DISTRIBUCIO,
                        'FECHA_HORA_CANCELA' => $linea->FECHA_HORA_CANCELA,
                        'CENTRO_COSTO' => $linea->CENTRO_COSTO,
                        'CUENTA_CONTABLE' => $linea->CUENTA_CONTABLE,
                        'E_MAIL' => $linea->E_MAIL,
                        'FASE' => $linea->FASE,
                        'PROYECTO' => $linea->PROYECTO,
                        'ORDEN_CAMBIO' => $linea->ORDEN_CAMBIO,
                        'RowPointer' => $linea->RowPointer,
                        'NoteExistsFlag' => $linea->NoteExistsFlag,
                        'RecordDate' => $linea->RecordDate,
                        'CreatedBy' => $linea->CreatedBy,
                        'UpdatedBy' => $linea->UpdatedBy,
                        'CreateDate' => $linea->CreateDate,
                        'solicitud_oc_auditoria_id' => $auditoria->id
                    ]);
                }

                // 3. Eliminar líneas de Softland
                foreach ($solicitud->lineas as $linea) {
                    $linea->delete();
                }

                // 4. Eliminar encabezado de Softland
                $solicitud->delete();
            });

            Alert::success('Éxito', 'La solicitud ha sido eliminada y guardada en auditoría.');

            return response()->json([
                'success' => true,
                'message' => 'Solicitud eliminada correctamente.',
                'redirect' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la solicitud: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Print purchase request
     */
    public function print($id)
    {
        // Verificar permiso
        if (!$this->checkPermission('solicitudes.show')) {
            Alert::error('Acceso Denegado', 'No tienes permiso para ver solicitudes de compra.');
            return redirect()->route('solicitudes.index');
        }

        // Obtener solicitud con relaciones
        $solicitud = SolicitudOc::with([
            'usuarioSolicitante',
            'departamento',
            'centroCosto',
            'usuarioAutoriza',
            'lineas.articulo',
            'lineas.cuentaContable',
            'lineas.centroCosto'
        ])->findOrFail($id);

        // Obtener conjunto (información de la empresa)
        $conjunto = Conjunto::where('CONJUNTO', session('conjunto', 'C01'))->first();

        // Buscar logo
        $logoPath = null;
        if ($conjunto && $conjunto->LOGO) {
            $possibleNames = [
                $conjunto->LOGO, // Logo_C01.JPG
                'logo' . strtolower(str_replace('C0', 'c0', session('conjunto', 'C01'))) . '.jpg',
                'logo' . session('conjunto', 'C01') . '.jpg',
                strtolower($conjunto->LOGO),
            ];

            foreach ($possibleNames as $logoFile) {
                $fullPath = public_path('imagen/conjunto/' . $logoFile);
                if (file_exists($fullPath)) {
                    $logoPath = asset('imagen/conjunto/' . $logoFile);
                    break;
                }
            }
        }

        return view('pages.solicitud_oc.print', compact('solicitud', 'conjunto', 'logoPath'));
    }
}
