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
  <form action="procesar-formularios.php" method="post" enctype="multipart/form-data">
    <a href="panel-usuario.php">Ver mis solicitudes</a>
    <img src="img/Justech-text-logo.png" alt="" style="width: 130px; position: relative; left: 450px; top: 10px;">
    <h3>FORMULARIO DE SOLICITUD DE INSTALACIÓN O REPARACIÓN DE RED</h3>
    <hr>

    <h1>Datos de ubicación</h1>
    <label for="sucursal" class="sucursal">Sucursal:</label>
    <input type="text" required name="sucursal" id="sucursal" placeholder="Sucursal">

    <label for="piso" class="piso">Piso / área específica:</label>
    <input type="number" required name="piso" id="piso" placeholder="Piso">

    <label for="contacto" class="contacto">Persona de contacto en sitio:</label>
    <input type="text" required placeholder="Nombre / Teléfono" name="contacto" id="contacto">
    <div class="checkbox-group" id="tipo-servicio">
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
          <h1>Cantidad requeridas</h1>

          <label>
            <input type="checkbox" id="chk_puntos_instalar" name="chk_puntos_instalar">
            Cantidad de puntos de red a instalar
          </label>
          <input type="number" name="cantidad_puntos_instalar" id="cantidad_puntos_instalar" class="oculto" placeholder="Ingrese cantidad a instalar" min="0">

          <label>
            <input type="checkbox" id="chk_puntos_reparar" name="chk_puntos_reparar">
            Cantidad de puntos de red a reparar
          </label>
          <input type="number" name="cantidad_puntos_reparar" id="cantidad_puntos_reparar" class="oculto" placeholder="Ingrese cantidad a reparar" min="0">
          <label>
          <input type="checkbox" id="chk_puntos_otros" name="chk_puntos_otros">
          Otros
        </label>
        <input type="text" id="cantidad_puntos_otros" name="cantidad_puntos_otros" class="oculto" placeholder="Especifique otro requerimiento">

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
            <label>Bocas:</label>
            <div>
              <label><input type="checkbox" name="faceplate_bocas_visible[]" value="1"> 1</label>
              <input type="number" name="faceplate_1_cantidad" placeholder="Cantidad requerida">
            </div>
            <div>
              <label><input type="checkbox" name="faceplate_bocas_visible[]" value="2"> 2</label>
              <input type="number" name="faceplate_2_cantidad" placeholder="Cantidad requerida">
            </div>
            <div>
              <label><input type="checkbox" name="faceplate_bocas_visible[]" value="3"> 3</label>
              <input type="number" name="faceplate_3_cantidad" placeholder="Cantidad requerida">
            </div>
            <div >
              <label><input type="checkbox" name="faceplate_bocas_visible[]" value="4"> 4</label>
              <input type="number" name="faceplate_4_cantidad" placeholder="Cantidad requerida">
            </div>

            <label for="faceplate_cantidad_visible">Cantidad total de faceplates:</label>
            <input type="number" name="faceplate_cantidad_visible" id="faceplate_cantidad_visible" placeholder="Cantidad total">
          </div>


    </div>

    <hr>
    <hr>
<h1>Canalización y montaje</h1>
<div class="checkbox-group" id="canalizacion">

  <label>
    <input type="checkbox" name="canalizacion[]" value="Canaleta plástica">
    Canaleta plástica
  </label>
  <div id="campo-canaleta" class="oculto">
    <label for="tamano_canaleta">Tamaño de canaleta plástica:</label>
    <input type="text" name="tamano_canaleta" id="tamano_canaleta" placeholder="Ej: 40x25">
  </div>

  <label>
    <input type="checkbox" name="canalizacion[]" value="EMT metálico">
    EMT metálico
  </label>
  <div id="campo-diametro" class="oculto">
    <label for="diametro_emt">Diámetro EMT:</label>
    <input type="text" name="diametro_emt" id="diametro_emt" placeholder="Ej: 25mm">
  </div>

  <label>
    <input type="checkbox" name="canalizacion[]" value="Bandeja portacable">
    Bandeja portacable
  </label>
  <div id="campo-bandeja" class="oculto">
    <label for="cantidad_bandeja">Cantidad de bandejas:</label>
    <input type="number" name="cantidad_bandeja" id="cantidad_bandeja" placeholder="Ej: 2">
  </div>

  <label>
    <input type="checkbox" name="canalizacion[]" value="Gabinete">
    Gabinete / Rack
  </label>
  <div id="campo-gabinete" class="oculto">
    <label for="gabinete_tipo">Tipo de gabinete:</label>
    <input type="text" name="gabinete_tipo" id="gabinete_tipo" placeholder="Ej: Rack mural">
    
    <label for="gabinete_tamano">Tamaño del gabinete:</label>
    <input type="text" name="gabinete_tamano" id="gabinete_tamano" placeholder="Ej: 12U">
  </div>

  <label>
    <input type="checkbox" name="canalizacion[]" value="Organizadores de cableado">
    Organizadores de cableado
  </label>
</div>

    <hr>
    <h1>Equipos activos</h1>
    <div class="checkbox-group" id="equipos">
      <!--<label><input type="checkbox" name="equipos[]" value="Switch"> Switch</label>-->
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

    <h2>Autorización especial / requisitos de seguridad</h2>
    <label><input type="checkbox" name="seguridad[]" value="Autorización previa de Seguridad"> Se requiere autorización previa de Seguridad del banco</label>
    <label><input type="checkbox" name="seguridad[]" value="Acceso a sala técnica"> Se requiere acceso a sala técnica / Data Center</label>
    <label><input type="checkbox" name="seguridad[]" value="Presencia de personal del banco"> Se requiere presencia de personal del banco en sitio</label>
    <label><input type="checkbox" name="seguridad[]" value="Sin autorizaciones especiales"> No se requieren autorizaciones especiales</label>
    <label class="lbl-id-name" style="position: relative; top: 40px; left: 320px;">Solicitado por: <?= htmlspecialchars($_SESSION['nombre']) ?></label>
    <hr>

    <h2>Adjunto (Opcional)</h2>
        <label for="file1">Archivo opcional 1</label>
        <input type="file" name="file_opcional" id="file1" accept=".pdf,.jpg,.jpeg,.png">
        
        <label for="file2">Archivo opcional 2</label>
        <input type="file" name="file_opcional2" id="file2" accept=".pdf,.jpg,.jpeg,.png">

    <hr>
           <input type="submit" value="Enviar">
           
    <hr>
    <h4 style="text-align: center;">&copy; 2025 JUSTECH</h4>
  </form>
</div>

<script src="js/form.js"></script>
</body>
</html>
