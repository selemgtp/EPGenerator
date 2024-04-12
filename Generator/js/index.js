DML = {
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

OPERATORS = [
    "!=",
    ">=",
    "<=",
    ">",
    "<",
    "in",
    "IN",
    "=",
]

INSECURE_VERBS = ["update", "delete"];

WHERE_VERBS = ["update", "delete", "select"];

var Index = {

    getModules(){
        $.ajax({
            url: 'modules.php',
            beforeSend:function(){
                $("#loader").show();
            },
            data:this.data,
            type:'json',
            type : 'POST',
            success: function(response) {
                console.log(response)
                $("#loader").hide();
                if (response.error){
                    bootbox.alert(error)
                }else{
                    Index.loadModules(JSON.parse(response));
                    Index.getDatabases();
                }
            },

            error: function(a,b,c){
                $("#loader").hide();
                bootbox.alert("Ocurrió un error al procesar la solicitud.")
                console.log(e,b,c)
            }
        });
    },
    getDatabases(){
        $.ajax({
            url: 'databases.php',
            beforeSend:function(){
                $("#loader").show();
            },
            data:{},
            type:'json',
            type : 'POST',
            success: function(response) {
                console.log(response)
                $("#loader").hide();
                if (response.error){
                    bootbox.alert(error)
                }else{
                    Index.loadDatabases(JSON.parse(response));
                }
            },

            error: function(a,b,c){
                $("#loader").hide();
                bootbox.alert("Ocurrió un error al procesar la solicitud.")
                console.log(e,b,c)
            }
        });
    },

    loadModules(json){
        $("#module").empty();
        stringModules = '<option value="">Selecciona un módulo de la lista desplegable</option>';
        for (var i = 0; i < json.length; i++){
            stringModules += '<option value="'+json[i].nombre+'">'+json[i].nombre+'</option>'

        }
        $("#module").append(stringModules);
        $("#module").select2({
            placeholder: "Selecciona",
            
        })
    },

    loadDatabases(json){
        $("#db").empty();
        stringModules = '<option value="">Selecciona una base de datos de la lista desplegable</option>';
        for (var i = 0; i < json.length; i++){
            stringModules += '<option value="'+json[i].name+'">'+json[i].name+'</option>'
            Index.arrayDatabases.push(json[i].name)
        }
        $("#db").append(stringModules);
        $("#db").select2({
            placeholder: "Selecciona",
            
        })
    },

    expression: /[A-Za-z]+/,

    arraySql : [],
    arrayWhere: [],

    arrayDatabases: [],

    validateName(){
        $("#name").val($.trim($("#name").val()))
        return this.expression.test($("#name").val());
    },

    focusElement(element){
        switch(element.id){
            case "name":
                $(element).attr("placeholder", "nombreconsulta");
            break;

            case "sql":
                $(element).attr("placeholder", "select * from tabla where annio = ? and mes = ? and dia = ?");
            break;

        }
    },

    blurElement(element){
        $(element).attr("placeholder", "");
    },

    validateForm(){
        $("#sql").val($.trim($("#sql").val()))
        $("#name").val($.trim($("#name").val()))
        if ($("#module").val() == ""){
            bootbox.alert("Selecciona un módulo");
            return false;
        /*}else if (!this.validateName() || $("#name").val() == ""){
            bootbox.alert("Digita un nombre de consulta correcto");
        */}else if ($("#sql").val() == ""){
            bootbox.alert("Digita una consulta valida.");
        }else{
            if ($("#name").val() == ""){
                this.analizeSql()
            }else{
                if (!this.validateName()){
                    bootbox.alert("Digita un nombre de consulta correcto");
                }else{
                    this.analizeSql()
                }
            }
        }
    },
    
    analizeSql(){
        var error = null;
        let parser = null
        let ast = null
        let method = "";
        let sql = "";
        let messageConfirm = "¿Estás seguro que deseas enviar la consulta?"
        $("#sql").val($.trim($("#sql").val()))
        var stringSql = $("#sql").val().replace(/;/g, '');
        stringSql = stringSql.replace(/ ,/g, ',');
        stringSql = stringSql.replace(/;/g, '');
        stringSql = stringSql.replace(/\n|\r/g, "");
        var stringSqlCopy = stringSql;
        var queryType = $("#query_type").val();
        var tableCreate = '';
        var typeInsertTable = ''
        var partsStringSql = stringSql.split(" ");

        var partsStringSql = partsStringSql.filter(function (el) {
            return el != " ";
          });

        if (partsStringSql[0].toLowerCase() == "create"){
            typeInsertTable = "CREATE"
            if (partsStringSql[1].toLowerCase() != "table"){
                bootbox.alert("Se detecto una consulta de creación de tabla con error de sintaxis.")
                return false;
            }

            if (queryType == "UNIQ"){
                if (partsStringSql[2].indexOf(".") > -1){
                    bootbox.alert("Se detecto un punto que se interpreta como notacion basededatso.tabla. El tipo de consulta es de una única base de datos. Revisa y vuelve a intentarlo.")
                    return false;
                }else{
                    tableCreate = partsStringSql[2];
                }
            }else{
                if (partsStringSql[2].indexOf(".") == -1){
                    bootbox.alert("Falta la referencia a la base de datos en el create.")
                    return false;
                }else{
                    tableCreate = partsStringSql[2];
                    var dbTable = tableCreate.split(".")[0]
                    if (Index.arrayDatabases.includes(dbTable) == false){
                        bootbox.alert("La base de datos "+dbTable +" NO existe dentro de las bases de datos permitidas: "+Index.arrayDatabases.join(","))
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
                bootbox.alert("Falta el AS después del nombre de tabla.")
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
                        bootbox.alert("Se detecto un punto que se interpreta como notacion basededatso.tabla. El tipo de consulta es de una única base de datos. Revisa y vuelve a intentarlo.")
                        return false;
                    }else{
                        tableCreate = partsStringSql[indexInto + 1];
                    }
                }else{
                    if (partsStringSql[indexInto + 1].indexOf(".") == -1){
                        bootbox.alert("Falta la referencia a la base de datos en el create.")
                        return false;
                    }else{
                        tableCreate = partsStringSql[indexInto + 1];
                        var dbTable = tableCreate.split(".")[0]
                        if (Index.arrayDatabases.includes(dbTable) == false){
                            bootbox.alert("La base de datos "+dbTable +" NO existe dentro de las bases de datos permitidas: "+Index.arrayDatabases.join(","))
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
                bootbox.alert("Error de sintaxis cerca de drop");
                return false;
            }else if (partsStringSql[2] != '?'){
                bootbox.alert("La consulta tipo DROP debe ir con el nombre de la tabla como parámetro ?");
                return false;
            }else{

                bootbox.confirm(messageConfirm, function(result){
                    if (result){
                        var data = {
                            method: 'post',
                            sqlUser: stringSql,
                            queryType: queryType
                        }
                        $("#data").val(JSON.stringify(data))
    
                        //return false;
                        $("#dataForm").submit();
                    }
                })
                return false;
            }
        }


        try{

            parser = new NodeSQLParser.Parser()
            ast = parser.astify(stringSql)
           
            console.log(ast)
            sql = parser.sqlify(ast)
            console.log(sql)
    
               
    
        }catch(e){
            console.log(e)
            error = e;
            //return false
            //bootbox.alert("Ocurrió un error al procesar la consulta: "+e.name+". "+e.message);
            //console.log(e.message)
           
    
        }

        if (error != null){
         
            bootbox.alert("Ocurrió un error al procesar la consulta: "+error.name+". "+error.message);
           
            return false;
        }else{
            if (ast.type == null || ast.type == "" || typeof ast.type == "undefined"){
                bootbox.alert("No se reconoce un tipo de consulta.");
                return false;
            }else{
                method = DML[ast.type].method;
                if (INSECURE_VERBS.includes(ast.type) && ast.where == null){
                    messageConfirm = "Se ha detectado una consulta de tipo "+ast.type+" sin un where. ¿Deseas continuar?";
                }

                

               // ast.configParams = Index.getOperator(ast);

                var arraySelect = Array();

               var arrayColumns = Array();

                switch (method){

                    case  "post":
                    if (ast.columns == null){
                        bootbox.alert("Debes nombrar las columnas a afectar para definir el JSON de Request.");
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


                        var columsSelect = Index.getOperator(ast);
                       
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
                            bootbox.alert("El número de parámetros no concuerda con el número de columnas.");
                            return false;
                        }

                    }else{

                        if (ast.values.length > 1){
                            bootbox.alert("No se permite multiple inserción.");
                            return false;
                        }
                        for (var i = 0; i < ast.values.length; i++){
                            if (ast.values[i].value.length != ast.columns.length){
                                bootbox.alert("El número de parámetros no concuerda con el número de columnas.");
                                return false;
                            }
                        }

                    
                        var uniqs = ast.columns.filter(function(item, index, array) {
                            return array.indexOf(item) === index;
                        })
                        if (uniqs.length != ast.columns.length){
                            bootbox.alert("Se encontraron columnas repetidas. Por favor revisa.");
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

                    //ast.from = Index.getOperatorTables(ast);
                        arraySelect = this.getSelect(ast.columns);
                        //console.log(arraySelect)
                        var columsSelect = this.getOperator(ast);
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

                        var columsSelect = Index.getOperator(ast);
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

                var arrayTables = Array();

                switch(method){
                    case "post":
                    case "put":

                    for (var i = 0; i < ast.table.length; i++){
                        arrayTables.push(ast.table[i]);
                    }

                    arrayTablesTemp = this.getOperatorTables(ast);

                    for (var i = 0; i < arrayTablesTemp.length;i++){
                        arrayTables.push(arrayTablesTemp[i]);
                    }

                    break;
                    case "delete":

                    case "get":
                        arrayTables = this.getOperatorTables(ast);
                    break;
                }


                console.log(arrayTables)
                //console.log(this.getOperator(ast))
                console.log(arrayColumns)

                var send = true;

                

                if (queryType == "UNIQ"){
                    for (var i = 0; i < arrayTables.length; i++){
                        if (arrayTables[i].db != null){
                           
                            bootbox.alert("Se detectó referencia a una base de datos "+arrayTables[i].db +". Recuerda que al seleccionar la opción de única base de datos no se acepta referencia como 'basededatos.tabla'")
                            i = arrayTables.length;
                            send = false;
                        }
                    }
                }else{
                    for (var i = 0; i < arrayTables.length; i++){
                        if (arrayTables[i].db == null){
                           
                            bootbox.alert("Se detectó que no existe referencia a las bases de datos en la tabla "+arrayTables[i].table +". Recuerda que al seleccionar la opción de múltiple base de datos es obligatorio que las tablas tengan la refrencia.")
                            i = arrayTables.length;
                            send = false;
                        }else{
                            if (Index.arrayDatabases.includes(arrayTables[i].db) == false){
                                bootbox.alert("La base de datos "+arrayTables[i].db +" NO existe dentro de las bases de datos permitidas: "+Index.arrayDatabases.join(","))
                                i = arrayTables.length;
                                send = false;
                            }
                        }
                    }
                }

                if (arrayColumns.length == 0){
                    send = false;
                    bootbox.alert("No se detecto una sentencia con columnas de tipo ?. No se procesará.");
                }

                if (send){

                    bootbox.confirm(messageConfirm, function(result){
                        if (result){
                            if (tableCreate != ''){
                                method = "post"
                                stringSql = stringSqlCopy
                            }else{

                                if (arrayTables.length == 1){

                                    bootbox.confirm("Se ha detectado una consulta con una única tabla. Se recomienda ver la documentación de los CRUD´s para usar un endpoint especifíco de tabla en lugar de crear una nuevo. ¿Desea crearlo de todas formas?", function(result){
                                        if (result){
                                            var data = {
                                                method: method,
                                                sqlUser: stringSql,
                                                sqlLibrary: parser.sqlify(ast),
                                                queryType: queryType,
                                                dataSql: ast,
                                                arrayColumns: arrayColumns,
                                                arrayTables: arrayTables,
                                                arraySelect: arraySelect,
                                                columns: (method == "get") ? ast.columns : [],
                                                tableCreate: tableCreate,
                                                typeInsertTable : typeInsertTable
                                            }
                                            $("#data").val(JSON.stringify(data))
                                            
                                            console.log(JSON.stringify(data))

                                            //return false;
                                            $("#dataForm").submit();
                                        }
                                    });

                                }else{

                                    var data = {
                                        method: method,
                                        sqlUser: stringSql,
                                        sqlLibrary: parser.sqlify(ast),
                                        queryType: queryType,
                                        dataSql: ast,
                                        arrayColumns: arrayColumns,
                                        arrayTables: arrayTables,
                                        arraySelect: arraySelect,
                                        columns: (method == "get") ? ast.columns : [],
                                        tableCreate: tableCreate,
                                        typeInsertTable : typeInsertTable
                                    }
                                    $("#data").val(JSON.stringify(data))
                                    console.log(JSON.stringify(data))
                                    //return false;
                                    $("#dataForm").submit();

                                }



                            }
                        }
                    })

                    
                }
            }
            
        }
    },

    getOperator(obj, result = []) {
        let value;
        Object.keys(obj).forEach(key => {
            
          value = obj[key];
          if (key == "right" && obj[key].value && obj[key].value == "?"){
              if (!result.includes(obj))
            result.push(obj);
          }else if (key == "right" && obj[key].type && obj[key].type == "expr_list"){
            if (obj.operator == "BETWEEN"){
               
                var hasParemeter = false;
                for (var i = 0; i < obj[key].value.length; i++){
                    if (obj[key].value[i].value == "?"){
                        hasParemeter = true;
                        i = obj[key].value.length
                    }
                }
                if (hasParemeter){
                    if (!result.includes(obj))
                    result.push(obj);
                }
            }else if (obj.operator == "IN"){
                if (obj[key].value.length == 1 && obj[key].value[0].value == "?"){
                    if (!result.includes(obj))
                    result.push(obj);
                }
            }
          }
          if (value instanceof Object) {
            Index.getOperator(value, result);
          } else if (key === "left") {
            //result.push(value);
          }
        });
        return result;
      },

      getSelect(obj, result = []) {
        let value;
        if (typeof obj == "object" ){
            Object.keys(obj).forEach(key => {
                
            value = obj[key];
            
            if (key == "expr" && obj[key].type == "column_ref"){
                if (!result.includes(obj[key]))
                    result.push(obj[key]);
            }
            if (value instanceof Object) {
                Index.getSelect(value, result);
            } else if (key === "left") {
                //result.push(value);
            }
            });
        }
        return result;
      },

      getOperatorTables(obj, resultTables = []) {
        let value;
        Object.keys(obj).forEach(key => {
            
          value = obj[key];
          if (key == "from"){
            
              //if (!resultTables.includes(obj))
              obj[key].forEach(element => {
                resultTables.push(element)
              });
             // resultTables.concat(obj[key]);
          }
          if (value instanceof Object) {
            Index.getOperatorTables(value, resultTables);
          } else if (key === "left") {
           // resultTables.push(value);
          }
        });
        return resultTables;
      }

    
}


$(document).ready(function(){

    $("#query_type").change(function(){
        $("#db").val("")
        if ($("#query_type").val() == "UNIQ"){
            $("#UNIQ").show();
            $("#MULTIPLE").hide();
        }else{
            $("#UNIQ").hide();
            $("#MULTIPLE").show();
        }
    })

    Index.getModules();
})




//https://github.com/zeroturnaround/sql-formatter