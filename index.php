<?php
// Definir la ruta raíz para una gestión de archivos consistente
define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);

require_once ROOT_PATH . 'config/session.php';
require_once ROOT_PATH . 'config/db.php';

$page = $_GET['page'] ?? 'catalogo';

// --- INICIO: Lógica de acciones que no renderizan vista (GET) ---
if ($page === 'logout') {
    logout(); // Cierra la sesión (definido en config/session.php)
    header('Location: index.php');
    exit;
}

// --- INICIO: Lógica de procesamiento de formularios ---
// Este bloque se ejecuta ANTES de enviar cualquier salida HTML,
// lo que permite realizar redirecciones con header().
$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($page) {
        case 'registro':
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';
            if (!$nombre || !$email || !$password || !$password2) {
                $error = 'Todos los campos son obligatorios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'El email no es válido.';
            } elseif ($password !== $password2) {
                $error = 'Las contraseñas no coinciden.';
            } else {
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'El email ya está registrado.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
                    $stmt->execute([$nombre, $email, $hash]);
                    header('Location: index.php?page=login&success=1');
                    exit;
                }
            }
            break;

        case 'login':
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            if (!$email || !$password) {
                $error = 'Todos los campos son obligatorios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'El email no es válido.';
            } else {
                $stmt = $pdo->prepare("SELECT id, password FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['usuario_id'] = $user['id'];

                    // Migrar carrito de sesión (invitado) a base de datos (usuario)
                    if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
                        foreach ($_SESSION['carrito'] as $guest_item) {
                            $pid = $guest_item['perfume_id'];
                            $qty = $guest_item['cantidad'];

                            // Verificar stock actual
                            $stmtStock = $pdo->prepare("SELECT stock FROM perfumes WHERE id = ?");
                            $stmtStock->execute([$pid]);
                            $stock = $stmtStock->fetchColumn();

                            if ($stock > 0) {
                                // Verificar si el usuario ya tiene este producto en su carrito DB
                                $stmtCheck = $pdo->prepare("SELECT id, cantidad FROM carrito WHERE usuario_id = ? AND perfume_id = ?");
                                $stmtCheck->execute([$user['id'], $pid]);
                                $db_item = $stmtCheck->fetch();

                                if ($db_item) {
                                    // Actualizar cantidad (sumar y validar stock)
                                    $new_qty = $db_item['cantidad'] + $qty;
                                    if ($new_qty > $stock) $new_qty = $stock;
                                    $stmtUpdate = $pdo->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
                                    $stmtUpdate->execute([$new_qty, $db_item['id']]);
                                } else {
                                    // Insertar nuevo
                                    if ($qty > $stock) $qty = $stock;
                                    $stmtInsert = $pdo->prepare("INSERT INTO carrito (usuario_id, perfume_id, cantidad) VALUES (?, ?, ?)");
                                    $stmtInsert->execute([$user['id'], $pid, $qty]);
                                }
                            }
                        }
                        // Limpiar carrito de sesión una vez migrado
                        unset($_SESSION['carrito']);
                    }

                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Email o contraseña incorrectos.';
                }
            }
            break;
    }
}
// --- FIN: Lógica de procesamiento de formularios ---

// --- INICIO: Lógica de pre-procesamiento y seguridad de página ---
$user = null;
$public_protected_pages = ['mi-cuenta', 'historial-pedidos', 'finalizar-compra'];
$admin_pages = [
    'admin_dashboard', 'admin_productos', 'admin_producto_form', 
    'admin_marcas', 'admin_pedidos', 'admin_suscripciones', 'admin_producto_delete'
];
$all_protected_pages = array_merge($public_protected_pages, $admin_pages);

if (in_array($page, $all_protected_pages)) {
    // 1. Si el usuario no está logueado, redirigir a la página de login.
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }

    // 2. Si la página necesita datos del usuario, los cargamos aquí.
    $stmt_user = $pdo->prepare("SELECT id, nombre, email, password FROM usuarios WHERE id = ?");
    $stmt_user->execute([$_SESSION['usuario_id']]);
    $user = $stmt_user->fetch();

    if (!$user) {
        logout();
        header('Location: index.php?page=login&error=invalid_user');
        exit;
    }

    // 3. Si es una página de admin, verificar que el usuario es admin (ID=1)
    if (in_array($page, $admin_pages) && $user['id'] != 1) {
        header('Location: index.php?page=catalogo');
        exit;
    }
}

// --- INICIO: Funciones auxiliares y enrutador de vistas ---

