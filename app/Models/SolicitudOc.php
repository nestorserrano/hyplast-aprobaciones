<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudOc extends SoftlandModel
{
    protected $table = 'C01.SOLICITUD_OC';
    protected $primaryKey = 'SOLICITUD_OC';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'SOLICITUD_OC',
        'FECHA_SOLICITUD',
        'USUARIO',
        'DEPARTAMENTO',
        'CENTRO_COSTO',
        'MONEDA',
        'PRIORIDAD',
        'ESTADO',
        'NOTAS',
        'AUTORIZADA_POR',
        'FECHA_AUTORIZADA',
    ];

    /**
     * Relación con las líneas de la solicitud
     */
    public function lineas()
    {
        return $this->hasMany(SolicitudOcLinea::class, 'SOLICITUD_OC', 'SOLICITUD_OC');
    }

    /**
     * Relación con el usuario solicitante de Softland
     */
    public function usuarioSolicitante()
    {
        return $this->belongsTo(Usuario::class, 'USUARIO', 'USUARIO');
    }

    /**
     * Relación con el usuario que autorizó
     */
    public function usuarioAutoriza()
    {
        return $this->belongsTo(Usuario::class, 'AUTORIZADA_POR', 'USUARIO');
    }

    /**
     * Relación con el departamento
     */
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'DEPARTAMENTO', 'DEPARTAMENTO');
    }

    /**
     * Relación con el centro de costo
     */
    public function centroCosto()
    {
        return $this->belongsTo(CentroCosto::class, 'CENTRO_COSTO', 'CENTRO_COSTO');
    }

    /**
     * Obtener el nombre de la prioridad
     */
    public function getPrioridadNombreAttribute()
    {
        $prioridades = [
            'Z' => 'Normal',
            'M' => 'Urgente',
            'A' => 'Emergencia',
        ];

        return $prioridades[$this->PRIORIDAD] ?? 'N/A';
    }

    /**
     * Obtener la clase de badge según prioridad
     */
    public function getPrioridadBadgeAttribute()
    {
        $badges = [
            'Z' => 'badge-secondary',
            'M' => 'badge-warning',
            'A' => 'badge-danger',
        ];

        return $badges[$this->PRIORIDAD] ?? 'badge-secondary';
    }

    /**
     * Verificar si está autorizada
     */
    public function getEstaAutorizadaAttribute()
    {
        return !empty($this->AUTORIZADA_POR);
    }
}
