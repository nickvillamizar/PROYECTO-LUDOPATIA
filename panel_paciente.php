<?php
session_start();
include 'conexion.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['nombre_completo']) || !isset($_SESSION['id'])) {
    header("Location: login.php"); // Redirigir a login si no está autenticado
    exit();
}

$nombre_completo = $_SESSION['nombre_completo'];
$paciente_id = $_SESSION['id'];

// Guardar la entrada del diario si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['entrada_diario'])) {
    $contenido = $_POST['entrada_diario'];
    $fecha = date('Y-m-d'); // Fecha actual

    // Insertar la entrada del diario en la base de datos
    $stmt = $conn->prepare("INSERT INTO diarios (paciente_id, fecha, contenido) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $paciente_id, $fecha, $contenido);

    if ($stmt->execute()) {
        // Redirigir para evitar reenvío del formulario
        header("Location: panel_paciente.php"); 
        exit();
    } else {
        $mensaje = "Error al guardar la entrada del diario.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Paciente</title>
    <link rel="stylesheet" type="text/css" href="panel_paciente.css">
</head>
<body>
    <video id="background-video" autoplay loop muted>
        <source src="videos/pasto.mp4" type="video/mp4">
        Tu navegador no soporta video.
    </video>

    <h1>Bienvenido, <?php echo htmlspecialchars($nombre_completo); ?>!</h1>
    <p>Este es su panel de apoyo para la ludopatía. Aquí encontrará recursos y herramientas para ayudarle en su proceso.</p>

    <h2>Diario Personal</h2>
    <p>Escriba lo que está sintiendo hoy o cualquier cosa que desee registrar en su diario.</p>

    <form action="panel_paciente.php" method="POST">
        <textarea name="entrada_diario" rows="5" cols="50" placeholder="Escriba su entrada del diario aquí..." required></textarea><br><br>
        <input type="submit" value="Guardar Entrada">
    </form>

    <?php
    // Mostrar mensaje si existe
    if (isset($mensaje)) {
        echo "<p style='color:red;'>$mensaje</p>";
    }
    ?>

    <h3>Entradas de Diario Anteriores</h3>
    <ul>
        <?php
        // Obtener y mostrar las entradas de diario del paciente
        $stmt = $conn->prepare("SELECT fecha, contenido FROM diarios WHERE paciente_id = ? ORDER BY fecha DESC");
        $stmt->bind_param("i", $paciente_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            echo "<li><strong>" . $row['fecha'] . ":</strong> " . htmlspecialchars($row['contenido']) . "</li>";
        }

        $stmt->close();
        ?>
    </ul>

    <a href="logout.php">Cerrar sesión</a>
</body>
</html>
