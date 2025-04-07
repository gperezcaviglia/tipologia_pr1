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
// Obtener criterio de ordenamiento desde la URL
// ============================
$orden_por = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'puntuacion'; // Por defecto FilmAffinity
$orden_dir = isset($_GET['dir']) ? $_GET['dir'] : 'desc'; // Descendente por defecto

// Función de comparación para ordenar películas
function compararPeliculas($a, $b) {
    global $orden_por, $orden_dir;
    
    // Determinar qué columna usar para ordenar
    $columna = $orden_por;
    
    // Extraer valores a comparar (convertir a float para puntuaciones)
    $valorA = isset($a[$columna]) ? (is_numeric($a[$columna]) ? (float)$a[$columna] : $a[$columna]) : 0;
    $valorB = isset($b[$columna]) ? (is_numeric($b[$columna]) ? (float)$b[$columna] : $b[$columna]) : 0;
    
    // Determinar dirección de ordenamiento
    $multiplier = ($orden_dir === 'asc') ? 1 : -1;
    
    // Comparar valores
    if ($valorA == $valorB) {
        return 0;
    }
    return ($valorA < $valorB) ? (-1 * $multiplier) : (1 * $multiplier);
}

// Ordenar películas por el criterio seleccionado
usort($peliculas, 'compararPeliculas');

// ============================
// Procesamiento de los años disponibles
// ============================

// Extraemos todos los años únicos del conjunto de películas
$anios_disponibles = array_unique(array_column($peliculas, 'anio'));
sort($anios_disponibles); // Ordenamos los años

// Determinamos qué año se está mostrando (desde la URL o el primero por defecto)
$anio_seleccionado = $_GET['anio'] ?? $anios_disponibles[0];

// ============================
// Cálculo de estadísticas
// ============================

// Inicializar variables para estadísticas
$estadisticas = [
    'total_peliculas' => 0,
    'puntuacion_media_fa' => 0,
    'puntuacion_media_imdb' => 0,
    'mejor_pelicula_fa' => ['titulo' => '', 'puntuacion' => 0],
    'mejor_pelicula_imdb' => ['titulo' => '', 'puntuacion' => 0],
    'diferencia_max' => ['titulo' => '', 'diferencia' => 0],
    'pais_mas_comun' => '',
    'decada' => $rango_seleccionado
];

// Contador de países
$contador_paises = [];

// Calcular estadísticas
foreach ($peliculas as $pelicula) {
    // Solo contar películas del año seleccionado si hay uno seleccionado
    if ($anio_seleccionado !== 'Elegir' && $pelicula['anio'] != $anio_seleccionado) {
        continue;
    }
    
    $estadisticas['total_peliculas']++;
    
    // Acumular puntuaciones
    $estadisticas['puntuacion_media_fa'] += (float)$pelicula['puntuacion'];
    if (isset($pelicula['imdb_puntuacion']) && is_numeric($pelicula['imdb_puntuacion'])) {
        $estadisticas['puntuacion_media_imdb'] += (float)$pelicula['imdb_puntuacion'];
    }
    
    // Mejor película según FilmAffinity
    if ((float)$pelicula['puntuacion'] > $estadisticas['mejor_pelicula_fa']['puntuacion']) {
        $estadisticas['mejor_pelicula_fa'] = [
            'titulo' => $pelicula['titulo_original'],
            'puntuacion' => (float)$pelicula['puntuacion']
        ];
    }
    
    // Mejor película según IMDb
    if (isset($pelicula['imdb_puntuacion']) && is_numeric($pelicula['imdb_puntuacion']) && 
        (float)$pelicula['imdb_puntuacion'] > $estadisticas['mejor_pelicula_imdb']['puntuacion']) {
        $estadisticas['mejor_pelicula_imdb'] = [
            'titulo' => $pelicula['titulo_original'],
            'puntuacion' => (float)$pelicula['imdb_puntuacion']
        ];
    }
    
    // Mayor diferencia entre puntuaciones
    if (isset($pelicula['imdb_puntuacion']) && is_numeric($pelicula['imdb_puntuacion'])) {
        $diferencia = abs((float)$pelicula['puntuacion'] - (float)$pelicula['imdb_puntuacion']);
        if ($diferencia > $estadisticas['diferencia_max']['diferencia']) {
            $estadisticas['diferencia_max'] = [
                'titulo' => $pelicula['titulo_original'],
                'diferencia' => $diferencia,
                'fa' => (float)$pelicula['puntuacion'],
                'imdb' => (float)$pelicula['imdb_puntuacion']
            ];
        }
    }
    
    // Contabilizar países
    if (isset($pelicula['pais']) && !empty($pelicula['pais'])) {
        if (!isset($contador_paises[$pelicula['pais']])) {
            $contador_paises[$pelicula['pais']] = 0;
        }
        $contador_paises[$pelicula['pais']]++;
    }
}

