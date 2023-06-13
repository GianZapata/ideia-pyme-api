<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>


# IDEIA PYME API

## Requisitos

Asegúrate de tener los siguientes requisitos antes de comenzar:

- PHP >= 8.1
- Composer
- Laravel >= 8.x
- Una base de datos compatible (por ejemplo, MySQL, PostgreSQL)


## Instalación

1. Clona el repositorio en tu máquina local:
```
git clone https://github.com/GianZapata/ideia-pyme-api.git
```

2. Navega hasta el directorio del proyecto:
```
cd ideia-pyme-api
```


3. Instala las dependencias del proyecto utilizando Composer:
```
composer install
```

4. Crea un archivo `.env` basado en el archivo `.env.example` y actualiza la configuración de la base de datos y otros ajustes según sea necesario.

5. Genera una clave de aplicación:
```
php artisan key:generate
```

6. Ejecuta las migraciones de la base de datos con el seeder incluido:
```
php artisan migrate:fresh --seed
```

7. Inicia el servidor de desarrollo de Laravel:
```
php artisan serve
```


El servidor de desarrollo se ejecutará en `http://localhost:8000`.

## Uso

En el .env modificar la variable `QUEUE_CONNECTION` a `database` para que los trabajos se manejen correctamente.
```
QUEUE_CONNECTION=database
```

Para procesar los archivos XML, se ha creado un comando personalizado que puede ser ejecutado desde la línea de comandos. El comando divide los archivos en chunks de 2500 y envía cada chunk como un trabajo a una cola llamada 'xml'.

Para ejecutar el comando, abre una terminal en el directorio del proyecto y ejecuta:
```
php artisan process:xml-files
```

El comando buscará los archivos XML en el directorio `public/xml` y los procesará en segundo plano.

Asegúrate de tener configurada una cola llamada 'xml' en tu archivo `config/queue.php` para que los trabajos se manejen correctamente.

Despues de ejecutar el comando, puedes verificar el estado de los trabajos en la cola ejecutando:

```
php artisan queue:work --queue=xml
```

Después que se insertaran todos los registros puedes ejecutar el seeder de clientes para crear las pymes en base de datos de los xml ya procesados:

```
php artisan db:seed --class=ClientSeeder
```

## Personalización

- Puedes ajustar el directorio de búsqueda de los archivos XML en el archivo `app/Console/Commands/ProcessXMLFiles.php`, modificando la variable `$directory` en el método `handle()`.
- Personaliza la lógica de procesamiento de los archivos XML en el archivo `app/Jobs/ProcessXMLJob.php` según tus necesidades.

