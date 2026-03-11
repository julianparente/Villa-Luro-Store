<?php
// Definir la ruta raíz para una gestión de archivos consistente
define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php'; // <--- AGREGÁ ESTA LÍNEA


$page = $_GET['page'] ?? 'catalogo';

// --- INICIO: Lógica de acciones que no renderizan vista (GET) ---
if ($page === 'logout') {
    logout(); // Cierra la sesión (definido en config/session.php)
    header('Location: index.php');
    exit;
}

$error = '';
$mensaje = '';

// --- INICIO: Lógica de acciones GET que pueden necesitar redirigir ---
// Este bloque se ejecuta ANTES de enviar cualquier salida HTML.
if ($page === 'admin_promociones' && isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    // Se verifica el permiso de admin antes de proceder
    if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == 1) {
        $id = (int)$_GET['id'];
        try {
            $stmt = $pdo->prepare("UPDATE perfumes SET en_promocion = 0 WHERE id = ?");
            $stmt->execute([$id]);
            redirect('index.php?page=admin_promociones&status=removed');
        } catch (PDOException $e) {
            $error = "Error al actualizar: " . $e->getMessage();
        }
    }
}

if ($page === 'admin_marcas' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == 1) {
        $id = (int)$_GET['id'];
        try {
            // Verificar si hay productos asociados antes de eliminar
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM perfumes WHERE marca_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                // Usamos una variable de sesión para pasar el error a través de la redirección
                $_SESSION['form_error'] = "No se puede eliminar la marca porque tiene productos asociados. Elimine o mueva los productos primero.";
                redirect('index.php?page=admin_marcas');
            } else {
                $stmt = $pdo->prepare("DELETE FROM marcas WHERE id = ?");
                $stmt->execute([$id]);
                redirect('index.php?page=admin_marcas&status=deleted');
            }
        } catch (PDOException $e) { $error = "Error al eliminar: " . $e->getMessage(); }
    }
}



// --- INICIO: Lógica de procesamiento de formularios POST ---
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
            } elseif (strlen($password) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres.';
            } elseif ($password !== $password2) {
                $error = 'Las contraseñas no coinciden.';
            } else {
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'El email ya está registrado.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // 1. Generar Token
                    $token = bin2hex(random_bytes(16));

                    // 2. Insertar usuario con token y activo = 0
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, token, activo) VALUES (?, ?, ?, ?, 0)");
                    $stmt->execute([$nombre, $email, $hash, $token]);

                    // 3. Enviar Email
                    enviarMailVerificacion($email, $token, $nombre);

                    // Redirigir (Nota: Si SMTPDebug está activo, esta redirección podría fallar visualmente, pero el mail debería salir)
                    header('Location: index.php?page=login&success=verify');
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
        
        case 'admin_marcas':
            if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == 1) {
                $nombre = trim($_POST['nombre'] ?? '');
                $id = $_POST['id'] ?? null;

                if (empty($nombre)) {
                    $error = "El nombre de la marca es obligatorio.";
                } else {
                    try {
                        if ($id) {
                            $stmt = $pdo->prepare("UPDATE marcas SET nombre = ? WHERE id = ?");
                            $stmt->execute([$nombre, $id]);
                            redirect('index.php?page=admin_marcas&status=updated');
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO marcas (nombre) VALUES (?)");
                            $stmt->execute([$nombre]);
                            redirect('index.php?page=admin_marcas&status=created');
                        }
                    } catch (PDOException $e) { $error = "Error en base de datos: " . $e->getMessage(); }
                }
            }
            break;

        case 'admin_producto_form':
            if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == 1) {
                if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
                    die("Error de seguridad: Token CSRF inválido.");
                }

                $perfume_id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                $is_edit_mode = $perfume_id !== null;

                $nombre = trim($_POST['nombre'] ?? '');
                $marca_id = (int)($_POST['marca_id'] ?? 0);
                $precio_lista = filter_var($_POST['precio_lista'] ?? 0, FILTER_VALIDATE_FLOAT);
                $precio = filter_var($_POST['precio'] ?? 0, FILTER_VALIDATE_FLOAT);
                $stock = filter_var($_POST['stock'] ?? 0, FILTER_VALIDATE_INT);
                $categoria = in_array($_POST['categoria'], ['masculino', 'femenino', 'unisex']) ? $_POST['categoria'] : 'unisex';
                $descripcion = trim($_POST['descripcion'] ?? '');
                $imagen_url_input = trim($_POST['imagen_url_input'] ?? '');
                $en_promocion = isset($_POST['en_promocion']) ? 1 : 0;
                
                $imagen_url = $_POST['current_imagen_url'] ?? '';

                if (empty($nombre) || empty($marca_id) || $precio === false || $precio_lista === false || $stock === false) {
                    $error = 'Nombre, Marca, Precios y Stock son campos obligatorios y deben ser válidos.';
                } else {
                    if ($en_promocion) {
                        $stmt_promo_count = $pdo->prepare("SELECT COUNT(*) FROM perfumes WHERE en_promocion = 1 AND id != ?");
                        $stmt_promo_count->execute([$perfume_id ?? 0]);
                        if ($stmt_promo_count->fetchColumn() >= 3) {
                            $error = 'No se pueden tener más de 3 perfumes en la oferta semanal. Por favor, quite uno de la promoción antes de añadir este.';
                            $en_promocion = 0;
                        }
                    }

                    if (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
                        $upload_result = upload_image($_FILES['imagen_file'], 'public/img/perfumes/');
                        if ($upload_result['success']) {
                            $imagen_url = $upload_result['path'];
                        } else {
                            $error = $upload_result['error'];
                        }
                    } elseif (!empty($imagen_url_input)) {
                        $imagen_url = $imagen_url_input;
                    }

                    if (empty($error)) {
                        if ($is_edit_mode) {
                            $sql = "UPDATE perfumes SET nombre = ?, marca_id = ?, precio = ?, precio_lista = ?, stock = ?, categoria = ?, descripcion = ?, imagen_url = ?, en_promocion = ? WHERE id = ?";
                            $params = [$nombre, $marca_id, $precio, $precio_lista, $stock, $categoria, $descripcion, $imagen_url, $en_promocion, $perfume_id];
                        } else {
                            $sql = "INSERT INTO perfumes (nombre, marca_id, precio, precio_lista, stock, categoria, descripcion, imagen_url, en_promocion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $params = [$nombre, $marca_id, $precio, $precio_lista, $stock, $categoria, $descripcion, $imagen_url, $en_promocion];
                        }
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        redirect('index.php?page=admin_productos&status=' . ($is_edit_mode ? 'updated' : 'created'));
                    }
                }
                // Si hay un error, la ejecución continúa y la vista `admin_producto_form` se renderizará,
                // mostrando el error y repoblando el formulario.
                $form_data_on_error = $_POST;
                $form_data_on_error['imagen_url'] = $imagen_url;
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
    'admin_marcas', 'admin_pedidos', 'admin_suscripciones', 'admin_producto_delete', 'admin_config',
    'admin_promociones'
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
    echo '<div class="flex flex-col md:flex-row min-h-screen">';
    
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
