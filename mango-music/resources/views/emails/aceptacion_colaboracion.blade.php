<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>¡Tu postulación fue aceptada! — Mango Music</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      background-color: #F4F4F4;
      font-family: 'Segoe UI', Arial, sans-serif;
      color: #1A1614;
    }
    .wrapper {
      max-width: 620px;
      margin: 40px auto;
      background: #FFFFFF;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 8px 40px rgba(0,0,0,0.10);
    }

    /* ── Header ── */
    .header {
      background: linear-gradient(135deg, #1A1614 0%, #2C2420 100%);
      padding: 40px 48px 32px;
      text-align: center;
    }
    .logo-circle {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 70px;
      height: 70px;
      background: linear-gradient(135deg, #FF7A1A, #FF4D00);
      border-radius: 20px;
      margin-bottom: 18px;
    }
    .logo-circle span {
      font-size: 2rem;
    }
    .brand-name {
      color: #FFFFFF;
      font-size: 1.6rem;
      font-weight: 800;
      letter-spacing: -0.5px;
    }
    .brand-name span {
      color: #FF7A1A;
    }
    .tagline {
      color: #A0948E;
      font-size: 0.85rem;
      margin-top: 4px;
    }

    /* ── Hero Badge ── */
    .hero {
      background: linear-gradient(135deg, #FF7A1A 0%, #FF4D00 100%);
      padding: 36px 48px;
      text-align: center;
    }
    .hero-icon {
      font-size: 3rem;
      display: block;
      margin-bottom: 12px;
    }
    .hero h1 {
      color: #FFFFFF;
      font-size: 1.75rem;
      font-weight: 900;
      line-height: 1.2;
    }
    .hero p {
      color: rgba(255,255,255,0.88);
      font-size: 1rem;
      margin-top: 8px;
    }

    /* ── Body ── */
    .body {
      padding: 40px 48px;
    }
    .greeting {
      font-size: 1.15rem;
      color: #1A1614;
      margin-bottom: 16px;
      line-height: 1.5;
    }
    .greeting strong {
      color: #FF7A1A;
    }

    /* ── Info Card ── */
    .info-card {
      background: #FFF8F4;
      border: 1.5px solid #FFD9BC;
      border-radius: 16px;
      padding: 24px 28px;
      margin: 24px 0;
    }
    .info-card .label {
      font-size: 0.72rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #FF7A1A;
      margin-bottom: 4px;
    }
    .info-card .value {
      font-size: 1.1rem;
      font-weight: 700;
      color: #1A1614;
      margin-bottom: 16px;
    }
    .info-card .value:last-child {
      margin-bottom: 0;
    }

    .body p {
      font-size: 0.97rem;
      color: #444;
      line-height: 1.65;
      margin-bottom: 16px;
    }

    /* ── CTA Button ── */
    .cta-wrap {
      text-align: center;
      margin: 32px 0 8px;
    }
    .cta-btn {
      display: inline-block;
      background: linear-gradient(135deg, #FF7A1A, #FF4D00);
      color: #FFFFFF !important;
      text-decoration: none;
      font-weight: 800;
      font-size: 1rem;
      padding: 16px 40px;
      border-radius: 50px;
      letter-spacing: 0.3px;
    }

    /* ── Footer ── */
    .footer {
      background: #F9F9F9;
      border-top: 1px solid #F0F0F0;
      padding: 28px 48px;
      text-align: center;
    }
    .footer p {
      font-size: 0.78rem;
      color: #A0A0A0;
      line-height: 1.6;
    }
    .footer a {
      color: #FF7A1A;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="wrapper">

    <!-- HEADER -->
    <div class="header">
      <div class="logo-circle"><span>🥭</span></div>
      <div class="brand-name">Mango <span>Music</span></div>
      <div class="tagline">Tu música. Tu comunidad.</div>
    </div>

    <!-- HERO -->
    <div class="hero">
      <span class="hero-icon">🎉</span>
      <h1>¡Tu postulación fue aceptada!</h1>
      <p>El productor ha revisado tu perfil y quiere trabajar contigo.</p>
    </div>

    <!-- BODY -->
    <div class="body">
      <p class="greeting">
        Hola, <strong>{{ $postulante['nombre_artistico'] ?? $postulante['nombre'] }}</strong> 👋
      </p>

      <p>
        Tenemos una excelente noticia: el productor ha revisado tu postulación y ha decidido
        <strong>aceptarte</strong> para la siguiente convocatoria dentro de Mango Music.
      </p>

      <!-- Detalles de la convocatoria -->
      <div class="info-card">
        <div class="label">Convocatoria</div>
        <div class="value">{{ $solicitud['titulo'] }}</div>

        <div class="label">Género Musical</div>
        <div class="value">{{ $solicitud['genero_musical'] ?? '—' }}</div>

        @if(!empty($solicitud['descripcion']))
        <div class="label">Descripción del Proyecto</div>
        <div class="value" style="font-size:0.95rem; font-weight:500; color:#555;">
          {{ $solicitud['descripcion'] }}
        </div>
        @endif
      </div>

      <p>
        El siguiente paso es coordinar los detalles directamente con el productor.
        Mantente atento a los mensajes que puedan llegar a este correo.
      </p>

      <p>
        ¡Mucho éxito en este nuevo proyecto musical! 🎵
      </p>

      <div class="cta-wrap">
        <a href="http://localhost:8000/artista/colaboraciones" class="cta-btn">
          Ver mis Convocatorias
        </a>
      </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
      <p>
        Este correo fue enviado automáticamente por <strong>Mango Music</strong>.<br/>
        Si crees que has recibido este mensaje por error, ignóralo.<br/>
        <a href="#">Política de privacidad</a> · <a href="#">Contacto</a>
      </p>
    </div>

  </div>
</body>
</html>