// Calcular promedios
if ($estadisticas['total_peliculas'] > 0) {
    $estadisticas['puntuacion_media_fa'] /= $estadisticas['total_peliculas'];
    $estadisticas['puntuacion_media_imdb'] /= $estadisticas['total_peliculas'];
}

// Encontrar el país más común
if (!empty($contador_paises)) {
    arsort($contador_paises);
    $estadisticas['pais_mas_comun'] = array_key_first($contador_paises);
    $estadisticas['pais_mas_comun_cantidad'] = reset($contador_paises);
}

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
        .stats-card {
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .sort-icon {
            font-size: 0.7em;
            margin-left: 5px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4 text-center">Análisis de Puntuaciones Cinematográficas</h1>

    <!-- Formulario de selección de rango de años y ordenamiento -->
    <?php if (count($archivos_csv) > 0): ?>
    <form method="get" class="mb-4 row">
        <div class="col-md-4 mb-3">
            <label for="rango" class="form-label">Selecciona un rango de años:</label>
            <select id="rango" name="rango" class="form-select" onchange="this.form.submit()">
                <?php foreach ($archivos_csv as $rango => $archivo): ?>
                    <option value="<?= $rango ?>" <?= $rango == $rango_seleccionado ? 'selected' : '' ?>>
                        <?= $rango ?> (<?= $archivo['nombre'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4 mb-3">
            <label for="anio" class="form-label">Selecciona un año específico:</label>
            <select id="anio" name="anio" class="form-select" onchange="this.form.submit()">
                <option value="Elegir" <?= $anio_seleccionado == 'Elegir' ? 'selected' : '' ?>>Todos los años</option>
                <?php foreach ($anios_disponibles as $anio): ?>
                    <option value="<?= $anio ?>" <?= $anio == $anio_seleccionado ? 'selected' : '' ?>><?= $anio ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4 mb-3">
            <label for="ordenar" class="form-label">Ordenar por:</label>
            <div class="input-group">
                <select id="ordenar" name="ordenar" class="form-select">
                    <option value="puntuacion" <?= $orden_por == 'puntuacion' ? 'selected' : '' ?>>FilmAffinity</option>
                    <option value="imdb_puntuacion" <?= $orden_por == 'imdb_puntuacion' ? 'selected' : '' ?>>IMDb</option>
                    <option value="titulo_original" <?= $orden_por == 'titulo_original' ? 'selected' : '' ?>>Alfabético</option>
                </select>
                <select id="dir" name="dir" class="form-select">
                    <option value="desc" <?= $orden_dir == 'desc' ? 'selected' : '' ?>>Mayor a menor</option>
                    <option value="asc" <?= $orden_dir == 'asc' ? 'selected' : '' ?>>Menor a mayor</option>
                </select>
                <button type="submit" class="btn btn-primary">Ordenar</button>
            </div>
        </div>
    </form>
    <?php else: ?>
    <div class="alert alert-warning">No se encontraron archivos CSV con rangos de años válidos.</div>
    <?php endif; ?>

    <!-- Mostrar rango actual -->
    <div class="alert alert-info mb-4">
        <h4 class="alert-heading">Rango de años: <?= $rango_seleccionado ?></h4>
        <p>Mostrando películas <?= $anio_seleccionado != 'Elegir' ? "del año: $anio_seleccionado" : "de todos los años" ?></p>
        <p class="mb-0">Archivo cargado: <?= basename($nombre_archivo) ?></p>
    </div>

    <!-- Panel de Estadísticas -->
    <?php if ($estadisticas['total_peliculas'] > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">Estadísticas <?= $anio_seleccionado != 'Elegir' ? "del año $anio_seleccionado" : "de $rango_seleccionado" ?></h2>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card stats-card bg-primary text-white h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-film"></i> Total de Películas</h5>
                    <p class="card-text display-4"><?= $estadisticas['total_peliculas'] ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card stats-card bg-success text-white h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-star-fill"></i> Puntuación Media</h5>
                    <div class="row">
                        <div class="col-6 text-center border-end">
                            <p><strong>FilmAffinity</strong></p>
                            <p class="display-6"><?= number_format($estadisticas['puntuacion_media_fa'], 1) ?></p>
                        </div>
                        <div class="col-6 text-center">
                            <p><strong>IMDb</strong></p>
                            <p class="display-6"><?= number_format($estadisticas['puntuacion_media_imdb'], 1) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card stats-card bg-warning h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-geo-alt"></i> País con más películas</h5>
                    <p class="display-6">
                        <?= $estadisticas['pais_mas_comun'] ?>
                        <?php if (isset($codigo_paises[$estadisticas['pais_mas_comun']])): ?>
                            <img src="https://flagcdn.com/<?= $codigo_paises[$estadisticas['pais_mas_comun']] ?>.svg" class="bandera" alt="bandera" style="width: 40px; height: 25px;">
                        <?php endif; ?>
                    </p>
                    <p><?= $estadisticas['pais_mas_comun_cantidad'] ?> películas</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card stats-card bg-info h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-trophy"></i> Mejores películas</h5>
                    <div class="row">
                        <div class="col-6 border-end">
                            <p><strong>FilmAffinity</strong></p>
                            <p class="h6"><?= $estadisticas['mejor_pelicula_fa']['titulo'] ?></p>
                            <p>Puntuación: <?= $estadisticas['mejor_pelicula_fa']['puntuacion'] ?></p>
                        </div>
                        <div class="col-6">
                            <p><strong>IMDb</strong></p>
                            <p class="h6"><?= $estadisticas['mejor_pelicula_imdb']['titulo'] ?></p>
                            <p>Puntuación: <?= $estadisticas['mejor_pelicula_imdb']['puntuacion'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card stats-card bg-danger text-white h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-graph-up"></i> Mayor diferencia de puntuación</h5>
                    <p class="h5"><?= $estadisticas['diferencia_max']['titulo'] ?></p>
                    <div class="row">
                        <div class="col-4 text-center">
                            <p><strong>FilmAffinity</strong></p>
                            <p class="h4"><?= number_format($estadisticas['diferencia_max']['fa'], 1) ?></p>
                        </div>
                        <div class="col-4 text-center">
                            <p><strong>Diferencia</strong></p>
                            <p class="h4"><?= number_format($estadisticas['diferencia_max']['diferencia'], 1) ?></p>
                        </div>
                        <div class="col-4 text-center">
                            <p><strong>IMDb</strong></p>
                            <p class="h4"><?= number_format($estadisticas['diferencia_max']['imdb'], 1) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tarjetas de películas -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($peliculas as $pelicula): ?>
            <?php if ($anio_seleccionado == 'Elegir' || $pelicula['anio'] == $anio_seleccionado): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= $pelicula['url_imagen'] ?>" class="card-img-top" alt="Imagen de la película">
                        <div class="card-body">
                            <h5 class="card-title mb-2 text-primary fw-bold">
                                <?= !empty($pelicula['titulo_original']) ? $pelicula['titulo_original'] : '<em>Sin título</em>' ?> 
                                <span class="text-muted">(<?= $pelicula['anio'] ?>)</span>
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
        return $anio_seleccionado == 'Elegir' || $p['anio'] == $anio_seleccionado;
    });
    
    if (empty($peliculas_del_anio)): 
    ?>
    <div class="alert alert-warning mt-4">
        No se ha seleccionado un año o no se encontraron películas para la selección realizada <?= $anio_seleccionado ?>.
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="mt-5 pt-4 border-top text-center text-muted">
        <p>Análisis de Puntuaciones Cinematográficas en Multiplataformas</p>
        <p class="small">Práctica de Web Scraping - Tipología y ciclo de vida de los datos</p>
        <p class="small">Gabriela Alejandra Pérez - María José de León Díaz</p>
    </footer>
</div>

<!-- Scripts Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
