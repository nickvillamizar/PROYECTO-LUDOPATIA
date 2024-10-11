<?php
session_start();
include 'conexion.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['nombre_completo'])) {
    header("Location: login.php"); // Redirigir a login si no está autenticado
    exit();
}

$nombre_completo = $_SESSION['nombre_completo'];

// Verificar si se ha seleccionado un paciente
$paciente_id = null;
$diarios = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['paciente_id'])) {
    $paciente_id = $_POST['paciente_id'];

    // Obtener las entradas de diario del paciente seleccionado
    $stmt = $conn->prepare("SELECT fecha, contenido FROM diarios WHERE paciente_id = ? ORDER BY fecha DESC");
    $stmt->bind_param("i", $paciente_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $diarios[] = $row; // Guardar las entradas en el arreglo $diarios
    }

    $stmt->close();
}

// Obtener la lista de pacientes para que el profesional pueda seleccionarlos
$pacientes = [];
$result = $conn->query("SELECT id, nombre_completo FROM pacientes");

while ($row = $result->fetch_assoc()) {
    $pacientes[] = $row; // Guardar los pacientes en el arreglo $pacientes
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Profesional</title>
    <link rel="stylesheet" type="text/css" href="panel_profesional.css">
</head>
<body>
    <video autoplay muted loop id="background-video">
        <source src="videos/pasto.mp4" type="video/mp4">
        Su navegador no soporta videos.
    </video>

    <div class="content">
        <h1>Bienvenido, <?php echo htmlspecialchars($nombre_completo); ?>!</h1>
        <p>Este es su panel de apoyo como profesional. Aquí podrá gestionar información y recursos para ayudar a sus pacientes en su proceso de ludopatía.</p>

        <h2>Seleccione un paciente para ver sus entradas de diario:</h2>
        <form method="POST" action="">
            <label for="paciente_id">Paciente:</label>
            <select name="paciente_id" id="paciente_id" required>
                <option value="">Seleccione un paciente</option>
                <?php foreach ($pacientes as $paciente): ?>
                    <option value="<?php echo $paciente['id']; ?>" <?php if ($paciente_id == $paciente['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($paciente['nombre_completo']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" value="Ver Diarios">
        </form>

        <?php if ($paciente_id): ?>
            <h3>Entradas de Diario del Paciente</h3>
            <?php if (count($diarios) > 0): ?>
                <ul>
                    <?php foreach ($diarios as $diario): ?>
                        <li><strong><?php echo $diario['fecha']; ?>:</strong> <?php echo htmlspecialchars($diario['contenido']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No hay entradas de diario para este paciente.</p>
            <?php endif; ?>
        <?php endif; ?>

        <a href="logout.php">Cerrar sesión</a>
    </div>
</body>
</html>

