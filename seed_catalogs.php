<?php
require_once __DIR__ . '/recursos/Database.php';

$pdo = Database::getConnection();

try {
    $pdo->beginTransaction();

    // 1. Seed Rubros
    $rubros = [
        ['PAPELERIA Y UTILES DE ESCRITORIO', '512001'],
        ['ASEO Y CAFETERIA', '513502'],
        ['MANTENIMIENTO Y REPARACION', '513501'],
        ['VIATICOS Y GASTOS DE VIAJE', '514502'],
        ['ACTIVIDADES CULTURALES Y BIENESTAR', '519514'],
        ['COMPRA DE EQUIPOS', '601001']
    ];

    foreach ($rubros as $r) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO rubros (nombre, codigo) VALUES (?, ?)");
        $stmt->execute($r);
    }

    // 2. Seed Funciones
    $funciones = [
        ['DOCENCIA', '100'],
        ['INVESTIGACION', '200'],
        ['PROYECCION SOCIAL', '300'],
        ['BIENESTAR INSTITUCIONAL', '400'],
        ['GESTION ADMINISTRATIVA', '500'],
        ['MANTENIMIENTO INFRAESTRUCTURA', '600']
    ];

    foreach ($funciones as $f) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO funcion (nombre, codigo) VALUES (?, ?)");
        $stmt->execute($f);
    }

    $pdo->commit();
    echo "¡Catalogos iniciales creados con éxito!";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error al sembrar catalogos: " . $e->getMessage();
}
