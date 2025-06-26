
          /////////NO ME PREGUNTES////////
 document.addEventListener('DOMContentLoaded', () => {
    // === TIPO DE SERVICIO ===
    document.querySelectorAll('#tipo-servicio input').forEach(input => {
      input.addEventListener('change', () => {
        const checkboxes = Array.from(document.querySelectorAll('#tipo-servicio input'));
        const otro = checkboxes.some(i => i.checked && i.value === "Otro");
        const otrosSeleccionados = checkboxes.some(i => i.checked && i.value !== "Otro");

        document.getElementById('campo-describir').classList.toggle('oculto', !otro);
        document.getElementById('campo-cantidad-servicio').classList.toggle('oculto', !otrosSeleccionados);
      });
    });

    // Mostrar/ocultar inputs para cantidad requerida
    function toggleCantidadInput(checkboxId, inputId) {
      const checkbox = document.getElementById(checkboxId);
      const input = document.getElementById(inputId);

      if (!checkbox || !input) return;

      checkbox.addEventListener('change', () => {
        if (checkbox.checked) {
          input.classList.remove('oculto');
          input.required = true;
        } else {
          input.classList.add('oculto');
          input.value = '';
          input.required = false;
        }
      });
    }

    toggleCantidadInput('chk_puntos_instalar', 'cantidad_puntos_instalar');
    toggleCantidadInput('chk_puntos_reparar', 'cantidad_puntos_reparar');
    toggleCantidadInput('chk_puntos_otros', 'cantidad_puntos_otros');

    // === MATERIALES DINÁMICOS ===
    function toggleField(checkbox, campoId, visibleInputIds, hiddenInputNames) {
      const campo = document.getElementById(campoId);
      campo.classList.toggle('oculto', !checkbox.checked);
      if (!checkbox.checked) {
        visibleInputIds.forEach(id => {
          const el = document.getElementById(id);
          if (el) el.value = '';
        });
        hiddenInputNames.forEach(name => {
          const el = document.querySelector(`input[name="${name}"]`);
          if (el) el.value = '';
        });
      }
    }

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
        visibles: ["faceplate_cantidad_visible"], // las bocas se tratan aparte
        ocultos: ["faceplate_bocas", "faceplate_cantidad"]
      }
    };

    document.querySelectorAll('#materiales input[type="checkbox"]').forEach(input => {
      input.addEventListener('change', () => {
        const m = materialesMap[input.value];
        if (m) toggleField(input, m.campo, m.visibles, m.ocultos);
      });
    });

    // === Mostrar/ocultar inputs cantidad para cada boca Faceplate ===
    const bocasCheckboxes = document.querySelectorAll('input[name="faceplate_bocas_visible[]"]');
    bocasCheckboxes.forEach(cb => {
      const inputCantidad = document.querySelector(`input[name="faceplate_${cb.value}_cantidad"]`);
      if (!inputCantidad) return;

      // Inicializar visibilidad y requerimiento según estado al cargar
      if (cb.checked) {
        inputCantidad.classList.remove('oculto');
        inputCantidad.required = true;
      } else {
        inputCantidad.classList.add('oculto');
        inputCantidad.value = '';
        inputCantidad.required = false;
      }

      cb.addEventListener('change', () => {
        if (cb.checked) {
          inputCantidad.classList.remove('oculto');
          inputCantidad.required = true;
        } else {
          inputCantidad.classList.add('oculto');
          inputCantidad.value = '';
          inputCantidad.required = false;
        }
      });
    });

    // === CANALIZACIÓN DINÁMICA ===
    const canalizacionMap = {
      "Canaleta plástica": {
        campoId: "campo-canaleta",
        inputs: ["tamano_canaleta"]
      },
      "EMT metálico": {
        campoId: "campo-diametro",
        inputs: ["diametro_emt"]
      },
      "Bandeja portacable": {
        campoId: "campo-bandeja",
        inputs: ["cantidad_bandeja"]
      },
      "Gabinete": {
        campoId: "campo-gabinete",
        inputs: ["gabinete_tipo", "gabinete_tamano"]
      }
    };

    document.querySelectorAll('#canalizacion input[type="checkbox"]').forEach(input => {
      input.addEventListener('change', () => {
        Object.entries(canalizacionMap).forEach(([key, cfg]) => {
          const checkbox = document.querySelector(`#canalizacion input[value="${key}"]`);
          const activo = checkbox && checkbox.checked;
          document.getElementById(cfg.campoId).classList.toggle('oculto', !activo);
          if (!activo) {
            cfg.inputs.forEach(id => {
              const el = document.getElementById(id);
              if (el) el.value = '';
            });
          }
        });
      });
    });

    // === Añadir automáticamente "mm" a entradas ===
    function agregarMM(inputId) {
      const input = document.getElementById(inputId);
      if (!input) return;
      input.addEventListener('input', () => {
        let val = input.value.trim();
        if (val && !val.toLowerCase().endsWith("mm")) {
          val = val.replace(/[^0-9]/g, '');
          input.value = val + "mm";
        }
      });
    }

    agregarMM("tamano_canaleta");
    agregarMM("diametro_emt");

 function actualizarEquiposActivos() {
  const otros = document.querySelector('#equipos input[value="Otros"]')?.checked;
  const pdu = document.querySelector('#equipos input[value="PDU"]')?.checked;

  document.getElementById('campo-especificar').classList.toggle('oculto', !otros);
  if (!otros) document.getElementById('equipo_otro').value = '';

  document.getElementById('campo-pdu').classList.toggle('oculto', !pdu);
  if (!pdu) document.getElementById('cantidad_pdu').value = '';
}

