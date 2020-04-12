#SpotiSeedem
Se requiere diseñar un módulo que cumpla con las siguientes características:
- Consumo de la API de Spotify para obtener información de:
  - Artistas
  - Albumes

### Requisitos
Este módulo esta diseñado para ser desplegado con Docker, 
por lo cual se deja la guía para su instalación:
- [Docker install](https://docs.docker.com/get-docker/)
- [Docker Compose install](https://docs.docker.com/compose/install/)

##Deploy
- Creación del contenedor, el cual esta configurado para 
correr en el puerto **8001**  
> `docker-compose up -d --build`  
- Instalación de dependencias  
> `composer install`  

- Configurar el archivo de variables de entorno
> Se debe copiar la configuración del archivo llamado **.env.example** 
en el archivo **.env**, si no existe, hay que crearlo  

- Ya esta todo listo, el módulo se encuentra disponible en 
 la siguiente ruta: [SpotiSeedem](http://localhost:8001)