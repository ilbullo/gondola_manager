# 🚤 Work Assignment Manager

Un’applicazione Laravel progettata per gestire, organizzare e monitorare i lavori giornalieri di una flotta di mezzi o squadre operative.  
Il sistema permette l’inserimento rapido dei lavori, la loro suddivisione (splitting) per licenze, la gestione dei conteggi e l’analisi complessiva delle attività.

---

## ✨ Funzionalità principali

### 📌 1. Gestione dei lavori (Work Assignments)
- Inserimento dei lavori con attributi come:
  - tipo lavoro (A, X, P, N…)
  - orari
  - quantità
  - note
  - licenza assegnata
- Validazione lato server tramite Livewire.
- Aggiornamento immediato senza ricaricare la pagina.
- Salvataggio in tempo reale.

---

### 📊 2. Riassunto e conteggi automatici
Il sistema calcola automaticamente:
- totale lavori per categoria  
- numero lavori assegnati  
- lavori non assegnati  
- metriche personalizzate basate sulle regole interne

Il metodo `refreshCounts()` è idempotente, assicurando conteggi sempre coerenti.

---

### 🔄 3. Splitter delle tabelle
Un algoritmo dedicato gestisce la distribuzione automatica dei lavori alle licenze disponibili:
- processa i lavori in base agli slot disponibili  
- assegna i lavori alle licenze seguendo le regole scelte  
- identifica i lavori che rimangono non assegnati  
- permette correzioni manuali  
- tiene conto di limiti come max_slots per ciascuna licenza

---

### 🖥️ 4. Interfaccia Livewire dinamica
L’app utilizza componenti Livewire per una UI ultra-reattiva:
- aggiornamenti al volo  
- modali dinamici  
- loading modal smart che compare SOLO durante azioni lente (come inserimento lavoro)  
- interazioni fluide grazie anche ad Alpine.js

---

### 🛠️ 5. Architettura estendibile
Il progetto è pensato per essere espanso facilmente:
- aggiunta di nuove tipologie di lavori  
- modifiche alle logiche di splitting  
- integrazione con API  
- implementazione profili e permessi  

---

## 🔧 Stack tecnologico

- **Laravel**  
- **Livewire**  
- **Blade**  
- **MySQL/MariaDB**  
- **Tailwind CSS** (o Bootstrap, secondo la configurazione)  
- **Alpine.js**

---

## 📁 Struttura del progetto

```
app/
  Http/
    Livewire/
      WorkAssignmentTable.php
      TableSplitter.php
      WorkSummary.php

resources/
  views/
    livewire/
      work-assignment-table.blade.php
      table-splitter.blade.php
      work-summary.blade.php
```

---

## 🚀 Avvio rapido

### 1. Clona il progetto
```bash
git clone https://github.com/<username>/<repo>.git
cd <repo>
```

### 2. Installa dipendenze
```bash
composer install
npm install && npm run build
```

### 3. Configura ambiente
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Migrazioni database
```bash
php artisan migrate
```

### 5. Avvia il server
```bash
php artisan serve
```

---

## 🧪 Esecuzione test

```bash
php artisan test
```

Test inclusi:
- assegnazione lavori  
- splitting  
- idempotenza conteggi  
- validazioni dedicate (es. shared_from_first solo per lavori 'A')

---

## 📜 Licenza

Questo progetto è distribuito sotto licenza MIT.

---

## 🤝 Contributi

Le pull request sono benvenute.  
Apri una issue per richieste, miglioramenti o segnalazioni.

---

## 📧 Contatti

Per supporto, segnalazioni o richieste:  
**<ilbullo@gmail.com>**

