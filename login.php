<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <title>Iniciar Sesión</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f2f2f2;
      margin: 0;
      padding: 0;
      display: flex;
      min-height: 100vh;
      justify-content: center;
      align-items: center;
    }

    .login-container {
      width: 100%;
      max-width: 360px;
      background: #fff;
      padding: 20px 25px;
      border-radius: 10px;
      box-shadow: 0px 0px 10px #aaa;
      box-sizing: border-box;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #005baa;
    }

    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 12px 10px;
      margin-top: 12px;
      box-sizing: border-box;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    input[type="submit"] {
      width: 100%;
      padding: 12px;
      margin-top: 24px;
      background-color: #005baa;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }

    input[type="submit"]:hover {
      background-color: #004080;
    }

    @media (max-width: 400px) {
      .login-container {
        margin: 15px;
        padding: 15px 20px;
      }

      input[type="text"], input[type="password"], input[type="submit"] {
        font-size: 14px;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Iniciar Sesión</h2>
    <form id="loginForm">
      <div class="g-recaptcha" data-sitekey="6Le_pm0rAAAAAKr9f9E_OG4QzTmhlo7gVbm4n27E"></div>
      <input type="text" id="usuario" name="usuario" required placeholder="Usuario" />
      <input type="password" id="contrasena" name="contrasena" required placeholder="Contraseña" />
      <input type="submit" value="Iniciar sesión" />
    </form>
  </div>

  <script>
    document.getElementById('loginForm').addEventListener('submit', async function (e) {
      e.preventDefault();

      const form = this;
      const formData = new FormData(form);
      
      const token = grecaptcha.getResponse();
      if (!token) {
        alert("Completa el reCAPTCHA");
        return;
      }

      formData.append('g-recaptcha-response', token);
      
      try {
        const res = await fetch('login-.php', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: formData
        });

        const json = await res.json();

        if (json.status === 'ok') {
          localStorage.setItem('usuario', json.usuario);
          localStorage.setItem('nombre', json.nombre);
          localStorage.setItem('id_usuario', json.id);
          localStorage.setItem('tipo', json.tipo);

          if (json.tipo === 'admin') {
            window.location.href = 'panel.php';
          } else {
            window.location.href = 'form-tecnico.php';
          }

        } else if (json.status === 'redirect') {
          // Cambio obligatorio de credenciales
          window.location.href = json.redirect;

        } else {
          alert(json.mensaje);
        }

      } catch (error) {
        alert('Error al conectarse con el servidor.');
        console.error(error);
      }
    });
  </script>
</body>
</html>
