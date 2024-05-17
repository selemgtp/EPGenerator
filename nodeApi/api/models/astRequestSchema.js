/**
* @swagger
*   components:
*     schemas:
*      astRequestSchema:
*         type: object
*         required:
*           - sql
*           - query_type
*           - db
*         properties:
*           sql:
*             type: string
*             description: Query SQL a procesar.
*           query_type:
*             type: string
*             description: Tipo de consulta
*             enum: [UNIQ, MULTIPLE]
*           db:
*             type: string
*             description: nombres de las bases de datos separados por comas cuando query_type es MULTIPLE. Cuando query_type es UNIQ envíe este campo vacío
*         example:
*            sql: SELECT promo.prepago, cliente.saldo_disponible, usuario.activo FROM `rad`.`usuario` INNER JOIN `rad`.`rad_cfg_promo` ON usuario.usupromo = rad_cfg_promo.rad_promo INNER JOIN `maestra`.`cliente` ON rad_cfg_promo.pid_cfg = cliente.id INNER JOIN `rad`.`promo` ON usuario.usupromo = promo.pnom WHERE usuario.id = ?
*            query_type: MULTIPLE
*            db: rad,maestra,promo,cliente,log,cfg,chat 
*/
