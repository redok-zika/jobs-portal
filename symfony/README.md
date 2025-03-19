# Recruitis Job Board

Aplikace pro zobrazování a správu pracovních nabídek pomocí Recruitis.io API.

## Technologie

- Vue.js 3 + TypeScript
- Bootstrap 5
- Pinia pro state management
- PHP 8.2+ s frameworkem Symfony 6.4
- Docker pro vývojové prostředí

## Požadavky

- Docker a Docker Compose
- Node.js 20+ (pro lokální vývoj)
- PHP 8.2+ (pro lokální vývoj)
- Composer (pro lokální vývoj)

## Instalace a spuštění

### S Dockerem (doporučeno)

1. Naklonujte repozitář
2. Vytvořte `.env.local` a nastavte API klíč:
   ```
   RECRUITIS_API_KEY=váš_api_klíč
   ```
3. Spusťte kontejnery:
   ```bash
   docker-compose up -d
   ```

Aplikace bude dostupná na:

- Frontend: http://localhost:5173
- Backend API: http://localhost:8080

### Lokální vývoj

1. Nainstalujte závislosti:

   ```bash
   # Backend
   composer install

   # Frontend
   npm install
   ```

2. Spusťte vývojové servery:

   ```bash
   # Backend
   composer serve

   # Frontend
   npm run dev
   ```

## Vývoj

### Frontend

Dostupné příkazy:

```bash
npm run dev          # Spustí vývojový server
npm run build        # Vytvoří produkční build
npm run preview      # Náhled produkčního buildu
npm run lint         # Kontrola kódu
npm run lint:fix     # Automatická oprava lint chyb
npm run format       # Formátování kódu
```

### Backend

Dostupné příkazy:

```bash
composer serve       # Spustí PHP vývojový server
composer test       # Spustí všechny testy
composer test:api   # Spustí API testy
composer test:e2e   # Spustí E2E testy
composer phpstan    # Statická analýza kódu
composer cs         # Kontrola coding standards
composer cs:fix     # Automatická oprava coding standards
```

## API Endpointy

### GET /api/jobs

Seznam pracovních nabídek s podporou stránkování.

Query parametry:

- `page` (výchozí: 1)
- `limit` (výchozí: 10)

### GET /api/jobs/{id}

Detail pracovní nabídky.

### POST /api/jobs/{id}/apply

Odeslání odpovědi na pracovní nabídku.

Payload:

```json
{
  "name": "string",
  "email": "string",
  "phone": "string",
  "cover_letter": "string",
  "linkedin": "string (optional)",
  "skype": "string (optional)",
  "facebook": "string (optional)",
  "twitter": "string (optional)",
  "attachments": [
    {
      "filename": "string",
      "type": "number (1-6)",
      "base64": "string"
    }
  ],
  "salary": {
    "amount": "number",
    "currency": "string (CZK|EUR|USD|BGN|RON|HUF)",
    "unit": "string (month|manday|hour|year)",
    "type": "number (0-6)",
    "note": "string (optional)"
  },
  "gdpr_agreement": "boolean"
}
```

## Testování

### Frontend testy

```bash
npm run test        # Spustí unit testy
```

### Backend testy

```bash
composer test       # Spustí všechny testy
composer test:api   # Spustí API testy
composer test:e2e   # Spustí E2E testy
```

## Kvalita kódu

### Frontend

- ESLint pro kontrolu kódu
- Prettier pro formátování
- TypeScript pro typovou kontrolu

### Backend

- PHPStan pro statickou analýzu
- PHP_CodeSniffer pro coding standards
- PHPUnit pro testování

## Docker

Projekt obsahuje Docker konfiguraci pro vývojové prostředí:

- `app` - PHP 8.2 s Apache
- `frontend` - Node.js 20 pro frontend

### Užitečné příkazy

```bash
# Spuštění kontejnerů
docker-compose up -d

# Zastavení kontejnerů
docker-compose down

# Zobrazení logů
docker-compose logs -f

# Přístup do kontejneru
docker-compose exec app bash
docker-compose exec frontend sh
```
