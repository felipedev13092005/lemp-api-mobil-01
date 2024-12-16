<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsersModel extends Model
{
    // Definir el nombre de la tabla
    protected $table = 'usuario';

    // Definir la clave primaria
    protected $primaryKey = 'codigo_usuario';

    // Si la clave primaria no es un entero auto incremental
    public $incrementing = false;

    // Deshabilitar el manejo automático de timestamps
    public $timestamps = false;

    // Definir los campos que pueden ser asignados masivamente (mass assignment)
    protected $fillable = [
        'codigo_usuario',
        'clave',
        'estado',
        'tipo_usuario',
        'id_empresa',
        'nit_FK',
        'mensaje',
        'hora_ingreso',
        'hora_salida',
        'usuario_FK',
        'imagen',
        'control_precio',
    ];

    // Definir la conexión si usas una diferente a la predeterminada
    // protected $connection = 'mysql_connection_name';
    public function login($code, $password)
    {
        // Usamos el constructor de consultas de Laravel (Query Builder) para realizar la consulta
        $query = DB::table('usuario as u')
            ->join('empresa as e', 'u.id_empresa', '=', 'e.id_empresa')
            ->join('tercero as t', 'u.nit_FK', '=', 't.nit')
            ->where('u.codigo_usuario', $code)
            ->where('u.clave', $password)
            ->where('u.estado', 1)
            ->select(
                'u.codigo_usuario',
                DB::raw("CONCAT(t.primer_nombre, ' ', t.primer_apellido, ' ', t.razon_social) as nombre"),
                't.razon_social',
                'e.bd',
                'u.tipo_usuario',
                't.dv',
                't.tipo',
                'e.fecha_fin',
                'u.nit_FK as nit_usuario',
                'u.id_empresa'
            )
            ->first(); // Devuelve el primer resultado o null si no se encuentra

        return $query;
    }
}
