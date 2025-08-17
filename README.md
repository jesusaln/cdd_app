Tips para commits más rápidos

cuando haga una actualizacion corro este en el vps

cd /srv/laravel-app/src
./update-from-github.sh

No subas node_modules ni vendor, deja que otros reconstruyan dependencias con:

composer install
npm install

Usa git add . solo después de limpiar archivos temporales o ignorados.

Para cambios frecuentes en frontend (Vite), solo sube el código fuente (resources/js, resources/css, vite.config.js) y deja que la compilación se haga en cada entorno.

# cdd_app

Aplicación desarrollada con Vue.js para la gestión y administración de información relacionada con CDD.

## Características principales

-   Interfaz moderna y responsiva con Vue.js.
-   Estructura modular para facilitar el mantenimiento y la escalabilidad.
-   Integración con servicios backend.
-   Gestión de usuarios y permisos.
-   Registro y consulta de datos relevantes.
-   Soporte para múltiples roles y funcionalidades extendidas.

## Instalación

1. Clona el repositorio:
    ```bash
    git clone https://github.com/jesusaln/cdd_app.git
    ```
2. Ingresa al directorio del proyecto:
    ```bash
    cd cdd_app
    ```
3. Instala las dependencias:
    ```bash
    npm install
    ```
4. Inicia la aplicación en modo desarrollo:
    ```bash
    npm run serve
    ```

## Uso

-   Accede a `http://localhost:8080` en tu navegador.
-   Explora las secciones y funcionalidades disponibles según tu rol.

## Estructura del proyecto

```
cdd_app/
├── src/
│   ├── assets/
│   ├── components/
│   ├── views/
│   ├── router/
│   ├── store/
│   └── App.vue
├── public/
├── package.json
└── README.md
```

## Contribución

1. Haz un fork del repositorio.
2. Crea una nueva rama con tu mejora: `git checkout -b mejora/feature-nueva`.
3. Realiza el commit de tus cambios: `git commit -m 'Agrega nueva funcionalidad'`.
4. Haz push a la rama: `git push origin mejora/feature-nueva`.
5. Abre un Pull Request.

## Licencia

Consulta el archivo LICENSE para detalles sobre la licencia del proyecto.

---

# cdd_app

Application developed with Vue.js for management and administration of information related to CDD.

## Main features

-   Modern, responsive interface built with Vue.js.
-   Modular structure for easy maintenance and scalability.
-   Integration with backend services.
-   User and permission management.
-   Registration and consultation of relevant data.
-   Support for multiple roles and extended functionalities.

## Installation

1. Clone the repository:
    ```bash
    git clone https://github.com/jesusaln/cdd_app.git
    ```
2. Enter the project directory:
    ```bash
    cd cdd_app
    ```
3. Install dependencies:
    ```bash
    npm install
    ```
4. Start the application in development mode:
    ```bash
    npm run serve
    ```

## Usage

-   Go to `http://localhost:8080` in your browser.
-   Explore the available sections and features according to your user role.

## Project Structure

```
cdd_app/
├── src/
│   ├── assets/
│   ├── components/
│   ├── views/
│   ├── router/
│   ├── store/
│   └── App.vue
├── public/
├── package.json
└── README.md
```

## Contribution

1. Fork the repository.
2. Create a new branch for your improvement: `git checkout -b feature/new-feature`.
3. Commit your changes: `git commit -m 'Add new functionality'`.
4. Push the branch: `git push origin feature/new-feature`.
5. Open a Pull Request.

## License

See the LICENSE file for project license details.
