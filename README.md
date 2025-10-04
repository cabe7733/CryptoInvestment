# 🚀 CryptoInvestment - Rastreador de Criptomonedas

Aplicación web de página única (SPA) para el seguimiento en tiempo real de criptomonedas utilizando Laravel y Vue.js.

## 📋 Características

- ✅ Seguimiento en tiempo real de criptomonedas
- ✅ Búsqueda y selección de criptomonedas
- ✅ Visualización de precios actualizados automáticamente
- ✅ Gráficos interactivos de historial de precios
- ✅ Cambios porcentuales (1h, 24h, 7d)
- ✅ Market Cap y volumen de trading
- ✅ Persistencia de datos históricos
- ✅ Rangos de tiempo personalizables
- ✅ Diseño responsivo para todos los dispositivos
- ✅ SPA sin recargas de página

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP 8.1+ / Laravel 10+
- **Frontend:** Vue.js 3 (Composition API)
- **Gráficos:** Chart.js 4
- **API:** CoinMarketCap API
- **Base de Datos:** MySQL 8.0+
- **Build Tool:** Vite

## 📦 Requisitos Previos

- PHP >= 8.1
- Composer
- Node.js >= 18.x
- NPM >= 9.x
- MySQL >= 8.0
- Cuenta en CoinMarketCap (API Key gratuita)

## 🔧 Instalación

### 1. Clonar el Repositorio

```bash
git clone https://github.com/tu-usuario/crypto-investment.git
cd crypto-investment
```

### 2. Instalar Dependencias de PHP

```bash
composer install
```

### 3. Instalar Dependencias de Node.js

```bash
npm install
```

### 4. Configurar Variables de Entorno

```bash
cp .env.example .env
```

Editar `.env` y configurar:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crypto_investment
DB_USERNAME=root
DB_PASSWORD=

COINMARKETCAP_API_KEY=tu_api_key_aqui
```

### 5. Generar Key de Aplicación

```bash
php artisan key:generate
```

### 6. Crear Base de Datos

```bash
mysql -u root -p
CREATE DATABASE crypto_investment;
exit;
```

### 7. Ejecutar Migraciones

```bash
php artisan migrate
```

### 8. Compilar Assets

Para desarrollo:
```bash
npm run dev
```

Para producción:
```bash
npm run build
```

### 9. Iniciar Servidor

```bash
php artisan serve
```

La aplicación estará disponible en: `http://localhost:8000`

## 🔑 Obtener API Key de CoinMarketCap

1. Registrarse en [CoinMarketCap](https://coinmarketcap.com/api/)
2. Seleccionar el plan gratuito (Basic)
3. Copiar la API Key
4. Agregar a `.env` en `COINMARKETCAP_API_KEY`

**Nota:** El plan gratuito incluye:
- 10,000 llamadas por mes
- Datos actualizados cada minuto
- Acceso a endpoints básicos

## 🌿 Estrategia Git

### Ramas Principales

```
main (producción)
  └── develop (desarrollo)
       ├── feature/api-integration
       ├── feature/frontend-vue
       ├── feature/real-time-updates
       ├── feature/charts
       └── feature/history-persistence
```

### Workflow de Desarrollo

```bash
# Crear rama de feature
git checkout develop
git checkout -b feature/nombre-feature

# Hacer commits
git add .
git commit -m "feat: descripción del cambio"

# Push a GitHub
git push origin feature/nombre-feature

# Crear Pull Request a develop
# Una vez aprobado, merge a develop

# Para deploy a producción
git checkout main
git merge develop
git push origin main
```

## 📁 Estructura del Proyecto

```
crypto-investment/
├── app/
│   ├── Http/Controllers/
│   │   ├── CryptoController.php
│   │   └── HistoryController.php
│   ├── Models/
│   │   ├── Cryptocurrency.php
│   │   └── PriceHistory.php
│   └── Services/
│       └── CoinMarketCapService.php
├── database/migrations/
├── resources/
│   ├── js/
│   │   ├── app.js
│   │   ├── components/
│   │   │   ├── CryptoTracker.vue
│   │   │   ├── CryptoSearch.vue
│   │   │   ├── CryptoCard.vue
│   │   │   └── PriceChart.vue
│   │   └── services/
│   │       └── api.js
│   └── views/
│       └── welcome.blade.php
└── routes/
    ├── api.php
    └── web.php
```

## 🔌 Endpoints API

### Criptomonedas

```
GET    /api/cryptocurrencies          # Listar trackeadas
GET    /api/cryptocurrencies/search   # Buscar
POST   /api/cryptocurrencies/track    # Agregar al seguimiento
DELETE /api/cryptocurrencies/{id}     # Eliminar
GET    /api/cryptocurrencies/prices   # Obtener precios actuales
```

### Historial

```
GET /api/history/{id}              # Historial completo
GET /api/history/{id}/range        # Historial en rango
    ?start_date=2024-01-01
    &end_date=2024-01-31
```

## ⚡ Funcionalidades

### Actualización Automática

La aplicación actualiza los precios automáticamente cada 30 segundos cuando hay criptomonedas en seguimiento.

### Persistencia de Datos

Todos los precios obtenidos se guardan en la base de datos, permitiendo:
- Consulta de históricos
- Análisis de tendencias
- Gráficos con datos reales

### Gráficos Interactivos

Visualización con Chart.js
