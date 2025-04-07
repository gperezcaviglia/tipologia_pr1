<?php
// ============================
// Buscar archivos CSV disponibles
// ============================

// Directorio donde se almacenan los archivos CSV
$directorio = './';  // Ajustar según donde estén los archivos

// Patrón para buscar archivos CSV con rangos de años
$patron = '/peliculas_filmaffinity_.*?(\d{4})-(\d{4}).*?\.csv/';
$archivos_csv = [];

// Escanear el directorio en busca de archivos CSV
foreach (glob($directorio . "*.csv") as $archivo) {
    $nombre_archivo = basename($archivo);
    
    // Extraer el rango de años del nombre del archivo si existe
    if (preg_match($patron, $nombre_archivo, $coincidencias)) {
        $anio_inicio = (int)$coincidencias[1];
        $anio_fin = (int)$coincidencias[2];
        $rango = "$anio_inicio-$anio_fin";
        
        $archivos_csv[$rango] = [
            'ruta' => $archivo,
            'nombre' => $nombre_archivo,
            'anio_inicio' => $anio_inicio,
            'anio_fin' => $anio_fin
        ];
    }
}

// Ordenar por año de inicio (de más reciente a más antiguo)
krsort($archivos_csv);

// Determinar qué archivo CSV usar (desde la URL o el primero disponible)
$rango_seleccionado = $_GET['rango'] ?? key($archivos_csv);
$nombre_archivo = $archivos_csv[$rango_seleccionado]['ruta'] ?? reset($archivos_csv)['ruta'];

// ============================
// Cargar y procesar el archivo CSV
// ============================

// Arreglo donde se guardarán las películas ya limpias
$peliculas = [];

// Abrimos el archivo CSV y procesamos línea por línea
if (($manejador = fopen($nombre_archivo, 'r')) !== false) {
    // Leemos los encabezados y los limpiamos (quitamos espacios y caracteres invisibles como BOM)
    $encabezados = array_map(function($encabezado) {
        return strtolower(trim(preg_replace('/\x{FEFF}/u', '', $encabezado)));
    }, fgetcsv($manejador, 0, ';'));

    // Leemos cada línea del archivo CSV
    while (($datos = fgetcsv($manejador, 0, ';')) !== false) {
        // Solo procesamos líneas con la misma cantidad de datos que de encabezados
        if (count($datos) === count($encabezados)) {
            $peliculas[] = array_combine($encabezados, $datos);
        }
    }
    fclose($manejador);
}

// ============================
// Procesamiento de los años disponibles
// ============================

// Extraemos todos los años únicos del conjunto de películas
$anios_disponibles = array_unique(array_column($peliculas, 'anio'));
sort($anios_disponibles); // Ordenamos los años

// Determinamos qué año se está mostrando (desde la URL o el primero por defecto)
$anio_seleccionado = $_GET['anio'] ?? $anios_disponibles[0];

// ============================
// Diccionario de banderas por país
// ============================

