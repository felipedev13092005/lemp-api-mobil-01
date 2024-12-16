<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientesModel extends Model
{
    // Definir el nombre de la tabla
    protected $table = 'cliente';

    // Definir la clave primaria
    protected $primaryKey = 'codigo';

    // Si la clave primaria no es auto incremental
    public $incrementing = false;

    // Deshabilitar el manejo automático de timestamps
    public $timestamps = false;

    // Definir los campos que pueden ser asignados masivamente (mass assignment)
    protected $fillable = [
        'codigo',
        'nit_FK',
        'nombre_cliente',
        'tipocliente',
        'gerente',
        'direccion',
        'codigo_mcipio_FK',
        'telefono',
        'mobil_01',
        'mobil_02',
        'email_01',
        'email_02',
        'email_03',
        'cupo_credito',
        'plazo',
        'vendedor_FK',
        'sucursal_FK',
        'listaPrecios_FK',
        'zona_FK',
        'canal_FK',
        'ruta_FK',
        'descuento_FK',
        'ubicacion',
        'contacto',
        'codPostal_FK',
        'transportador_FK',
        'notas',
        'reteica_FK',
        'retefuente_FK',
        'reteiva_FK',
        'id_empresa',
        'estado',
        'codigo_usuario_FK',
        'excluidoiva'
    ];

    // Deshabilitar la gestión automática de timestamps de Laravel
    protected $casts = [
        'fecha_creacion' => 'datetime', // Si quieres convertir `fecha_creacion` a un tipo DateTime automáticamente
    ];
}
