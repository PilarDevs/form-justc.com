document.addEventListener("DOMContentLoaded", function () {
  const tipoServicio = document.getElementById("tipo-servicio");
  const campoDescribir = document.getElementById("campo-describir");

  const materiales = document.getElementById("materiales");
  const campoFaceplate = document.getElementById("campo-faceplate");

  const equipos = document.getElementById("equipos");
  const campoEspecificar = document.getElementById("campo-especificar");

  const canalizacion = document.getElementById("canalizacion");
  const campoDiametro = document.getElementById("campo-diametro");
  const campoGabinete = document.getElementById("campo-gabinete");

  tipoServicio.addEventListener("change", () => {
    const opciones = Array.from(tipoServicio.selectedOptions).map(opt => opt.text);
    campoDescribir.classList.toggle("oculto", !opciones.includes("Otro (describir)"));
  });

  materiales.addEventListener("change", () => {
    const opciones = Array.from(materiales.selectedOptions).map(opt => opt.text);
    campoFaceplate.classList.toggle("oculto", !opciones.includes("Faceplate"));
  });

  equipos.addEventListener("change", () => {
    const valor = equipos.options[equipos.selectedIndex].text;
    campoEspecificar.classList.toggle("oculto", valor !== "Otros (especificar)");
  });

  canalizacion.addEventListener("change", () => {
    const opciones = Array.from(canalizacion.selectedOptions).map(opt => opt.text);
    campoDiametro.classList.toggle("oculto", !opciones.includes("EMT metálico — diámetro:"));
    campoGabinete.classList.toggle("oculto", !opciones.includes("Gabinete / Rack (tipo / tamaño):"));
  });
});
