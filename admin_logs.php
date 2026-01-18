<?php
// admin_logs.php
session_start();
require_once __DIR__ . '/config/database.php';

// Verificar rol admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'administrador') {
    header('Location: auth/login.php');
    exit;
}

$conn = getConnection();
if (!$conn) { die('Error de conexión'); }

// Filtros
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$filterTipo = trim($_GET['tipo'] ?? '');
$filterUsuario = trim($_GET['usuario_id'] ?? '');
$filterSearch = trim($_GET['q'] ?? '');
$filterDesde = trim($_GET['desde'] ?? '');
$filterHasta = trim($_GET['hasta'] ?? '');

// Construir WHERE dinámico
$where = [];
$params = [];
$types = '';

if ($filterTipo !== '') { $where[] = 'tipo_usuario = ?'; $params[] = $filterTipo; $types .= 's'; }
if ($filterUsuario !== '') { $where[] = 'usuario_id = ?'; $params[] = intval($filterUsuario); $types .= 'i'; }
if ($filterDesde !== '') { $where[] = 'fecha >= ?'; $params[] = $filterDesde . ' 00:00:00'; $types .= 's'; }
if ($filterHasta !== '') { $where[] = 'fecha <= ?'; $params[] = $filterHasta . ' 23:59:59'; $types .= 's'; }
if ($filterSearch !== '') { $where[] = 'accion LIKE ?'; $params[] = '%' . $filterSearch . '%'; $types .= 's'; }

$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Total
$countSql = "SELECT COUNT(*) AS c FROM logs_actividad $where_sql";
$stmtCount = $conn->prepare($countSql);
if ($stmtCount) {
    if ($types) $stmtCount->bind_param($types, ...$params);
    $stmtCount->execute();
    $resCount = $stmtCount->get_result();
    $total = ($resCount->fetch_assoc()['c']) ?? 0;
    $stmtCount->close();
} else {
    $total = 0;
}

// Query principal
$sql = "SELECT l.*, u.usuario AS usuario_nombre
        FROM logs_actividad l
        LEFT JOIN usuarios_sistema u ON u.id = l.usuario_id
        $where_sql
        ORDER BY fecha DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types) {
        $bindParams = array_merge($params, [$perPage, $offset]);
        $bindTypes = $types . 'ii';
        $stmt->bind_param($bindTypes, ...$bindParams);
    } else {
        $stmt->bind_param('ii', $perPage, $offset);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die('Error al preparar la consulta.');
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Logs de actividad</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Asegúrate que esta ruta coincide con tu estructura -->
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <?php include 'includes/sidebar.php'; ?>

  <div class="ml-64">
    <?php include 'includes/header.php'; ?>

    <main class="p-6 pt-24">
      <h1 class="text-2xl font-bold mb-4">Registro de actividad</h1>

      <form method="get" class="mb-4 flex flex-wrap gap-2 items-end">
        <div>
          <label>Buscar</label><br>
          <input type="text" name="q" value="<?php echo htmlspecialchars($filterSearch); ?>" class="px-2 py-1 border rounded">
        </div>
        <div>
          <label>Tipo</label><br>
          <select name="tipo" class="px-2 py-1 border rounded">
            <option value="">Todos</option>
            <option value="asesor" <?php if($filterTipo==='asesor') echo 'selected'; ?>>Asesores</option>
            <option value="delegado" <?php if($filterTipo==='delegado') echo 'selected'; ?>>Delegados</option>
            <option value="usuario" <?php if($filterTipo==='usuario') echo 'selected'; ?>>Usuarios</option>
          </select>
        </div>
        <div>
          <label>Usuario ID</label><br>
          <input type="text" name="usuario_id" value="<?php echo htmlspecialchars($filterUsuario); ?>" class="px-2 py-1 border rounded">
        </div>
        <div>
          <label>Desde</label><br>
          <input type="date" name="desde" value="<?php echo htmlspecialchars($filterDesde); ?>" class="px-2 py-1 border rounded">
        </div>
        <div>
          <label>Hasta</label><br>
          <input type="date" name="hasta" value="<?php echo htmlspecialchars($filterHasta); ?>" class="px-2 py-1 border rounded">
        </div>
        <div>
          <button class="bg-navy text-white px-3 py-1 rounded">Filtrar</button>
        </div>
      </form>

      <div class="overflow-x-auto bg-white rounded shadow">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-2 text-left">ID</th>
              <th class="p-2 text-left">Fecha</th>
              <th class="p-2 text-left">Usuario</th>
              <th class="p-2 text-left">Tipo</th>
              <th class="p-2 text-left">IP</th>
              <th class="p-2 text-left">Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td class="p-2"><?php echo intval($row['id']); ?></td>
              <td class="p-2"><?php echo htmlspecialchars($row['fecha']); ?></td>
              <td class="p-2"><?php echo htmlspecialchars($row['usuario_nombre'] ?? ('ID '.$row['usuario_id'])); ?></td>
              <td class="p-2"><?php echo htmlspecialchars($row['tipo_usuario']); ?></td>
              <td class="p-2"><?php echo htmlspecialchars($row['ip']); ?></td>
              <td class="p-2"><?php echo htmlspecialchars($row['accion']); ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <?php
        $pages = max(1, ceil($total / $perPage));
        if ($pages > 1) {
            echo '<div class="mt-4">';
            for ($p=1;$p<=$pages;$p++) {
                $q = $_GET; $q['page'] = $p;
                $url = $_SERVER['PHP_SELF'] . '?' . http_build_query($q);
                echo '<a href="'.htmlspecialchars($url).'" class="px-2 py-1 mr-1 '.($p==$page?'bg-navy text-white rounded':'border rounded').'">'.$p.'</a>';
            }
            echo '</div>';
        }
      ?>

    </main>

    <?php include 'includes/footer.php'; ?>
  </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>