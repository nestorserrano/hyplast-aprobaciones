<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Requisition extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'requisitions';
    protected $guarded = [
        'id',
    ];
    protected $fillable = [
        'id',
        'conjunto_id',
        'codebar',
        'client_code',
        'product_id',
        'machine_id',
        'requested',
        'total_weight',
        'dosage_virgen',
        'dosage_recycled',
        'manufactured',
        'manufactured_weight',
        'date_limit',
        'date_fabrication_init',
        'date_fabrication_finish',
        'status_id',
        'user_id',
        'approved_by',
        'approved_at',
        'extruder_id',
        'id_article'
    ];

    /**
     * Boot del modelo - Auto-asignar conjunto_id y filtrar por empresa actual
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-asignar conjunto_id al crear
        static::creating(function ($requisition) {
            if (!$requisition->conjunto_id) {
                $requisition->conjunto_id = \App\Helpers\SchemaHelper::getSchema();
            }
        });

        // Filtrar automáticamente por conjunto del usuario (Global Scope)
        static::addGlobalScope('conjunto', function ($builder) {
            $conjunto = \App\Helpers\SchemaHelper::getSchema();
            if ($conjunto) {
                $builder->where('conjunto_id', $conjunto);
            }
        });
    }

    protected $casts = [
        'id'                        => 'integer',
        'codebar'                   => 'string',
        'client_code'               => 'string',
        'product_id'                => 'string', // MIGRATED: ahora apunta a products.code
        'machine_id'                => 'integer',
        'requested'                 => 'integer',
        'total_weight'              => 'float',
        'manufactured'              => 'integer',
        'manufactured_weight'       => 'float',
        'dosage_virgen'             => 'integer',
        'dosage_recycled'           => 'integer',
        'date_limit'                => 'datetime',
        'date_fabrication_init'     => 'datetime',
        'date_fabrication_finish'   => 'datetime',
        'status_id'                 => 'integer',
        'user_id'                   => 'integer',
        'extruder_id'               => 'integer',
    ];

    /**
     * Get cliente from Softland
     */
    public function getClienteAttribute()
    {
        if (!$this->client_code) {
            return null;
        }

        $schema = \App\Helpers\SchemaHelper::getSchema();

        return \DB::connection('softland')
            ->table("{$schema}.CLIENTE")
            ->where('CLIENTE', $this->client_code)
            ->select('CLIENTE', 'NOMBRE', 'CONTACTO', 'TELEFONO1', 'E_MAIL')
            ->first();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class,'products_requisitions')
            ->using(\Illuminate\Database\Eloquent\Relations\Pivot::class)
            ->withPivot('product_id')
            ->withTimestamps();
    }

    /**
     * Obtener los supplies/insumos de esta requisición
     * NOTA: No se puede usar belongsToMany porque requisitions_supplies.supplie_id (varchar)
     * referencia a ARTICULO de Softland, no a supplies.id (bigint)
     */
    public function supplies()
    {
        $schema = \App\Helpers\SchemaHelper::getSchema();
        $softlandDB = config('database.connections.softland.database');

        return DB::table('requisitions_supplies as rs')
            ->join("{$softlandDB}.{$schema}.ARTICULO as a", 'rs.supplie_id', '=', 'a.ARTICULO')
            ->where('rs.requisition_id', $this->id)
            ->select(
                'rs.id',
                'rs.supplie_id as ARTICULO',
                'a.DESCRIPCION',
                'a.UNIDAD_ALMACEN',
                'rs.quantity',
                'rs.created_at',
                'rs.updated_at'
            )
            ->get();
    }

    public function product()
    {
        // MIGRATED: ahora busca por ARTICULO (PK en Softland)
        return $this->hasOne(Product::class, 'ARTICULO', 'product_id');
    }

    public function extruders()
    {
        return $this->hasMany(Extruder::class, 'id', 'extruder_id');
    }

    public function machine()
    {
        return $this->hasOne(Machine::class, 'U_CODIGO', 'machine_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function approvedBy()
    {
        return $this->hasOne(User::class, 'id', 'approved_by');
    }

    public function status()
    {
        return $this->hasOne(Status::class, 'U_CODIGO', 'status_id');
    }

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
}