// Función auxiliar para buscar la ruta de un archivo en múltiples directorios
function find_path(string $filename): ?string {
    $search_paths = [
        ROOT_PATH . 'public' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR,
        ROOT_PATH . 'views' . DIRECTORY_SEPARATOR,
        ROOT_PATH,
        ROOT_PATH . 'admin' . DIRECTORY_SEPARATOR,
        ROOT_PATH . 'views' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR,
        ROOT_PATH . 'public' . DIRECTORY_SEPARATOR,
    ];
    foreach ($search_paths as $path) {
        $full_path = $path . $filename;
        if (file_exists($full_path)) {
            return $full_path;
        }
    }
    return null;
}

// Función para mostrar un error de carga de forma estandarizada
function show_load_error(string $filename): void {
    echo "<div style='font-family: sans-serif; padding: 1rem; margin: 1rem; background-color: #ffe3e3; border: 1px solid #ffb3b3; color: #b30000;'><strong>Error de Carga:</strong> No se pudo encontrar el archivo '<strong>" . htmlspecialchars($filename) . "</strong>'.</div>";
}

// Rutas de API que no deben renderizar HTML
if (strpos($page, 'api_') === 0) {
    $api_path = find_path($page . '.php');
    if ($api_path) {
        include $api_path;
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Endpoint no encontrado.']);
    }
    exit;
}

// --- INICIO: Renderizado de Página ---

if (in_array($page, $admin_pages)) {
    // --- LAYOUT DE ADMINISTRADOR ---
    echo '<!DOCTYPE html><html lang="es"><head>';
    echo '<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Admin Panel - Villa Luro Store</title>';
    echo '<script src="https://cdn.tailwindcss.com"></script>';
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">';
    echo '<link rel="stylesheet" href="public/css/custom.css">';
    echo '<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '<script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        \'serif\': [\'Playfair Display\', \'serif\'],
                        \'sans\': [\'Montserrat\', \'sans-serif\'],
                    },
                    colors: {
                        \'luxury-gold\': \'#D4AF37\',
                        \'luxury-matte\': \'#1A1A1A\',
                        \'luxury-bone\': \'#F9F7F2\',
                    }
                }
            }
        }
    </script>';
    echo '</head><body class="bg-gray-100">';
    echo '<div class="flex min-h-screen">';
    
    $sidebar_path = find_path('admin_sidebar.php');
    if ($sidebar_path) {
        include $sidebar_path;
    } else {
        show_load_error('admin_sidebar.php');
    }

    echo '<main class="flex-grow p-6 md:p-10">';
    $page_filename = $page . '.php';
    $page_path = find_path($page_filename);
    if ($page_path) {
        // Las variables $error y $mensaje del procesamiento POST están disponibles aquí
        include $page_path;
    } else {
        echo "<h1>Error 404</h1><p>Página de administración no encontrada: " . htmlspecialchars($page_filename) . "</p>";
    }
    echo '</main>';
    echo '</div>';
    echo '</body></html>';

} else {
    // --- LAYOUT PÚBLICO (LÓGICA EXISTENTE) ---
    $header_path = find_path('header.php');
    if ($header_path) {
        include $header_path;
    } else {
        show_load_error('header.php');
    }

    echo "<main class='min-h-screen bg-white'>";
    if ($page === 'catalogo' && !isset($_GET['marca']) && !isset($_GET['categoria'])) {
        $hero_path = find_path('hero.php');
        if ($hero_path) { include $hero_path; } else { show_load_error('hero.php'); }
    }

    $page_filename = $page . '.php';
    $page_path = find_path($page_filename);
    if ($page_path) {
        include $page_path;
    } else {
        echo "<div style='font-family: sans-serif; padding: 1rem; margin: 1rem; background-color: #fff9e3; border: 1px solid #ffecb3; color: #b38f00;'><strong>Aviso:</strong> La página '<strong>" . htmlspecialchars($page_filename) . "</strong>' no se encontró. Mostrando la página de inicio.</div>";
        $hero_path = find_path('hero.php');
        if ($hero_path) { include $hero_path; } else { show_load_error('hero.php'); }
        $catalogo_path = find_path('catalogo.php');
        if ($catalogo_path) { include $catalogo_path; } else { show_load_error('catalogo.php'); }
    }

    echo "</main>";
    $footer_path = find_path('footer.php');
    if ($footer_path) {
        include $footer_path;
    } else {
        show_load_error('footer.php');
    }
}
