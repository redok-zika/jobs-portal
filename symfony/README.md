# Recruitis Job Board

Aplikace pro zobrazování a správu pracovních nabídek pomocí Recruitis.io API.

## Technologie

### Frontend
- Vue.js 3 + TypeScript
- Element Plus pro UI komponenty
- Bootstrap 5 pro layout a základní styly
- Pinia pro state management
- Vite jako build nástroj
- Vitest pro unit testy

### Backend
- PHP 8.2+ s frameworkem Symfony 6.4
- Apache jako webový server
- Composer pro správu závislostí

### Development
- Docker a Docker Compose pro vývojové prostředí
- ESLint + Prettier pro formátování kódu
- PHPStan pro statickou analýzu PHP kódu
- PHP_CodeSniffer pro coding standards

## Požadavky

- Docker a Docker Compose
- Git

## Instalace a spuštění

### Vývojové prostředí (doporučeno)

1. Naklonujte repozitář:
   ```bash
   git clone <repository-url>
   cd recruitis-job-board
   ```

2. Vytvořte `.env.local` v adresáři `symfony` a nastavte API klíč:
   ```
   RECRUITIS_API_KEY=váš_api_klíč
   ```

3. Spusťte Docker kontejnery:
   ```bash
   docker-compose up -d
   ```

4. Aplikace bude dostupná na:
   - Frontend: http://localhost:5173
   - Backend API: http://localhost:8000

### Struktura projektu

```
.
├── docker/                 # Docker konfigurace
├── src/                    # Frontend aplikace
│   ├── components/        # Vue komponenty
│   ├── composables/       # Vue composables
│   ├── stores/           # Pinia stores
│   └── types/            # TypeScript typy
├── symfony/               # Backend aplikace
│   ├── config/           # Symfony konfigurace
│   ├── public/           # Veřejné soubory
│   ├── src/              # PHP zdrojové kódy
│   └── tests/            # Testy
└── docker-compose.yml    # Docker Compose konfigurace
```

## Vývoj

### Frontend

Dostupné příkazy:
```bash
# Spuštění vývojového serveru
npm run dev

# Build pro produkci
npm run build

# Spuštění testů
npm run test

# Lint a formátování
npm run lint
npm run format

# Kontrola TypeScript typů
npm run typecheck
```

### Backend

Dostupné příkazy (spouštět v kontejneru `app`):
```bash
# Přístup do kontejneru
docker-compose exec app bash

# Composer příkazy
composer install
composer update

# Testy
composer test

# Kontrola kódu
composer phpstan
composer cs
composer cs:fix
```

## Docker kontejnery

### Frontend (`frontend`)
- Node.js 20 na Alpine Linux
- Vite dev server na portu 5173
- Hot-reloading pro Vue komponenty
- Automatická instalace npm závislostí

### Backend (`app`)
- PHP 8.2 s Apache
- Symfony 6.4
- Composer pro správu závislostí
- Apache na portu 8000

### Užitečné Docker příkazy

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

# Rebuild kontejnerů
docker-compose build --no-cache
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

## Komponenty

### Formulářové komponenty

- `PersonalInfoSection`: Osobní údaje (jméno, email, telefon)
- `SocialMediaSection`: Sociální sítě (LinkedIn, Facebook, Twitter)
- `SalarySection`: Platové požadavky
- `AttachmentsSection`: Nahrávání příloh
- `GdprAgreement`: Souhlas se zpracováním osobních údajů

### Validace

Formuláře používají `useFormValidation` composable pro:
- Validaci povinných polí
- Kontrolu formátu emailu
- Validaci telefonního čísla
- Kontrolu URL adres
- Validaci platových údajů

## Testování

### Frontend testy
```bash
# Spuštění všech testů
npm run test
```

### Backend testy
```bash
# Unit a API
composer test
```

## Kvalita kódu

### Frontend
- ESLint pro kontrolu kódu
- Prettier pro formátování
- TypeScript pro typovou kontrolu
- Vitest pro unit testy

### Backend
- PHPStan (level 8) pro statickou analýzu
- PHP_CodeSniffer pro coding standards (PSR-12)
- PHPUnit pro testování

## Produkční nasazení

### Frontend
1. Build aplikace:
   ```bash
   npm run build
   ```
2. Výsledné soubory v `dist/` nahrát na webový server

### Backend
1. Nastavit produkční prostředí:
   ```
   APP_ENV=prod
   APP_DEBUG=0
   ```
2. Optimalizace:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
3. Nastavit Apache/Nginx pro směrování na `public/index.php`

## Troubleshooting

Může být potřeba provést následující úlohy:

1. `curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh'` nebo `sudo apt install symfony-cli` pro instalaci Symfony CLI
2. `sudo apt install docker-compose` pro instalaci Docker Compose
3. `sudo apt install php8.3-zip` pro instalaci ZIP knihovny pro PHP 8.2 resp. 8.3