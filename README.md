# Análisis de Puntuaciones Cinematográficas en Multiplataformas

## Descripción del proyecto
Este proyecto tiene como objetivo la creación de un dataset comparativo de puntuaciones cinematográficas entre dos plataformas especializadas
como son **FilmAffinity** e **IMDb**. A través de técnicas de web scraping, se recopilan datos detallados de películas, incluyendo información
técnica, metadatos y valoraciones de usuarios en ambas plataformas.
El flujo de trabajo se divide en dos fases:
1. **Extracción de datos desde FilmAffinity**, incluyendo puntuaciones y metadatos por película.
2. **Enriquecimiento del dataset con valoraciones obtenidas desde IMDb** mediante búsquedas automatizadas.

### Características configurables

- Rango de años: 5 o 10 años (desde 1980 en adelante).
- Número de películas por año: hasta un máximo de 30.

Esto permite generar datasets flexibles, desde muestras pequeñas hasta conjuntos más amplios de hasta 300 películas.

### Salida de datos

Los resultados se exportan en formatos:

- **Excel (.xlsx)**
- **CSV (.csv)**

### Interfaz web

El proyecto incluye una **interfaz web interactiva** que:

- Detecta automáticamente los archivos CSV disponibles en el servidor.
- Permite seleccionar un rango de años y filtrar películas por año específico.
- Muestra la información de manera visual y organizada, incluyendo:
  - Imágenes
  - Datos técnicos
  - Comparativa de puntuaciones entre plataformas


## Integrantes del grupo
- Gabriela Alejandra Pérez
- María José de León Díaz

## Estructura del repositorio

Este repositorio contiene los siguientes archivos y carpetas:
- `README.md`: Este documento con información general del repositorio.
- `requirements.txt`: con las librerías necesarias para ejecutar el código
- `source/`: Carpeta que contiene los ficheros con el código del proyecto.
- `dataset/`: Carpeta donde se guarda el fichero final obtenido.


## Cómo usar el código

Para ejecutar el script principal, asegúrate de tener Python instalado. Luego, puedes ejecutar el siguiente comando desde la terminal:

```bash
python script.py --input datos/dataset.csv --output output/resultado.txt
```

## DOI
