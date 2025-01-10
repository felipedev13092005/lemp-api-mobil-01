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
// 	vrUnitario: string // inv_prod2024 se obtiene de dividiendo san(men) entre can(mes)
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
// export interface ProductWithAmount extends Product {
// 	amount: number
// }

class ProductosController extends Controller
{
    public function index(Request $request)
    {
        $precio_fk = $request->query('precio_fk');
        try {
            $year = date('Y');
            $month = date('m');
            // convertir el mes a un número de 1 a 12
            $month = (int)$month;
            // si se recibe un precio_fk pero no es un número
            if ($precio_fk && !is_numeric($precio_fk)) {
                return response()->json([
                    'message' => 'El precio_fk debe ser un número',
                ], 400);
            }

            // si el precio_fk es menor a 1 o mayor a 6
            if (($precio_fk && !is_numeric($precio_fk)) && ($precio_fk < 1 || $precio_fk > 6)) {
                return response()->json([
                    'message' => 'El precio_fk debe ser un número entre 1 y 6',
                ], 400);
            }
            if ($precio_fk === '0') {
                return response()->json([
                    'message' => 'El precio_fk debe ser un número entre 1 y 6',
                ], 400);
            }
            if (!$precio_fk) {
                // si se manda el precio fk
                $sqlObtenerProducto = "SELECT codigo, nombre, descuento, iva, referencia, imagen, unidad_FK, marca_FK, linea_FK, id_invcatalogo, cod_barras, estado, vr_unitario, inv_prod" . $year . ".can" . $month . " as stock FROM inv_catalogo
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
                $unidades = DB::reconnect('dynamic_database')->select("SELECT codigo, descripcion FROM parametros_inv WHERE concepto='Medida'");
                $unidades = collect($unidades); // Ya es una colección

                $productosMap = $productos->map(function ($product) use ($unidades) {
                    $unidad = $product->unidad_FK ?? '';
                    if ($unidad) {
                        $unidad = $unidades->first(function ($u) use ($unidad) {
                            return $u->codigo === $unidad;
                        });
                        $unidad = $unidad ? $unidad->descripcion : '';
                    }
                    return [
                        'codigo' => $product->codigo,
                        'descripcion' => $product->nombre,
                        'descuento' => $product->descuento,
                        'iva' => $product->iva,
                        'referencia' => $product->referencia,
                        'imagen' => $product->imagen ? "https://lempap.net.co/" . $product->imagen : null,
                        'marca' => $product->marca_FK,
                        'linea' => $product->linea_FK,
                        'invCatalogo' => $product->id_invcatalogo,
                        'nombre' => $product->nombre,
                        'cod_barras' => $product->cod_barras,
                        'estado' => $product->estado,
                        'vr_unitario' => $product->vr_unitario,
                        'stock' => $product->stock,
                        'unidad' => $unidad,
                    ];
                });

                return response()->json([
                    // 'unidad' => $unidades,
                    'productos' => $productosMap,
                ]);
            } else {
                // si se manda el precio fk
                $sqlObtenerProducto = "
                                        SELECT 
                                        inv_catalogo.codigo, 
                                        inv_catalogo.nombre, 
                                        inv_catalogo.descuento, 
                                        inv_catalogo.iva, 
                                        inv_catalogo.referencia, 
                                        inv_catalogo.imagen, 
                                        inv_catalogo.unidad_FK, 
                                        inv_catalogo.marca_FK, 
                                        inv_catalogo.linea_FK, 
                                        inv_catalogo.id_invcatalogo, 
                                        inv_catalogo.cod_barras, 
                                        inv_catalogo.estado, 
                                        inv_prod" . $year . ".can" . $month . " AS stock, 
                                        inv_lprecios.vr_" . $precio_fk . " AS vr_unitario
                                    FROM 
                                        inv_catalogo 
                                    JOIN 
                                        inv_prod" . $year . " 
                                    ON 
                                        inv_catalogo.id_invcatalogo = inv_prod" . $year . ".id_catalogo 
                                    JOIN 
                                        inv_lprecios 
                                    ON 
                                        inv_catalogo.id_invcatalogo = inv_lprecios.id_catalogo;
                ";
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
                $unidades = DB::reconnect('dynamic_database')->select("SELECT codigo, descripcion FROM parametros_inv WHERE concepto='Medida'");
                $unidades = collect($unidades); // Ya es una colección

                $productosMap = $productos->map(function ($product) use ($unidades) {
                    $unidad = $product->unidad_FK ?? '';
                    if ($unidad) {
                        $unidad = $unidades->first(function ($u) use ($unidad) {
                            return $u->codigo === $unidad;
                        });
                        $unidad = $unidad ? $unidad->descripcion : '';
                    }
                    return [
                        'codigo' => $product->codigo,
                        'descripcion' => $product->nombre,
                        'descuento' => $product->descuento,
                        'iva' => $product->iva,
                        'referencia' => $product->referencia,
                        'imagen' => $product->imagen ? "https://lempap.net.co/" . $product->imagen : null,
                        'marca' => $product->marca_FK,
                        'linea' => $product->linea_FK,
                        'invCatalogo' => $product->id_invcatalogo,
                        'nombre' => $product->nombre,
                        'cod_barras' => $product->cod_barras,
                        'estado' => $product->estado,
                        'vr_unitario' => $product->vr_unitario,
                        'stock' => $product->stock,
                        'unidad' => $unidad,
                    ];
                });

                return response()->json([
                    // 'unidad' => $unidades,
                    'productos' => $productosMap,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error querying the database',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
