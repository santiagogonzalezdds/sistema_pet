<?php
$adminPassword = 'Salvador';
$empleadoPassword = 'Empleado';

// Cifrar las contraseñas
$adminHashed = password_hash($adminPassword, PASSWORD_BCRYPT);
$empleadoHashed = password_hash($empleadoPassword, PASSWORD_BCRYPT);

// Mostrar contraseñas cifradas
echo "Admin: $adminHashed\n";
echo "Empleado: $empleadoHashed\n";
?>
