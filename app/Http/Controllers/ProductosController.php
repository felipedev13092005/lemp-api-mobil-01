<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductosController extends Controller
{
    public function index(Request $request)
    {
        $jwt_payload = $request->attributes->get('jwt_payload');
        $querys = $request->query();
        $precio = $querys['precio'] ?? 0;
        $codproducto = $querys['codproducto'] ?? 1;
        $anio = date('Y');
        $id_empresa = $jwt_payload['id_empresa'];
        $mes = date('m');
        try {
            // validar que si precio existe tiene que ser entero
            if (!is_numeric($precio)) {
                return response()->json([
                    'message' => 'The price must be a number',
                ], 400);
            }
            if ($codproducto == '') {
                $sql3 = "";
                $codproducto = 1;
            } else {
                $sql3 = "inv_catalogo.codigo='" . $codproducto . "' AND";
            }

            $sql4 = " inv_catalogo.vr_unitario ";
            if ((int)$precio > 0 && (int)$precio <= 6) {
                $sql4 = " IF(inv_lprecios.vr_" . $precio . " is null, inv_catalogo.vr_unitario, inv_lprecios.vr_" . $precio . ") ";
            }
            $sql = "SELECT " .
                "IF(LEFT(inv_catalogo.imagen, 3) = '../', inv_catalogo.imagen, '') AS imagenproducto, " .
                "inv_catalogo.cod_parametro, " .
                "inv_catalogo.id_invcatalogo, " .
                "inv_catalogo.depende, " .
                "inv_catalogo.codigo, " .
                "inv_catalogo.nombre, " .
                "inv_catalogo.cod_barras, " .
                "inv_catalogo.nivel, " .
                "inv_catalogo.referencia, " .
                "inv_catalogo.imagen, " .
                "inv_catalogo.gravado, " .
                "inv_catalogo.iva, " .
                "$sql4 AS vr_unitario, " .
                "inv_catalogo.unidad_FK AS unidad, " .
                "1 AS contador, " .
                "IF(inv_prod$anio.codigos_FK IS NULL, 0, can$mes) AS existencia, " .
                "IF(ordendia.cantidad IS NULL, 0, ordendia.cantidad) AS ordenusada, " .
                "inv_catalogo.marca_FK, " .
                "inv_catalogo.linea_FK, " .
                "IF(pamarca.descripcion IS NULL, '', pamarca.descripcion) AS nombre_marca, " .
                "IF(palinea.descripcion IS NULL, '', palinea.descripcion) AS nombre_linea, " .
                "inv_catalogo.talla AS nombre_talla, " .
                "inv_catalogo.color AS nombre_color, " .
                "inv_catalogo.descuento, " .
                "IF(pabodega.id IS NULL, pabodega2.id, pabodega.id) AS bodega_FK, " .
                "IF(pabodega.descripcion IS NULL, CONCAT(pabodega2.descripcion, ' ', pabodega2.codigo), CONCAT(pabodega.descripcion, ' ', pabodega.codigo)) AS nombre_bodega, " .
                "inv_catalogo.cod_parametro, " .
                "cta_inventario, " .
                "cta_costo, " .
                "cta_venta, " .
                "cta_devoluciones AS cta_devventa, " .
                "cta_devcompra, " .
                "iva2 AS ivaventa, " .
                "IF(naturaleza2 = 'DB', 'D', 'C') AS naturalezaventa, " .
                "cuentaventa.naturaleza, " .
                "cuentainven.naturaleza AS naturalezainv2, " .
                "IF(naturaleza1 = 'DB', 'D', 'C') AS naturalezainven, " .
                "iva1 AS ivainven, " .
                "iva3 AS ivadevventa, " .
                "IF(naturaleza3 = 'DB', 'D', 'C') AS naturalezadevventa, " .
                "cuentadevventa.naturaleza AS naturalezadevven2, " .
                "iva4 AS ivadevcompra, " .
                "IF(naturaleza4 = 'DB', 'D', 'C') AS naturalezadevcompra, " .
                "cuentadevcompra.naturaleza AS naturalezadevcomp2, " .
                "inv_lprecios.vr_1, " .
                "inv_lprecios.vr_2, " .
                "inv_lprecios.vr_3, " .
                "inv_lprecios.vr_4, " .
                "inv_lprecios.vr_5, " .
                "inv_lprecios.vr_6, " .
                "IF(inv_lprecios.id_lista IS NULL, 0, inv_lprecios.id_lista) AS id_lista " .
                "FROM " .
                "inv_catalogo " .
                "LEFT JOIN inv_lprecios " .
                "ON inv_lprecios.id_catalogo = inv_catalogo.id_invcatalogo " .
                "LEFT JOIN inv_prod$anio " .
                "ON inv_prod$anio.id_catalogo = inv_catalogo.id_invcatalogo " .
                "AND inv_prod$anio.clase = 3 " .
                "LEFT JOIN lempapco_operacion.parametros_inv AS pamarca " .
                "ON pamarca.id = inv_catalogo.marca_FK " .
                "AND pamarca.id_empresa = '$id_empresa' " .
                "AND pamarca.concepto = 'MARCA' " .
                "LEFT JOIN lempapco_operacion.parametros_inv AS palinea " .
                "ON palinea.id = inv_catalogo.linea_FK " .
                "AND palinea.id_empresa = '$id_empresa' " .
                "AND palinea.concepto = 'LINEA' " .
                "LEFT JOIN lempapco_operacion.parametros_inv AS pabodega " .
                "ON pabodega.id = inv_prod$anio.bodega_FK " .
                "AND pabodega.id_empresa = '$id_empresa' " .
                "AND pabodega.concepto = 'BODEGA' " .
                "LEFT JOIN lempapco_operacion.parametros_inv AS pabodega2 " .
                "ON pabodega2.id = inv_catalogo.bodega_FK " .
                "AND pabodega2.id_empresa = '$id_empresa' " .
                "AND pabodega2.concepto = 'BODEGA' " .
                "LEFT JOIN lempapco_operacion.inv_cuentas " .
                "ON inv_cuentas.id_parametro = inv_catalogo.cod_parametro " .
                "LEFT JOIN (SELECT cuenta.naturaleza, cuenta.cuenta FROM cuenta WHERE nivel = 5) AS cuentaventa " .
                "ON cuentaventa.cuenta = inv_cuentas.cta_venta " .
                "LEFT JOIN (SELECT cuenta.naturaleza, cuenta.cuenta FROM cuenta WHERE nivel = 5) AS cuentainven " .
                "ON cuentainven.cuenta = inv_cuentas.cta_inventario " .
                "LEFT JOIN (SELECT cuenta.naturaleza, cuenta.cuenta FROM cuenta WHERE nivel = 5) AS cuentadevventa " .
                "ON cuentadevventa.cuenta = inv_cuentas.cta_inventario " .
                "LEFT JOIN (SELECT cuenta.naturaleza, cuenta.cuenta FROM cuenta WHERE nivel = 5) AS cuentadevcompra " .
                "ON cuentadevcompra.cuenta = inv_cuentas.cta_inventario " .
                "LEFT JOIN ( " .
                "SELECT inv_ordenes$anio.id_catalogo, SUM(cant_cr - cant_db) AS cantidad " .
                "FROM inv_ordenes$anio " .
                "INNER JOIN tipo " .
                "ON tipo.tipo = inv_ordenes$anio.tipo " .
                "WHERE inv_ordenes$anio.documento = '' " .
                "AND tipo.clase = 'ORDENES VENTA' " .
                "AND nota1 != 'ANULADO' " .
                "GROUP BY inv_ordenes$anio.id_catalogo " .
                ") AS ordendia " .
                "ON ordendia.id_catalogo = inv_catalogo.id_invcatalogo " .
                "WHERE $sql3 " .
                "AND inv_catalogo.id_empresa = '$id_empresa' " .
                "GROUP BY inv_catalogo.id_invcatalogo, inv_prod$anio.bodega_FK " .
                "HAVING bodega_FK != 0";

            $productos = DB::connection('dynamic_database')->select($sql);
            return response()->json([
                'message' => 'Query executed successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error querying the database',
                'error' => $th->getMessage(),
            ], 500);
        }
        // try {

        //     // // $productos = DB::connection('dynamic_database')->select($sql);
        //     return response()->json([
        //         'message' => 'Query executed successfully',
        //     ]);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'message' => 'Error querying the database',
        //         'error' => $e->getMessage(),
        //     ], 500);
        // }
    }
}
