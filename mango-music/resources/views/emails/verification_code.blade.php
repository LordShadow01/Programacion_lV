<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Código de Verificación — Mango Music</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      background-color: #FAF8F6;
      color: #1A1614;
      line-height: 1.6;
      padding: 40px 20px;
    }
    .email-container {
      max-width: 500px;
      margin: 0 auto;
      background-color: #FFFFFF;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.02);
      border: 1px solid #F0EAE5;
    }
    .header {
      background: linear-gradient(135deg, #FF9F59 0%, #FF7A1A 100%);
      padding: 30px;
      text-align: center;
      color: #FFFFFF;
    }
    .header h1 {
      font-size: 1.5rem;
      font-weight: 800;
      letter-spacing: -0.5px;
    }
    .content {
      padding: 40px 30px;
      text-align: center;
    }
    .description {
      font-size: 0.95rem;
      color: #666;
      margin-bottom: 25px;
    }
    .code-box {
      font-size: 2.2rem;
      font-weight: 800;
      color: #FF7A1A;
      letter-spacing: 5px;
      padding: 15px 30px;
      background-color: #FFF5EE;
      border-radius: 16px;
      display: inline-block;
      margin-bottom: 25px;
      border: 1px dashed #FFDAB9;
    }
    .footer {
      padding: 20px;
      background-color: #FAF8F6;
      border-top: 1px solid #F0EAE5;
      text-align: center;
      font-size: 0.8rem;
      color: #999;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="header">
      <h1>Mango Music</h1>
    </div>
    <div class="content">
      <p class="description">Usa el siguiente código de verificación de 6 dígitos para restablecer tu contraseña. Este código vencerá en 10 minutos.</p>
      <div class="code-box">{{ $code }}</div>
      <p class="description">Si no has solicitado este código, puedes ignorar este mensaje.</p>
    </div>
    <div class="footer">
      &copy; 2026 Mango Music. Tu música, tu comunidad.
    </div>
  </div>
</body>
</html>
