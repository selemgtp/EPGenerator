const Utils = {

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
            Utils.getOperator(value, result);
          } else if (key === "left") {
            //result.push(value);
          }
        });
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
            Utils.getOperatorTables(value, resultTables);
        } else if (key === "left") {
        // resultTables.push(value);
        }
        });
        return resultTables;
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
                Utils.getSelect(value, result);
            } else if (key === "left") {
                //result.push(value);
            }
            });
        }
        return result;
      },

}


exports.Utils = Utils

