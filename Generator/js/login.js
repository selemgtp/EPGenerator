var Login = {
    data: {},
    validate(){
        $("#user").val($.trim($("#user").val()))
        if ($("#user").val() == ""){
            bootbox.alert("Debes digitar un usuario.")
            return false;
        }else if ($("#password").val() == ""){
            bootbox.alert("Debes digitar una contraseña.")
            return false;
        }else{
            this.data = {
                user:  $("#user").val(),
                password:  $("#password").val()
            }
            return true;
        }
    },
    send(){
        if (this.validate()){
            $.ajax({
                url: 'authenticate.php',
                beforeSend:function(){
                    $("#loader").show();
                },
                data:this.data,
                type : 'POST',
                success: function(response) {
                    $("#loader").hide();
                    console.log(response);
                    if (response == 1){
                        window.location.href = "main.php";
                    }else{
                        bootbox.alert("No se encontraron credenciales de acceso coincidentes. Vuelve a intentarlo.")
                    }
                },

                error: function(a,b,c){
                    $("#loader").hide();
                    bootbox.alert("Ocurrió un error al procesar la solicitud.")
                    console.log(e,b,c)
                }
            });
        }
    }
}