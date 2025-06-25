<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="img/just-logo.png" type="image/x-icon">
  <link rel="stylesheet" href="css/style.css">
  <title>Formulario</title>
</head>
<body>
<div class="container">
  <form action="procesar-formularios.php" method="post">
    <h3>FORMULARIO DE SOLICITUD DE INSTALACIÓN O REPARACIÓN DE RED</h3>
    <hr>

    <h1>Datos de ubicación</h1>
    <label for="sucursal" class="sucursal">Sucursal:</label>
    <input type="text" required name="sucursal" id="sucursal" placeholder="Sucursal">

    <label for="piso" class="piso">Piso / área específica:</label>
    <input type="number" required name="piso" id="piso" placeholder="Piso">

    <label for="contacto" class="contacto">Persona de contacto en sitio:</label>
    <input type="text" required placeholder="Nombre / Teléfono" name="contacto" id="contacto">
    <hr>

    <h1>Tipo de servicio requerido</h1>
    <div class="checkbox-group" id="tipo-servicio">
      <label><input type="checkbox" name="tipo_servicio[]" value="Instalación de nuevos puntos"> Instalación de nuevos puntos</label>
      <label><input type="checkbox" name="tipo_servicio[]" value="Reparación de puntos existentes"> Reparación de puntos existentes</label>
      <label><input type="checkbox" name="tipo_servicio[]" value="Otro"> Otro (describir)</label>
    </div>

    <div id="campo-describir" class="oculto">
      <label for="otro_tipo">Describa</label>
      <input type="text" name="otro_tipo" id="otro_tipo">
    </div>

    <div id="campo-cantidad-servicio" class="oculto">
      <label for="cantidad_servicio">Cantidad de puntos requeridos:</label>
      <input type="number" name="cantidad_servicio" id="cantidad_servicio" placeholder="Ingrese cantidad total">
    </div>

    <hr>
    <h1>Cantidad de puntos</h1>
    <div class="input-wrapper">
      <label for="lineas_instalar" class="lineas">Cantidad de líneas a instalar:</label>
      <input type="number" name="lineas_instalar" id="lineas_instalar" placeholder="Líneas a instalar" class="lineasInp">
    </div>

    <div class="input-wrapper">
      <label for="lineas_reparar" class="reparar">Cantidad de líneas a reparar:</label>
      <input type="number" name="lineas_reparar" id="lineas_reparar" placeholder="Líneas a reparar" class="repararInp">
    </div>

    <hr>
    <h1>Materiales / componentes requeridos</h1>
    <div class="checkbox-group" id="materiales">

      <label>
        <input type="checkbox" name="materiales[]" value="Cable UTP Cat6">
        Cable UTP Cat6
      </label>
      <!-- input oculto para enviar siempre el campo aunque esté oculto -->
      <input type="hidden" name="detalle_Cable_UTP" value="">
      <div id="campo-Cable UTP Cat6" class="oculto">
        <label for="detalle_Cable_UTP_visible">Cantidad de metros:</label>
        <input type="number" name="detalle_Cable_UTP_visible" id="detalle_Cable_UTP_visible" placeholder="Ej: 100">
      </div>

      <label>
        <input type="checkbox" name="materiales[]" value="Patch cord (RJ45)">
        Patch cord (RJ45)
      </label>
      <input type="hidden" name="detalle_Patch_cord" value="">
      <div id="campo-Patch cord (RJ45)" class="oculto">
        <label for="detalle_Patch_cord_visible">Cantidad:</label>
        <input type="number" name="detalle_Patch_cord_visible" id="detalle_Patch_cord_visible" placeholder="Cantidad requerida">
      </div>

      <label>
        <input type="checkbox" name="materiales[]" value="Conector RJ45">
        Conector RJ45
      </label>
      <input type="hidden" name="detalle_Conector_RJ45" value="">
      <div id="campo-Conector RJ45" class="oculto">
        <label for="detalle_Conector_RJ45_visible">Cantidad:</label>
        <input type="number" name="detalle_Conector_RJ45_visible" id="detalle_Conector_RJ45_visible" placeholder="Cantidad requerida">
      </div>

      <label>
        <input type="checkbox" name="materiales[]" value="Jack / Mini Jack RJ45">
        Jack / Mini Jack RJ45
      </label>
      <input type="hidden" name="detalle_Jack" value="">
      <div id="campo-Jack / Mini Jack RJ45" class="oculto">
        <label for="detalle_Jack_visible">Cantidad:</label>
        <input type="number" name="detalle_Jack_visible" id="detalle_Jack_visible" placeholder="Cantidad requerida">
      </div>

      <label>
        <input type="checkbox" name="materiales[]" value="Patch panel">
        Patch panel
      </label>
      <input type="hidden" name="detalle_Patch_panel" value="">
      <div id="campo-Patch panel" class="oculto">
        <label for="detalle_Patch_panel_visible">Cantidad de unidades:</label>
        <input type="number" name="detalle_Patch_panel_visible" id="detalle_Patch_panel_visible" placeholder="Unidades">
      </div>

      <label>
        <input type="checkbox" name="materiales[]" value="Faceplate">
        Faceplate
      </label>
      <input type="hidden" name="faceplate_bocas" value="">
      <input type="hidden" name="faceplate_cantidad" value="">
      <div id="campo-faceplate" class="oculto">
        <label for="faceplate_bocas_visible">Bocas:</label>
        <select name="faceplate_bocas_visible" id="faceplate_bocas_visible">
          <option disabled selected>Seleccione bocas</option>
          <option>1</option>
          <option>2</option>
          <option>4</option>
        </select>
        <label for="faceplate_cantidad_visible">Cantidad de faceplates:</label>
        <input type="number" name="faceplate_cantidad_visible" id="faceplate_cantidad_visible" placeholder="Cantidad requerida">
      </div>
    </div>

    <hr>
    <h1>Canalización y montaje</h1>
    <div class="checkbox-group" id="canalizacion">
      <label><input type="checkbox" name="canalizacion[]" value="Canaleta plástica"> Canaleta plástica</label>
      <label><input type="checkbox" name="canalizacion[]" value="EMT metálico"> EMT metálico — diámetro:</label>
      <label><input type="checkbox" name="canalizacion[]" value="Bandeja portacable"> Bandeja portacable</label>
      <label><input type="checkbox" name="canalizacion[]" value="Gabinete"> Gabinete / Rack (tipo / tamaño):</label>
      <label><input type="checkbox" name="canalizacion[]" value="Organizadores de cableado"> Organizadores de cableado</label>
    </div>

    <div id="campo-canaleta" class="oculto">
      <label for="tamano_canaleta">Tamaño de canaleta plástica (mm):</label>
      <input type="text" name="tamano_canaleta" id="tamano_canaleta">
    </div>

    <div id="campo-diametro" class="oculto">
      <label for="diametro_emt">Diámetro EMT (en mm):</label>
      <input type="text" name="diametro_emt" id="diametro_emt">
    </div>

    <div id="campo-bandeja" class="oculto">
      <label for="cantidad_bandeja">Cantidad de bandejas:</label>
      <input type="number" name="cantidad_bandeja" id="cantidad_bandeja">
    </div>

    <div id="campo-gabinete" class="oculto">
      <label for="gabinete_tipo">Tipo de gabinete:</label>
      <input type="text" name="gabinete_tipo" id="gabinete_tipo">
      <label for="gabinete_tamano">Tamaño del gabinete:</label>
      <input type="text" name="gabinete_tamano" id="gabinete_tamano">
    </div>

    <hr>
    <h1>Equipos activos</h1>
    <div class="checkbox-group" id="equipos">
      <label><input type="checkbox" name="equipos[]" value="Switch"> Switch</label>
      <label><input type="checkbox" name="equipos[]" value="PDU"> PDU</label>
      <label><input type="checkbox" name="equipos[]" value="Otros"> Otros (especificar)</label>
    </div>

    <div id="campo-pdu" class="oculto">
      <label for="cantidad_pdu">Cantidad de PDU:</label>
      <input type="number" name="cantidad_pdu" id="cantidad_pdu">
    </div>

    <div id="campo-especificar" class="oculto">
      <label for="equipo_otro">Especificar</label>
      <input type="text" name="equipo_otro" id="equipo_otro">
    </div>

    <hr>
    <h2>Diagnóstico / Comentario adicional</h2>
    <input type="text" name="comentario" id="comentario" placeholder="Sugerencia / Comentario">
    <hr>

    <h1>Prioridad de la solicitud</h1>
    <div class="checkbox-group" id="prioridad">
      <label><input type="radio" name="prioridad" value="Alta (Urgente)" required> Alta (Urgente)</label>
      <label><input type="radio" name="prioridad" value="Media (Planificar próxima visita)"> Media (Planificar próxima visita)</label>
      <label><input type="radio" name="prioridad" value="Baja (Sin urgencia)"> Baja (Sin urgencia)</label>
    </div>
    <hr>

    <input type="submit" value="Enviar">
    <hr>

    <h2>Autorización especial / requisitos de seguridad</h2>
    <label><input type="checkbox" name="seguridad[]" value="Autorización previa de Seguridad"> Se requiere autorización previa de Seguridad del banco</label>
    <label><input type="checkbox" name="seguridad[]" value="Acceso a sala técnica"> Se requiere acceso a sala técnica / Data Center</label>
    <label><input type="checkbox" name="seguridad[]" value="Presencia de personal del banco"> Se requiere presencia de personal del banco en sitio</label>
    <label><input type="checkbox" name="seguridad[]" value="Sin autorizaciones especiales"> No se requieren autorizaciones especiales</label>
    <hr>

    <h4 style="text-align: center;">&copy; JUSTECH</h4>
  </form>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Tipo de servicio
    document.querySelectorAll('#tipo-servicio input').forEach(input => {
      input.addEventListener('change', () => {
        const checkboxes = Array.from(document.querySelectorAll('#tipo-servicio input'));
        const otro = checkboxes.some(i => i.checked && i.value === "Otro");
        const otrosSeleccionados = checkboxes.some(i => i.checked && i.value !== "Otro");

        document.getElementById('campo-describir').classList.toggle('oculto', !otro);
        document.getElementById('campo-cantidad-servicio').classList.toggle('oculto', !otrosSeleccionados);
      });
    });

    // Materiales - campos dinámicos y limpieza automática
    function toggleField(checkbox, campoId, visibleInputIds, hiddenInputNames) {
      const campo = document.getElementById(campoId);
      campo.classList.toggle('oculto', !checkbox.checked);
      if (!checkbox.checked) {
        // Limpiar visibles
        visibleInputIds.forEach(id => {
          const el = document.getElementById(id);
          if (el) el.value = '';
        });
        // Limpiar ocultos
        hiddenInputNames.forEach(name => {
          const el = document.querySelector(`input[name="${name}"]`);
          if (el) el.value = '';
        });
      }
    }

    // Mapeo materiales con sus campos visibles y ocultos
    const materialesMap = {
      "Cable UTP Cat6": {
        campo: "campo-Cable UTP Cat6",
        visibles: ["detalle_Cable_UTP_visible"],
        ocultos: ["detalle_Cable_UTP"]
      },
      "Patch cord (RJ45)": {
        campo: "campo-Patch cord (RJ45)",
        visibles: ["detalle_Patch_cord_visible"],
        ocultos: ["detalle_Patch_cord"]
      },
      "Conector RJ45": {
        campo: "campo-Conector RJ45",
        visibles: ["detalle_Conector_RJ45_visible"],
        ocultos: ["detalle_Conector_RJ45"]
      },
      "Jack / Mini Jack RJ45": {
        campo: "campo-Jack / Mini Jack RJ45",
        visibles: ["detalle_Jack_visible"],
        ocultos: ["detalle_Jack"]
      },
      "Patch panel": {
        campo: "campo-Patch panel",
        visibles: ["detalle_Patch_panel_visible"],
        ocultos: ["detalle_Patch_panel"]
      },
      "Faceplate": {
        campo: "campo-faceplate",
        visibles: ["faceplate_bocas_visible", "faceplate_cantidad_visible"],
        ocultos: ["faceplate_bocas", "faceplate_cantidad"]
      }
    };

    document.querySelectorAll('#materiales input[type="checkbox"]').forEach(input => {
      input.addEventListener('change', () => {
        const m = materialesMap[input.value];
        if (m) {
          toggleField(input, m.campo, m.visibles, m.ocultos);
        }
      });
    });

    // Canalización - campos dinámicos y limpieza
    function limpiarCamposCanalizacion() {
      ['tamano_canaleta', 'diametro_emt', 'cantidad_bandeja', 'gabinete_tipo', 'gabinete_tamano'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
      });
    }

    document.querySelectorAll('#canalizacion input[type="checkbox"]').forEach(input => {
      input.addEventListener('change', () => {
        const canaleta = input.value === "Canaleta plástica" && input.checked;
        const emt = input.value === "EMT metálico" && input.checked;
        const bandeja = input.value === "Bandeja portacable" && input.checked;
        const gabinete = input.value === "Gabinete" && input.checked;

        document.getElementById('campo-canaleta').classList.toggle('oculto', !canaleta);
        if (!canaleta) document.getElementById('tamano_canaleta').value = '';

        document.getElementById('campo-diametro').classList.toggle('oculto', !emt);
        if (!emt) document.getElementById('diametro_emt').value = '';

        document.getElementById('campo-bandeja').classList.toggle('oculto', !bandeja);
        if (!bandeja) document.getElementById('cantidad_bandeja').value = '';

        document.getElementById('campo-gabinete').classList.toggle('oculto', !gabinete);
        if (!gabinete) {
          document.getElementById('gabinete_tipo').value = '';
          document.getElementById('gabinete_tamano').value = '';
        }
      });
    });

    // Equipos activos - campos dinámicos y limpieza
    document.querySelectorAll('#equipos input[type="checkbox"]').forEach(input => {
      input.addEventListener('change', () => {
        const otros = input.value === "Otros" && input.checked;
        const pdu = input.value === "PDU" && input.checked;

        document.getElementById('campo-especificar').classList.toggle('oculto', !otros);
        if (!otros) document.getElementById('equipo_otro').value = '';

        document.getElementById('campo-pdu').classList.toggle('oculto', !pdu);
        if (!pdu) document.getElementById('cantidad_pdu').value = '';
      });
    });

    // Copiar valores de inputs visibles a ocultos justo antes de enviar
    document.querySelector('form').addEventListener('submit', function(event) {
      // Materiales detalles
      document.querySelector('input[name="detalle_Cable_UTP"]').value =
        document.getElementById('detalle_Cable_UTP_visible').value || '';

      document.querySelector('input[name="detalle_Patch_cord"]').value =
        document.getElementById('detalle_Patch_cord_visible').value || '';

      document.querySelector('input[name="detalle_Conector_RJ45"]').value =
        document.getElementById('detalle_Conector_RJ45_visible').value || '';

      document.querySelector('input[name="detalle_Jack"]').value =
        document.getElementById('detalle_Jack_visible').value || '';

      document.querySelector('input[name="detalle_Patch_panel"]').value =
        document.getElementById('detalle_Patch_panel_visible').value || '';

      document.querySelector('input[name="faceplate_bocas"]').value =
        document.getElementById('faceplate_bocas_visible').value || '';

      document.querySelector('input[name="faceplate_cantidad"]').value =
        document.getElementById('faceplate_cantidad_visible').value || '';
    });
  });
</script>
</body>
</html>
