# 🎬 Análisis de puntuaciones cinematográficas en multiplataformas

## 📌 Descripción del proyecto

Este proyecto tiene como objetivo crear un dataset comparativo de puntuaciones cinematográficas entre dos de las plataformas más importantes de valoración de películas: **FilmAffinity** e **IMDb**. Mediante técnicas avanzadas de **web scraping**, se recopilan datos detallados de películas, incluyendo información técnica, metadatos y valoraciones de usuarios en ambas plataformas.

### 🔄 Flujo de trabajo

1. **Extracción de datos desde FilmAffinity**  
   Obtención de información completa de películas, incluyendo:
   - Título
   - Año
   - Duración
   - País
   - Director
   - Reparto
   - Género
   - Sinopsis
   - URL de imagen
   - Puntuación
   - Número de votos

2. **Enriquecimiento del dataset con valoraciones de IMDb**  
   A partir de búsquedas automatizadas en IMDb, se complementa el dataset con:
   - Puntuaciones
   - Datos adicionales relevantes

## ⚙️ Características configurables

- **Rango de años:** Selección flexible entre periodos de 5 o 10 años (desde 1980).
- **Volumen de datos:** Hasta 30 películas por año.
- **Modo de ejecución:** 
  - Modo prueba (1 película por año)
  - Modo completo

Estas opciones permiten generar datasets adaptados a distintas necesidades: desde muestras pequeñas hasta conjuntos de 300 películas por década.

## 📊 Salida de datos

Los resultados se exportan en dos formatos:

- `.xlsx` (Excel): Para análisis detallado y visualizaciones.
- `.csv`: Para interoperabilidad con otras herramientas.

## 🌐 Interfaz web

El proyecto incluye una interfaz web interactiva y responsiva que:

- Detecta automáticamente los archivos CSV generados en el servidor.
- Permite seleccionar rangos de años y filtrar por año específico.
- Presenta visualmente:
  - Carteles de películas
  - Fichas técnicas completas
  - Comparativas de puntuaciones entre FilmAffinity e IMDb
  - Clasificación por países (con banderas)

## 👩‍💻 Integrantes del grupo

- Gabriela Alejandra Pérez  
- María José de León Díaz

## 🔍 Paso 1: Extracción de datos de FilmAffinity

Ejecuta el notebook `paso01_filmaffinity_elige_rango.ipynb` y sigue las instrucciones interactivas para configurar:

- Tipo de extracción (prueba o completa)
- Rango de años (5 o 10)
- Año de inicio (desde 1980)
- Número de películas por año

Se generará un archivo `.xlsx` con los datos.


## 🔗 Paso 2: Enriquecimiento con datos de IMDb

Ejecuta el notebook `paso02_imdb_con_rango.ipynb`:

- Selecciona el archivo Excel generado en el paso anterior.
- El script realizará búsquedas automáticas en IMDb.
- Se generará un nuevo archivo `.xlsx` y `.csv` con los datos enriquecidos.


## 🌐 Paso 3: Visualización web

Para visualizar los datos mediante la interfaz web:

1. Coloca el archivo `index.php` y los archivos `.csv` generados en un servidor PHP.
2. Accede a la URL correspondiente para explorar los datos.


## 📊 Visualización online

Puedes ver un ejemplo en:  
[http://gperezcaviglia.com/pec2/index.php](http://gperezcaviglia.com/pec2/index.php)


## 📄 DOI

El dataset completo está disponible con el siguiente DOI:  
**(https://doi.org/10.5281/zenodo.15170924)**


## 📝 Licencia

Este proyecto está licenciado bajo **Creative Commons BY-NC-SA 4.0**.  
Consulta más en:  
[https://creativecommons.org/licenses/by-nc-sa/4.0/](https://creativecommons.org/licenses/by-nc-sa/4.0/)


