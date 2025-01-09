<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
// export type Product = {
// 	codigo: string // inv_catalogo
// 	descripcion: string // inv_catalogo
// 	descuento: string // inv_catalogo
// 	iva: string // inv_catalogo
// 	referencia: string // inv_catalogo
// 	stock: string // inv_prod2024 se obtiene del campo can(mes)
// 	imagen: string | null // inv_catalogo
// 	cuentaVenta: string // inv_parametros.cta_venta
// 	ivaventa: string // inv_parametros.iva2
// 	vrUnitario: string // inv_prod2024 se obtiene de dividiendo san(mes) entre can(mes)
// 	marca: string // inv_catalogo
// 	linea: string // inv_catalogo
// 	bodega: string // operacion.parametros_inv se filtra por id_empresa
// 	invCatalogo: string // inv_catalogo
// 	unidad: string // operacion.parametros_inv
// 	nombre: string // inv_catalogo
// 	naturaleza: string // en blanco
// 	ordenusada: string // en blanco
// 	naturalezaventa: string // cr
// 	cod_barras: string // inv_catalogo
// 	estado: string // inv_catalogo
// }

class ProductosController extends Controller
{
    public function index(Request $request)
    {
        try {
            $year = date('Y');
            $month = date('m');
            // convertir el mes a un número de 1 a 12
            $month = (int)$month;
            $jwt_payload = $request->attributes->get('jwt_payload');
            // cortar los dos primeros caracteres del db de jwt_payload
            $dbId = (int)substr($jwt_payload['db'], 2);
            $sqlObtenerProducto = "SELECT codigo, nombre, descuento, iva, referencia, imagen, marca_FK, linea_FK, id_invcatalogo, cod_barras, estado, inv_prod" . $year . ".can" . $month . " as stock, inv_prod" . $year . ".san" . $month . " as vrTotal FROM inv_catalogo
                                   JOIN inv_prod" . $year . " ON inv_catalogo.id_invcatalogo = inv_prod" . $year . ".id_catalogo;";
            $productos = Db::connection('dynamic_database')->select($sqlObtenerProducto);
            $productos = collect($productos);
            Config::set('database.connections.dynamic_database', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => "lempapco_operacion",
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $unidad = DB::reconnect('dynamic_database')->select("SELECT * FROM parametros_inv WHERE id_empresa = $dbId");
            $productosMap = $productos->map(function ($product) {
                return [
                    'codigo' => $product->codigo,
                    'descripcion' => $product->nombre,
                    'descuento' => $product->descuento,
                    'iva' => $product->iva,
                    'referencia' => $product->referencia,
                    'imagen' => $product->imagen,
                    'marca' => $product->marca_FK,
                    'linea' => $product->linea_FK,
                    'invCatalogo' => $product->id_invcatalogo,
                    'nombre' => $product->nombre,
                    'cod_barras' => $product->cod_barras,
                    'estado' => $product->estado,
                    'stock' => $product->stock,
                    'vrUnitario' => $product->stock > 0 ? $product->vrTotal / $product->stock : 0,
                ];
            });
            return response()->json([
                'unidad' => $unidad,
                'productos' => $productosMap,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error querying the database',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
