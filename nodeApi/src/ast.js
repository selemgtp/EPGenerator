var {Utils} = require('./utils')

const DML = {
    select:{
        method: "get"
    },
    update:{
        method: "put"
    },
    insert:{
        method: "post"
    },
    delete:{
        method:"delete"
    }
}

const astSchema = {
    type: 'object',
    properties: {
        sql: {
            type: 'string',
            required: true
        },
        query_type:{
            type: 'string',
            required: true,
            enum: ['UNIQ', 'MULTIPLE']
        },
        db:{
            type: 'string',
            required: true
        }

    }
}

const responseBody = (res, status, message, description, data = null) =>{
    res.setHeader('Content-Type', 'application/json');

    var jsonObject = {
        "status": status,
        "message": message,
        "description": description
    }
    if (data != null){
        jsonObject.data = data
    }
    res.status(status).send(JSON.stringify(jsonObject));
}

const ast = (request, response) =>{
   
    const requestBody = request.body;
    arrayDatabases = requestBody.db.replace(/ ,/g, ',').split(",")
    var stringSql = requestBody.sql.replace(/;/g, '');
    stringSql = stringSql.replace(/ ,/g, ',');
    stringSql = stringSql.replace(/;/g, '');
    stringSql = stringSql.replace(/\n|\r/g, " ");
    var stringSqlCopy = stringSql;
  
    var tableCreate = '';
    var typeInsertTable = ''
    var partsStringSql = stringSql.split(" ");

    var partsStringSql = partsStringSql.filter(function (el) {
        return el != " ";
      });

      if (partsStringSql[0].toLowerCase() == "create"){
        typeInsertTable = "CREATE"
        if (partsStringSql[1].toLowerCase() != "table"){
            responseBody(response, 400,"BAD_REQUEST", "Se detecto una consulta de creación de tabla con error de sintaxis.")
            return false;
        }

        if (queryType == "UNIQ"){
            if (partsStringSql[2].indexOf(".") > -1){
                responseBody(response, 400,"BAD_REQUEST","Se detecto un punto que se interpreta como notacion basededatso.tabla. El tipo de consulta es de una única base de datos. Revisa y vuelve a intentarlo.")
                return false;
            }else{
                tableCreate = partsStringSql[2];
            }
        }else{
            if (partsStringSql[2].indexOf(".") == -1){
                responseBody(response, 400,"BAD_REQUEST","Falta la referencia a la base de datos en el create.")
                return false;
            }else{
                tableCreate = partsStringSql[2];
                var dbTable = tableCreate.split(".")[0]
                if (arrayDatabases.includes(dbTable) == false){
                    responseBody(response, 400,"BAD_REQUEST","La base de datos "+dbTable +" NO existe dentro de las bases de datos permitidas: "+arrayDatabases.join(","))
                    i = arrayTables.length;
                    return false
                }
            }
        }

        if (partsStringSql[3].toLowerCase() == "as"){
            stringSql = '';
            for (var i = 4; i < partsStringSql.length; i++){
                stringSql += partsStringSql[i];
                if (i < partsStringSql.length - 1){
                    stringSql += ' ';
                }
            }
            

        }else{
            responseBody(response, 400,"BAD_REQUEST","Falta el AS después del nombre de tabla.")
            return false;
        }

        console.log(stringSql)

    }else if (partsStringSql[0].toLowerCase() == "select"){

        //buscar el into 
        var searchInto = partsStringSql.filter(function(element){
            return element.toLowerCase() == "into"
        })
        if (searchInto.length > 0){
            typeInsertTable = "INTO"
            var isInto = (element) => element.toLowerCase() == 'into';

            var indexInto = partsStringSql.findIndex(isInto);

            if (queryType == "UNIQ"){
                if (partsStringSql[indexInto + 1].indexOf(".") > -1){
                    responseBody(response, 400,"BAD_REQUEST","Se detecto un punto que se interpreta como notacion basededatso.tabla. El tipo de consulta es de una única base de datos. Revisa y vuelve a intentarlo.")
                    return false;
                }else{
                    tableCreate = partsStringSql[indexInto + 1];
                }
            }else{
                if (partsStringSql[indexInto + 1].indexOf(".") == -1){
                    responseBody(response, 400,"BAD_REQUEST","Falta la referencia a la base de datos en el create.")
                    return false;
                }else{
                    tableCreate = partsStringSql[indexInto + 1];
                    var dbTable = tableCreate.split(".")[0]
                    if (arrayDatabases.includes(dbTable) == false){
                        bootbox.alert("La base de datos "+dbTable +" NO existe dentro de las bases de datos permitidas: "+arrayDatabases.join(","))
                        i = arrayTables.length;
                        return false
                    }
                }
            }
            stringSql = '';
            for (var i = 0; i < partsStringSql.length; i++){
                if (partsStringSql[i].toLowerCase() != "into" && i != (indexInto + 1)){
                    stringSql += partsStringSql[i];
                    if (i < partsStringSql.length - 1){
                        stringSql += ' ';
                    }
                }
            }

        }

    }else if (partsStringSql[0].toLowerCase() == "drop"){
        if (partsStringSql[1].toLowerCase() != "table"){
            responseBody(response, 400,"BAD_REQUEST","Error de sintaxis cerca de drop");
            return false;
        }else if (partsStringSql[2] != '?'){
            responseBody(response, 400,"BAD_REQUEST","La consulta tipo DROP debe ir con el nombre de la tabla como parámetro ?");
            return false;
        }else{

            responseBody(response, 200,"OK","SQL procesado con éxito", {
                method: 'post',
                sqlUser: stringSql,
            })
            return false;
        }
    }

    const { Parser } = require('node-sql-parser');
    const parser = new Parser();
    let ast = null;
    let method = '';
    var arraySelect = Array();
    var arrayColumns = Array();
    var arrayTables = Array();
    let queryType = requestBody.query_type;

    try{
        ast = parser.astify(requestBody.sql); 

        if (ast.type == null || ast.type == "" || typeof ast.type == "undefined"){
            responseBody(response, 400,"BAD_REQUEST", "SQL desconocido");
        }else{

            method = DML[ast.type].method;
            switch (method){

                case  "post":
                if (ast.columns == null){
                    responseBody(response, 400,"BAD_REQUEST","Debes nombrar las columnas a afectar para definir el JSON de Request.");
                    return false;
                }

                if (typeof ast.values[0] == "undefined"){

                    for (var i = 0; i < ast.columns.length; i++){
                        arrayColumns.push({
                            columnName: ast.columns[i],
                            column: ast.columns[i],
                            operator: "",
                            table:ast.table[0].table,
                            parameter: ast.columns[i]+":value"
                        })
                    }


                    var columsSelect = Utils.getOperator(ast);
                   
                    for (var i = 0; i < columsSelect.length; i++){
                        var columnName = (columsSelect[i].left.table == null) ? columsSelect[i].left.column : columsSelect[i].left.table+"."+columsSelect[i].left.column; 
                        var arrayParameters = Array();

                        switch(columsSelect[i].operator){
                            case "BETWEEN":
                                if (columsSelect[i].right.value[0].value == "?"){
                                    arrayParameters.push(columnName+".left:value")
                                }
                                if (columsSelect[i].right.value[1].value == "?"){
                                    arrayParameters.push(columnName+".right:value")
                                }
                            break;

                            case "LIKE":
                                arrayParameters.push(columnName+".like_expr:value")
                            break; 
                            case "NOT LIKE":
                                arrayParameters.push(columnName+".not_like_expr:value")
                            break;

                            case "IN":
                                arrayParameters.push("["+columnName+".in:value]")
                            break;

                            default:
                                arrayParameters.push(columnName+":value")
                        }
                        
                        
                        arrayColumns.push({
                            column: columsSelect[i].left.column,
                            columnName: columnName,
                            table: columsSelect[i].left.table,
                            operator: columsSelect[i].operator,
                            parameter: arrayParameters
                        })
                    }

                    if (ast.columns.length != ast.values.columns.length){
                        responseBody(response, 400,"BAD_REQUEST","El número de parámetros no concuerda con el número de columnas.");
                        return false;
                    }

                }else{

                    if (ast.values.length > 1){
                        responseBody(response, 400,"BAD_REQUEST","No se permite multiple inserción.");
                        return false;
                    }
                    for (var i = 0; i < ast.values.length; i++){
                        if (ast.values[i].value.length != ast.columns.length){
                            responseBody(response, 400,"BAD_REQUEST","El número de parámetros no concuerda con el número de columnas.");
                            return false;
                        }
                    }

                
                    var uniqs = ast.columns.filter(function(item, index, array) {
                        return array.indexOf(item) === index;
                    })
                    if (uniqs.length != ast.columns.length){
                        responseBody(response, 400,"BAD_REQUEST","Se encontraron columnas repetidas. Por favor revisa.");
                        return false;
                    }

                    for (var i = 0; i < ast.columns.length; i++){
                        arrayColumns.push({
                            columnName: ast.columns[i],
                            column: ast.columns[i],
                            operator: "=",
                            table:ast.table[0].table,
                            parameter: [ast.columns[i]+":value"]
                        })
                    }

                }

                break;

                case "delete":
                case "get":

                    arraySelect = Utils.getSelect(ast.columns);
                    //console.log(arraySelect)
                    var columsSelect = Utils.getOperator(ast);
                    console.log(columsSelect)
                    for (var i = 0; i < columsSelect.length; i++){
                        var columnName = (columsSelect[i].left.table == null) ? columsSelect[i].left.column : columsSelect[i].left.table+"."+columsSelect[i].left.column; 
                        var arrayParameters = Array();

                        switch(columsSelect[i].operator){
                            case "BETWEEN":
                                if (columsSelect[i].right.value[0].value == "?"){
                                    arrayParameters.push(columnName+".left:value")
                                }
                                if (columsSelect[i].right.value[1].value == "?"){
                                    arrayParameters.push(columnName+".right:value")
                                }
                            break;

                            case "LIKE":
                                arrayParameters.push(columnName+".like_expr:value")
                                break;
                            case "NOT LIKE":
                              
                                arrayParameters.push(columnName+".not_like_expr:value")
                                break;
                            case "IN":
                                arrayParameters.push("["+columnName+".in:value]")
                            break;

                            default:
                                arrayParameters.push(columnName+":value")
                        }
                        
                        arrayColumns.push({
                            columnName: columnName,
                            column:columsSelect[i].left.column,
                            table: columsSelect[i].left.table,
                            operator: columsSelect[i].operator,
                            parameter: arrayParameters
                        })
                    }


                break;


                case "put":

                    for (var i = 0; i < ast.set.length; i++){

                        if (ast.set[i].value.value && ast.set[i].value.value == "?"){
                            var parameter = (ast.set[i].table == null) ? [ast.set[i].column+":value"] : [ast.set[i].table+"."+ast.set[i].column+":value"];
                            var columnName = (ast.set[i].table == null) ? ast.set[i].column: ast.set[i].table+"."+ast.set[i].column;
                            arrayColumns.push({
                                columnName: columnName,
                                column: ast.set[i].column,
                                table: ast.set[i].table,
                                operator:"=",
                                parameter: parameter
                            })
                        }
                    }

                    var columsSelect = Utils.getOperator(ast);
                    //console.log(columsSelect)
                    for (var i = 0; i < columsSelect.length; i++){
                        var columnName = (columsSelect[i].left.table == null) ? columsSelect[i].left.column : columsSelect[i].left.table+"."+columsSelect[i].left.column; 
                        var arrayParameters = Array();

                        switch(columsSelect[i].operator){
                            case "BETWEEN":
                                if (columsSelect[i].right.value[0].value == "?"){
                                    arrayParameters.push(columnName+".left:value")
                                }
                                if (columsSelect[i].right.value[1].value == "?"){
                                    arrayParameters.push(columnName+".right:value")
                                }
                            break;

                            case "LIKE":
                                arrayParameters.push(columnName+".like_expr:value")
                            break;
                            case "NOT LIKE":
                                arrayParameters.push(columnName+".not_like_expr:value")
                            break;

                            case "IN":
                                arrayParameters.push("["+columnName+".in:value]")
                            break;

                            default:
                                arrayParameters.push(columnName+":value")
                        }
                        
                        arrayColumns.push({
                            column: columsSelect[i].left.column,
                            columnName: columnName,
                            table: columsSelect[i].left.table,
                            operator: columsSelect[i].operator,
                            parameter: arrayParameters
                        })
                    }
                    
                break;


            }

            switch(method){
                case "post":
                case "put":

                for (var i = 0; i < ast.table.length; i++){
                    arrayTables.push(ast.table[i]);
                }

                arrayTablesTemp = Utils.getOperatorTables(ast);

                for (var i = 0; i < arrayTablesTemp.length;i++){
                    arrayTables.push(arrayTablesTemp[i]);
                }

                break;
                case "delete":

                case "get":
                    arrayTables = Utils.getOperatorTables(ast);
                break;
            }

        }

        if (queryType == "UNIQ"){
            for (var i = 0; i < arrayTables.length; i++){
                if (arrayTables[i].db != null){
                   
                    responseBody(response, 400,"BAD_REQUEST","Se detectó referencia a una base de datos "+arrayTables[i].db +". Recuerda que al seleccionar la opción de única base de datos no se acepta referencia como 'basededatos.tabla'")
                    i = arrayTables.length;
                    send = false;
                }
            }
        }else{
            for (var i = 0; i < arrayTables.length; i++){
                if (arrayTables[i].db == null){
                   
                    responseBody(response, 400,"BAD_REQUEST","Se detectó que no existe referencia a las bases de datos en la tabla "+arrayTables[i].table +". Recuerda que al seleccionar la opción de múltiple base de datos es obligatorio que las tablas tengan la referencia.")
                    i = arrayTables.length;
                    send = false;
                }else{
                    if (arrayDatabases.includes(arrayTables[i].db) == false){
                        responseBody(response, 400,"BAD_REQUEST","La base de datos "+arrayTables[i].db +" NO existe dentro de las bases de datos permitidas: "+arrayDatabases.join(","))
                        i = arrayTables.length;
                        send = false;
                    }
                }
            }
        }

        if (arrayColumns.length == 0){
            responseBody(response, 400,"BAD_REQUEST","No se detecto una sentencia con columnas de tipo ?. No se procesará.");
            return false;
        }

        if (tableCreate != ''){
            method = "post"
            stringSql = stringSqlCopy
        }

        responseBody(response, 200,"OK","SQL procesado con éxito", {
            method: method,
            sql: stringSql,
            dataSql: ast,
            arrayColumns: arrayColumns,
            arrayTables: arrayTables,
            arraySelect: arraySelect,
            //columns: (method == "get") ? ast.columns : [],
            tableCreate: tableCreate,
            typeInsertTable : typeInsertTable
        });
       

    }catch(error){
        responseBody(response, 400,"BAD_REQUEST", "No se pudo procesar el sql. Error: "+error.message);
    }

}

exports.ast = ast
exports.astSchema = astSchema