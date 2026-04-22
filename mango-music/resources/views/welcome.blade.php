<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mango Music - Admin</title>
    
    <!-- Bootstrap CSS y Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0d0d0d;
            color: #ffffff;
            min-height: 100vh;
        }
        
        .bg-mango {
            background-color: #ff9800 !important;
        }
        
        .text-mango {
            color: #ff9800 !important;
        }
        
        .border-mango {
            border-color: #ff9800 !important;
        }
        
        .btn-mango {
            background-color: #ff9800;
            color: #0d0d0d;
            font-weight: 600;
            border: none;
        }
        
        .btn-mango:hover {
            background-color: #e68a00;
            color: #0d0d0d;
            transform: scale(1.02);
            transition: all 0.2s ease-in-out;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .custom-navbar {
            background: linear-gradient(90deg, #000000 0%, #1a1a1a 100%);
            border-bottom: 2px solid #ff9800;
        }

        .custom-table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
        }
        .app-footer {
            margin-top: auto;
            border-top: 1px solid #333;
        }
    </style>
</head>
<body class="d-flex flex-column">

    <!-- Header Section -->
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar py-3 shadow-lg">
        <div class="container">
            <a class="navbar-brand text-white d-flex align-items-center" href="/">
                <img src="/logo.png" alt="Mango Music Logo" style="height: 45px; border-radius: 8px; margin-right: 12px;">
                MANGO <span class="text-mango ms-1">MUSIC</span>
            </a>
            <span class="navbar-text ms-auto d-none d-md-block text-muted">
                Admin Panel <small class="text-mango">v1.2</small>
            </span>
        </div>
    </nav>

    <!-- Main Vue App Section -->
    <main id="app" class="flex-grow-1 py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-md-8 mx-auto">
                    <h1 class="display-5 fw-bold mb-3">Panel de <span class="text-mango">Administración</span></h1>
                    <p class="lead text-white">Gestiona el catálogo musical de Mango Music. Aquí puedes agregar los mejores artistas locales y organizar su música fácilmente.</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-xl-6">
                    <!-- Vue Component 1 -->
                    <artistas-component></artistas-component>
                </div>
                <div class="col-xl-6">
                    <!-- Vue Component 2 -->
                    <canciones-component></canciones-component>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Section -->
    <footer class="app-footer bg-black text-center py-4 text-muted">
        <div class="container">
            <small>&copy; 2026 Mango Music Platform. Todos los derechos reservados. Desarrollado con Laravel & Vue.js</small>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