document.querySelectorAll('#equipos input[type="checkbox"]').forEach(input => {
  input.addEventListener('change', actualizarEquiposActivos);
});

// Ejecutar al cargar la página por si viene preseleccionado
actualizarEquiposActivos();


    // === Al enviar ===
    document.querySelector('form').addEventListener('submit', function(event) {
      // Validar campos mm
      const mmCampos = [
        { id: "tamano_canaleta", nombre: "Tamaño de canaleta" },
        { id: "diametro_emt", nombre: "Diámetro EMT" }
      ];

      for (const campo of mmCampos) {
        const input = document.getElementById(campo.id);
        if (input && !input.closest('.oculto')) {
          const valor = input.value.trim().toLowerCase();
          if (!/^\d+mm$/.test(valor)) {
            alert(`El campo "${campo.nombre}" debe contener solo números seguidos de 'mm' (ej: 40mm)`);
            input.focus();
            event.preventDefault();
            return;
          }
        }
      }

      // Copiar visibles a ocultos
      document.querySelector('input[name="detalle_Cable_UTP"]').value =
        document.getElementById('detalle_Cable_UTP_visible')?.value || '';

      document.querySelector('input[name="detalle_Patch_cord"]').value =
        document.getElementById('detalle_Patch_cord_visible')?.value || '';

      document.querySelector('input[name="detalle_Conector_RJ45"]').value =
        document.getElementById('detalle_Conector_RJ45_visible')?.value || '';

      document.querySelector('input[name="detalle_Jack"]').value =
        document.getElementById('detalle_Jack_visible')?.value || '';

      document.querySelector('input[name="detalle_Patch_panel"]').value =
        document.getElementById('detalle_Patch_panel_visible')?.value || '';

      // COPIAR BOCA MULTIPLE (faceplate)
      const faceplateBocasSeleccionadas = Array.from(
        document.querySelectorAll('input[name="faceplate_bocas_visible[]"]:checked')
      ).map(cb => cb.value);
      document.querySelector('input[name="faceplate_bocas"]').value =
        faceplateBocasSeleccionadas.join(', ');

      document.querySelector('input[name="faceplate_cantidad"]').value =
        document.getElementById('faceplate_cantidad_visible')?.value || '';
    });
  });