$codigo_paises = [
    'Japón' => 'jp', 'Irlanda' => 'ie', 'Estados Unidos' => 'us', 'Reino Unido' => 'gb',
    'Bosnia y Herzegovina' => 'ba', 'Corea del Sur' => 'kr', 'Francia' => 'fr', 'España' => 'es',
    'Italia' => 'it', 'Alemania' => 'de', 'Argentina' => 'ar', 'Irán' => 'ir', 'Bélgica' => 'be',
    'Canadá' => 'ca', 'China' => 'cn', 'India' => 'in', 'Suecia' => 'se', 'México' => 'mx',
    'Dinamarca' => 'dk', 'Austria' => 'at', 'Israel' => 'il', 'Hong Kong' => 'hk', 'Turquía' => 'tr',
    'Polonia' => 'pl', 'Australia' => 'au', 'Tailandia' => 'th', 'Letonia' => 'lv', 'Brasil' => 'br'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Películas por Año y Rango</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Estilos con Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-img-top { object-fit: cover; height: 300px; }
        .card-text-small { font-size: 0.9em; }
        .bandera {
            width: 24px;
            height: 16px;
            object-fit: cover;
            margin-left: 5px;
            vertical-align: text-bottom;
            border: 0.5px solid #999;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4 text-center">Películas por Año y Rango</h1>

    <!-- Formulario de selección de rango de años -->
    <?php if (count($archivos_csv) > 0): ?>
    <form method="get" class="mb-4 row">
        <div class="col-md-6 mb-3">
            <label for="rango" class="form-label">Selecciona un rango de años:</label>
            <select id="rango" name="rango" class="form-select" onchange="this.form.submit()">
                <?php foreach ($archivos_csv as $rango => $archivo): ?>
                    <option value="<?= $rango ?>" <?= $rango == $rango_seleccionado ? 'selected' : '' ?>>
                        <?= $rango ?> (<?= $archivo['nombre'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6 mb-3">
            <label for="anio" class="form-label">Selecciona un año específico:</label>
            <select id="anio" name="anio" class="form-select" onchange="this.form.submit()">
                <option value="Elegir" selected>Elija el año</option>
                <?php foreach ($anios_disponibles as $anio): ?>
                    <option value="<?= $anio ?>" <?= $anio == $anio_seleccionado ? 'selected' : '' ?>><?= $anio ?></option>
                    <!--<option value="<?= $anio ?>" <?= $anio == $anio_seleccionado ? 'selected' : '' ?>><?= $anio ?></option>-->
                <?php endforeach; ?>
            </select>
        </div>
    </form>
    <?php else: ?>
    <div class="alert alert-warning">No se encontraron archivos CSV con rangos de años válidos.</div>
    <?php endif; ?>

    <!-- Mostrar rango actual -->
    <div class="alert alert-info mb-4">
        <h4 class="alert-heading">Rango de años: <?= $rango_seleccionado ?></h4>
        <p>Mostrando películas del año: <?= $anio_seleccionado ?></p>
        <p class="mb-0">Archivo cargado: <?= basename($nombre_archivo) ?></p>
    </div>

    <!-- Tarjetas de películas -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($peliculas as $pelicula): ?>
            <?php if ($pelicula['anio'] == $anio_seleccionado): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= $pelicula['url_imagen'] ?>" class="card-img-top" alt="Imagen de la película">
                        <div class="card-body">
                            <h5 class="card-title mb-2 text-primary fw-bold">
                                <?= !empty($pelicula['titulo_original']) ? $pelicula['titulo_original'] : '<em>Sin título</em>' ?>
                            </h5>

                            <!-- Detalles básicos -->
                            <p class="card-text-small text-muted mb-1">
                                <i class="bi bi-clock"></i> <?= $pelicula['duracion'] ?><br>
                                <i class="bi bi-geo-alt"></i> <?= $pelicula['pais'] ?>
                                <?php if (isset($codigo_paises[$pelicula['pais']])): ?>
                                    <img src="https://flagcdn.com/<?= $codigo_paises[$pelicula['pais']] ?>.svg" class="bandera" alt="bandera">
                                <?php endif; ?>
                            </p>

                            <!-- Género, dirección, reparto -->
                            <p class="card-text-small text-muted mb-1">
                                <i class="bi bi-film"></i> <strong>Género:</strong> <?= $pelicula['genero'] ?><br>
                                <i class="bi bi-person-video"></i> <strong>Dirección:</strong> <?= $pelicula['direccion'] ?><br>
                                <i class="bi bi-people"></i> <strong>Reparto:</strong> 
                                <?php 
                                    $reparto_array = explode(',', $pelicula['reparto']);
                                    echo count($reparto_array) > 3 
                                        ? implode(', ', array_slice($reparto_array, 0, 3)) . '...' 
                                        : $pelicula['reparto'];
                                ?>
                            </p>

                            <!-- Sinopsis breve -->
                            <p class="card-text card-text-small mb-2">
                                <?= substr($pelicula['sinopsis'], 0, 160) ?>...
                            </p>

                            <!-- Puntajes con votos, en líneas separadas -->
                            <p class="mb-2">
                                <i class="bi bi-star-fill text-warning"></i>
                                <strong>Filmaffinity:</strong> <?= $pelicula['puntuacion'] ?>
                                (<?= number_format((int)$pelicula['votos'], 0, ',', '.') ?> votos)<br>
                                <?php if (!empty($pelicula['imdb_puntuacion'])): ?>
                                <i class="bi bi-star-fill text-warning"></i>
                                <strong>IMDb:</strong> <?= $pelicula['imdb_puntuacion'] ?>
                                (<?= number_format((int)($pelicula['imdb_votos'] ?? 0), 0, ',', '.') ?> votos)
                                <?php endif; ?>
                            </p>

                            <!-- Enlaces externos -->
                            <a href="<?= $pelicula['url'] ?>" class="btn btn-primary btn-sm" target="_blank">Filmaffinity</a>
                            <?php if (!empty($pelicula['imdb_url'])): ?>
                            <a href="<?= $pelicula['imdb_url'] ?>" class="btn btn-dark btn-sm" target="_blank">IMDb</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Mensaje si no hay películas para el año seleccionado -->
    <?php 
    $peliculas_del_anio = array_filter($peliculas, function($p) use ($anio_seleccionado) {
        return $p['anio'] == $anio_seleccionado;
    });
    
    if (empty($peliculas_del_anio)): 
    ?>
    <div class="alert alert-warning mt-4">
        No se ha seleccionado un año o no se encontraron películas para la selección realizada <?= $anio_seleccionado ?>.
    </div>
    <?php endif; ?>
</div>

<!-- Scripts Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
