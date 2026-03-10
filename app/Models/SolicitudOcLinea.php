<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudOcLinea extends SoftlandModel
{
    protected $table = 'C01.SOLICITUD_OC_LINEA';
    protected $primaryKey = ['SOLICITUD_OC', 'LINEA'];
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'SOLICITUD_OC',
        'SOLICITUD_OC_LINEA',
        'ARTICULO',
        'DESCRIPCION',
        'CANTIDAD',
        'PRECIO_UNITARIO',
        'TOTAL_LINEA',
        'CUENTA_CONTABLE',
        'CENTRO_COSTO',
        'NOTAS',
        'ESTADO',
        'SALDO',
        'COMENTARIO',
        'FECHA_REQUERIDA',
        'UNIDAD_DISTRIBUCIO',
        'USUARIO_CANCELA',
        'FECHA_HORA_CANCELA',
        'E_MAIL',
        'FASE',
        'PROYECTO',
        'ORDEN_CAMBIO',
    ];

    /**
     * Relación con la solicitud padre
     */
    public function solicitud()
    {
        return $this->belongsTo(SolicitudOc::class, 'SOLICITUD_OC', 'SOLICITUD_OC');
    }

    /**
     * Relación con el artículo
     */
    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'ARTICULO', 'ARTICULO');
    }

    /**
     * Relación con la cuenta contable
     */
    public function cuentaContable()
    {
        return $this->belongsTo(CuentaContable::class, 'CUENTA_CONTABLE', 'CUENTA_CONTABLE');
    }

    /**
     * Relación con el centro de costo
     */
    public function centroCosto()
    {
        return $this->belongsTo(CentroCosto::class, 'CENTRO_COSTO', 'CENTRO_COSTO');
    }
}
