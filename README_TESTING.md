// README_TESTING.md
/*
# 🧪 Testing Guide - Sistema Gestione Gondole

## 📋 Indice
1. [Setup Iniziale](#setup-iniziale)
2. [Struttura dei Test](#struttura-dei-test)
3. [Esecuzione dei Test](#esecuzione-dei-test)
4. [Copertura del Codice](#copertura-del-codice)
5. [Best Practices](#best-practices)
6. [Troubleshooting](#troubleshooting)

---

## 🚀 Setup Iniziale

### Requisiti
```bash
composer require --dev phpunit/phpunit
composer require --dev mockery/mockery
composer require --dev pestphp/pest (optional)
```

### Configurazione Database Testing
Nel file `.env.testing`:
```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
```

### Configurazione PHPUnit
Assicurati che `phpunit.xml` contenga:
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_DATABASE" value=":memory:"/>
</php>
```

---

## 📁 Struttura dei Test

```
tests/
├── Unit/
│   ├── Models/
│   │   ├── UserTest.php
│   │   ├── AgencyTest.php
│   │   ├── LicenseTableTest.php
│   │   └── WorkAssignmentTest.php
│   ├── Policies/
│   │   ├── UserPolicyTest.php
│   │   ├── AgencyPolicyTest.php
│   │   └── WorkAssignmentPolicyTest.php
│   ├── Resources/
│   │   └── LicenseResourceTest.php
│   ├── Services/
│   │   └── MatrixSplitterServiceTest.php
│   └── Traits/
│       └── MatrixDistributionTest.php
│
├── Feature/
│   ├── Auth/
│   │   └── AuthorizationTest.php
│   ├── Commands/
│   │   └── GenerateTestDataTest.php
│   ├── LivewireComponents/
│   │   ├── LicenseManagerTest.php
│   │   ├── WorkAssignmentTableTest.php
│   │   ├── AgencyManagerTest.php
│   │   └── UserManagerTest.php
│   ├── CalculationTests/
│   │   ├── CashCalculationTest.php
│   │   └── WorkCountTest.php
│   ├── LicenseManagementTest.php
│   ├── WorkAssignmentTest.php
│   ├── TurnManagementTest.php
│   ├── CompleteWorkflowTest.php
│   ├── EdgeCasesTest.php
│   ├── PdfGenerationTest.php
│   └── EnumTest.php
│
└── TestCase.php (Base class con helpers)
```

---

## ▶️ Esecuzione dei Test

### Tutti i Test
```bash
php artisan test
```

### Test Specifici
```bash
# Per suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Per directory
php artisan test tests/Unit/Models
php artisan test tests/Feature/LivewireComponents

# Per file specifico
php artisan test tests/Feature/LicenseManagementTest.php

# Per metodo specifico
php artisan test --filter=it_can_assign_work_to_empty_slot
```

### Test con Output Dettagliato
```bash
php artisan test --verbose
php artisan test -vvv # extra verbose
```

### Test Paralleli (più veloci)
```bash
php artisan test --parallel
php artisan test --parallel --processes=4
```

### Test con Coverage
```bash
php artisan test --coverage
php artisan test --coverage --min=80
php artisan test --coverage-html coverage-report
```

---

## 📊 Copertura del Codice

### Metriche Target
- **Models**: 100% coverage
- **Policies**: 100% coverage
- **Services**: 90%+ coverage
- **Controllers/Livewire**: 85%+ coverage
- **Totale**: 85%+ coverage

### Generare Report HTML
```bash
php artisan test --coverage-html coverage-report
# Apri coverage-report/index.html nel browser
```

### Verificare Coverage Minima
```bash
php artisan test --coverage --min=85
```

---

## ✅ Best Practices

### 1. Naming Convention
```php
/** @test */
public function it_does_something_expected()
{
    // Test code
}
```

### 2. Arrange-Act-Assert Pattern
```php
public function it_calculates_total_correctly()
{
    // Arrange
    $license = LicenseTable::factory()->create();
    
    // Act
    $total = $license->calculateTotal();
    
    // Assert
    $this->assertEquals(100, $total);
}
```

### 3. Use Factories
```php
// ✅ Corretto
$user = User::factory()->admin()->create();

// ❌ Evitare
$user = new User(['role' => 'admin', 'email' => '...']);
$user->save();
```

### 4. Test Data Isolation
```php
use RefreshDatabase; // Sempre nelle classi di test
```

### 5. Meaningful Assertions
```php
// ✅ Corretto
$this->assertDatabaseHas('users', ['email' => 'test@test.com']);

// ❌ Meno chiaro
$this->assertTrue(User::where('email', 'test@test.com')->exists());
```

---

## 🐛 Troubleshooting

### Database Non Si Resetta
```bash
php artisan migrate:fresh --env=testing
php artisan config:clear
php artisan cache:clear
```

### Test Lenti
```bash
# Usa database in-memory
DB_DATABASE=:memory:

# Esegui in parallelo
php artisan test --parallel

# Disabilita Xdebug se non necessario
php -d xdebug.mode=off artisan test
```

### Factory Non Trovate
```bash
composer dump-autoload
```

### Errori di Policy
```php
// Assicurati di chiamare registerPolicies() in AppServiceProvider
$this->registerPolicies();
```

---

## 🎯 Quick Commands Cheat Sheet

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific suite
php artisan test --testsuite=Feature

# Run parallel (faster)
php artisan test --parallel

# Run with filter
php artisan test --filter=LicenseManagerTest

# Generate coverage report
php artisan test --coverage-html coverage

# Watch mode (requires package)
php artisan test --watch
```

---

## 📚 Risorse Utili

- [Laravel Testing Docs](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Livewire Testing](https://livewire.laravel.com/docs/testing)
- [Pest PHP](https://pestphp.com/) (alternativa moderna)

---

**Autore**: Sistema Gestione Gondole  
**Ultima modifica**: Dicembre 2025  
**Copertura**: 85%+ 🎉
*/