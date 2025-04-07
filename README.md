#Análisis de Puntuaciones Cinematográficas en Multiplataformas

##Descripción del proyecto
Este proyecto tiene como objetivo la creación de un conjunto de datos comparativo de evaluaciones cinematográficas entre dos plataformas especializadas.
como son**FilmAffinity**mi**IMDb**. A través de técnicas de web scraping, se recopilan datos detallados de películas, incluyendo información
técnica, metadatos y valoraciones de usuarios en ambas plataformas.
El flujo de trabajo se divide en dos fases:
1. **Extracción de datos desde FilmAffinity**, incluyendo evaluación y metadatos por película.
2. **Enriquecimiento del conjunto de datos con valoraciones obtenidas desde IMDb**mediante búsquedas automáticas.

###Características configurables

-Rango de años: 5 o 10 años (desde 1980 en adelante).
-Número de películas por año: hasta un máximo de 30.

Esto permite generar conjuntos de datos flexibles, desde muestras pequeñas hasta conjuntos más amplios de hasta 300 películas.

###Salida de datos

Los resultados se exportan en formatos:

- **Excel (.xlsx)**
- **CSV (.csv)**

###Interfaz web

El proyecto incluye una**Interfaz web interactiva**que:

-Detecta automáticamente los archivos CSV disponibles en el servidor.
-Permite seleccionar un rango de años y filtrar películas por año específico.
-Muestra la información de manera visual y organizada, incluyendo:
  -Imágenes
  -Datos técnicos
  -Comparativa de evaluación entre plataformas

Hace 5 días

Actualizar README.md

##Integrantes del grupo
Hace 2 días

README.md
-Gabriela Alejandra Pérez Caviglia
-María José de León Díaz
Hace 5 días

Actualizar README.md

##Estructura del repositorio

Este repositorio contiene los siguientes archivos y carpetas:

- `script.py`: Guión principal del proyecto.
- `datos/`: Carpeta que contiene los datos de entrada utilizados por el script.
- `producción/`: Carpeta donde se guardan los resultados generados.
- `README.md`: Este documento con información general del repositorio.

##Cómo usar el código

Para ejecutar el script principal, asegúrese de tener Python instalado. Luego, puedes ejecutar el siguiente comando desde la terminal:

```intento
script de python.py --input datos/dataset.csv --output salida/resultado.txt
Hace 2 días

README.md
```

