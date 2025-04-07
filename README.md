# 🎬 Análisis de puntuaciones cinematográficas en multiplataformas

## 📌 Descripción del proyecto

Este proyecto tiene como objetivo crear un dataset comparativo de puntuaciones cinematográficas entre dos de las plataformas más importantes de valoración de películas: **FilmAffinity** e **IMDb**. Mediante técnicas avanzadas de **web scraping**, se recopilan datos detallados de películas, incluyendo información técnica, metadatos y valoraciones de usuarios en ambas plataformas.

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

### 📊 Salida de datos

Los resultados se exportan en dos formatos:

- `.xlsx` (Excel): Para análisis detallado y visualizaciones.
- `.csv`: Para interoperabilidad con otras herramientas.

## 🌐 Paso 3: Visualización web

Para visualizar los datos mediante la interfaz web:

1. Coloca el archivo `index.php` y los archivos `.csv` generados en un servidor PHP.
2. Accede a la URL correspondiente para explorar los datos.


### 📊 Visualización online

Puedes ver un ejemplo en:  
[http://gperezcaviglia.com/pec2/index.php](http://gperezcaviglia.com/pec2/index.php)


## 📄 DOI

El dataset completo está disponible con el siguiente DOI:  
**(https://doi.org/10.5281/zenodo.15170924)**


## 📝 Licencia

Este proyecto está licenciado bajo **Creative Commons BY-NC-SA 4.0**.  
Consulta más en:  
[https://creativecommons.org/licenses/by-nc-sa/4.0/](https://creativecommons.org/licenses/by-nc-sa/4.0/)
