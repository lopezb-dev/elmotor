//Ejecutando funciones
document.getElementById("btn-sesion").addEventListener("click", iniciarSesion);
document.getElementById("btn-registro").addEventListener("click", register);
window.addEventListener("resize", anchoPage);

//Declarando variables
var formulario_sesion = document.querySelector(".formulario-sesion");
var formulario_registro = document.querySelector(".formulario-registro");
var contenedor_sesion_registro = document.querySelector(".contenedor-sesion-registro");
var caja_trasera_sesion = document.querySelector(".caja-trasera-sesion");
var caja_trasera_registro = document.querySelector(".caja-trasera-registro");

//FUNCIONES
function anchoPage(){

    if (window.innerWidth > 850){
        caja_trasera_registro.style.display = "block";
        caja_trasera_sesion.style.display = "block";
    }else{
        caja_trasera_registro.style.display = "block";
        caja_trasera_registro.style.opacity = "1";
        caja_trasera_sesion.style.display = "none";
        formulario_sesion.style.display = "block";
        contenedor_sesion_registro.style.left = "0px";
        formulario_registro.style.display = "none";   
    }
}

anchoPage();


    function iniciarSesion(){
        if (window.innerWidth > 850){
            formulario_sesion.style.display = "block";
            contenedor_sesion_registro.style.left = "10px";
            formulario_registro.style.display = "none";
            caja_trasera_registro.style.opacity = "1";
            caja_trasera_sesion.style.opacity = "0";
        }else{
            formulario_sesion.style.display = "block";
            contenedor_sesion_registro.style.left = "0px";
            formulario_registro.style.display = "none";
            caja_trasera_registro.style.display = "block";
            caja_trasera_sesion.style.display = "none";
        }
    }

    function register(){
        if (window.innerWidth > 850){
            formulario_registro.style.display = "block";
            contenedor_sesion_registro.style.left = "410px";
            formulario_sesion.style.display = "none";
            caja_trasera_registro.style.opacity = "0";
            caja_trasera_sesion.style.opacity = "1";
        }else{
            formulario_registro.style.display = "block";
            contenedor_sesion_registro.style.left = "0px";
            formulario_sesion.style.display = "none";
            caja_trasera_registro.style.display = "none";
            caja_trasera_sesion.style.display = "block";
            caja_trasera_sesion.style.opacity = "1";
        }
}