<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudOcAuditoria extends Model
{
    use HasFactory;

    protected $table = 'solicitud_oc_auditoria';

    protected $fillable = [
        'SOLICITUD_OC',
        'DEPARTAMENTO',
        'FECHA_SOLICITUD',
        'FECHA_REQUERIDA',
        'AUTORIZADA_POR',
        'FECHA_AUTORIZADA',
        'PRIORIDAD',
        'LINEAS_NO_ASIG',
        'ESTADO',
        'COMENTARIO',
        'FECHA_HORA',
        'USUARIO',
        'USUARIO_CANCELA',
        'FECHA_HORA_CANCELA',
        'RUBRO1',
        'RUBRO2',
        'RUBRO3',
        'RUBRO4',
        'RUBRO5',
        'RowPointer',
        'NoteExistsFlag',
        'RecordDate',
        'CreatedBy',
        'UpdatedBy',
        'CreateDate',
        'fecha_eliminacion',
        'usuario_eliminacion_id',
        'softland_user_eliminacion',
        'accion'
    ];

    protected $casts = [
        'FECHA_SOLICITUD' => 'datetime',
        'FECHA_REQUERIDA' => 'datetime',
        'FECHA_AUTORIZADA' => 'datetime',
        'FECHA_HORA' => 'datetime',
        'FECHA_HORA_CANCELA' => 'datetime',
        'RecordDate' => 'datetime',
        'CreateDate' => 'datetime',
        'fecha_eliminacion' => 'datetime',
    ];

    // Relaciones
    public function usuarioEliminacion()
    {
        return $this->belongsTo(User::class, 'usuario_eliminacion_id');
    }

    public function lineas()
    {
        return $this->hasMany(SolicitudOcLineaAuditoria::class, 'solicitud_oc_auditoria_id');
    }
}
