/**
 * @swagger
 * tags:
 *   name: Generico
 *   description: Tag generico.
 */
/**
* @swagger
*  path:
*   /v1/ast:
*     post:
*       summary: Procesa un Query SQL y devuelve la información de su estructura
*       tags: [Generico]
*       requestBody:
*         required: true
*         content:
*           application/json:
*             schema:
*               $ref: '#/components/schemas/astRequestSchema'
*       responses:
*         "200":
*           description: Información AST del SQL
*           content:
*             application/json:
*               schema:
*                 $ref: '#/components/schemas/astResponseOK'
*               example:
*                   status: 200
*                   message: OK
*                   description: SQL procesado con éxito
*                   data:
*                       method: get
*                       sql: SELECT promo.prepago, cliente.saldo_disponible, usuario.activo FROM `rad`.`usuario` INNER JOIN `rad`.`rad_cfg_promo` ON usuario.usupromo = rad_cfg_promo.rad_promo INNER JOIN `maestra`.`cliente` ON rad_cfg_promo.pid_cfg = cliente.id INNER JOIN `rad`.`promo` ON usuario.usupromo = promo.pnom WHERE usuario.id = ?
*                       dataSql:
*                           with: null
*                           type: select
*                           options: null
*                           distinct: null
*                           columns:
*                           - expr:
*                               type: column_ref
*                               table: promo
*                               column: prepago
*                               as: null
*                           - expr:
*                               type: column_ref
*                               table: cliente
*                               column: saldo_disponible
*                               as: null
*                           - expr:
*                               type: column_ref
*                               table: usuario
*                               column: activo
*                               as: null
*                           from:
*                           - db: rad
*                             table: usuario
*                             as: null
*                           - db: rad
*                             table: rad_cfg_promo
*                             as: null
*                             join: INNER JOIN
*                             'on':
*                               type: binary_expr
*                               operator: =
*                               left:
*                                   type: column_ref
*                                   table: usuario
*                                   column: usupromo
*                               right:
*                                   type: column_ref
*                                   table: rad_cfg_promo
*                                   column: rad_promo
*                           - db: maestra
*                             table: cliente
*                             as: null
*                             join: INNER JOIN
*                             'on':
*                               type: binary_expr
*                               operator: =
*                               left:
*                                   type: column_ref
*                                   table: rad_cfg_promo
*                                   column: pid_cfg
*                               right:
*                                   type: column_ref
*                                   table: cliente
*                                   column: id
*                           - db: rad
*                             table: promo
*                             as: null
*                             join: INNER JOIN
*                             'on':
*                               type: binary_expr
*                               operator: =
*                               left:
*                                   type: column_ref
*                                   table: usuario
*                                   column: usupromo
*                               right:
*                                   type: column_ref
*                                   table: promo
*                                   column: pnom
*                           where:
*                               type: binary_expr
*                               operator: =
*                               left:
*                                   type: column_ref
*                                   table: usuario
*                                   column: id
*                               right:
*                                   type: origin
*                                   value: '?'
*                           groupby: null
*                           having: null
*                           orderby: null
*                           limit: null
*                           for_update: null
*                       arrayColumns:
*                           - columnName: usuario.id
*                             column: id
*                             table: usuario
*                             operator: =
*                             parameter:
*                             - 'usuario.id:value'
*                       arrayTables:
*                           - db: rad
*                             table: usuario
*                             as: null
*                           - db: rad
*                             table: rad_cfg_promo
*                             as: null
*                             join: INNER JOIN
*                             'on':
*                               type: binary_expr
*                               operator: =
*                               left:
*                                   type: column_ref
*                                   table: usuario
*                                   column: usupromo
*                               right:
*                                   type: column_ref
*                                   table: rad_cfg_promo
*                                   column: rad_promo
*                           - db: maestra
*                             table: cliente
*                             as: null
*                             join: INNER JOIN
*                             'on':
*                               type: binary_expr
*                               operator: =
*                               left:
*                                   type: column_ref
*                                   table: rad_cfg_promo
*                                   column: pid_cfg
*                               right:
*                                   type: column_ref
*                                   table: cliente
*                                   column: id
*                           - db: rad
*                             table: promo
*                             as: null
*                             join: INNER JOIN
*                             'on':
*                               type: binary_expr
*                               operator: =
*                               left:
*                                   type: column_ref
*                                   table: usuario
*                                   column: usupromo
*                               right:
*                                   type: column_ref
*                                   table: promo
*                                   column: pnom
*                       arraySelect:
*                           - type: column_ref
*                             table: promo
*                             column: prepago
*                           - type: column_ref
*                             table: cliente
*                             column: saldo_disponible
*                           - type: column_ref
*                             table: usuario
*                             column: activo
*                       tableCreate: ''
*                       typeInsertTable: ''
*         "400":
*           description: Cuerpo o payload con errores
*           content:
*             application/json:
*               schema:
*                 $ref: '#/components/schemas/astResponseError'
*               example:
*                   status: 400
*                   message: "BAD_REQUEST"
*                   description: Payload no válido
*         "500":
*           description: Error crítico
*           content:
*             application/json:
*               schema:
*                 $ref: '#/components/schemas/astResponseError'
*               example:
*                   status: 500
*                   message: "INTERNAL_SERVER_ERROR"
*                   description: 'Error crítico' 
*/