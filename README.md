# PhysioVital Recruiting-Funnel

Mobile-first Landingpage mit mehrstufigem Bewerbungsprozess: Störfaktor, Erfahrung, Arbeitszeit, dann Name und Handynummer (E-Mail optional).

## Ordnerstruktur

```
physiovital-funnel/
├── index.html          komplette Seite inkl. CSS, JS und Funnel
├── assets/             Bilder (Logo, Hero, Conny, Favicon)
├── api/bewerbung.js    Mailversand-Funktion (läuft automatisch auf Vercel)
├── send-bewerbung.php  Alternative für WordPress-Hosting (nicht für Vercel nötig)
├── .vercelignore       schließt PHP & README vom Deployment aus
└── README.md           diese Anleitung
```

## 1. Bilder

Alle Bilder liegen bereits in `assets/` und sind eingebunden:

| Datei | Inhalt |
|---|---|
| `physiovital.webp` | Praxis-Logo (Header) |
| `hero.jpg` | Hero-Bild |
| `conny.jpg` | Porträt Conny (wird rund zugeschnitten) |
| `favicon.png` | Browser-Icon (aus dem Logo erzeugt) |

Zum Austauschen einfach eine neue Datei mit demselben Namen ablegen.

## 2. Bewerbungen per E-Mail erhalten

**Empfohlener Weg: eigenes PHP-Skript, kein Anbieter nötig.**

Die Praxis-Website läuft auf WordPress, also auf PHP-Hosting. Das reicht:

1. `send-bewerbung.php` aus diesem Ordner per FTP oder Hosting-Dateimanager in das Hauptverzeichnis von physiovital-frankfurt.de hochladen (dorthin, wo `wp-config.php` liegt).
2. In `index.html` eintragen:

```js
const WEBHOOK_URL = "https://physiovital-frankfurt.de/send-bewerbung.php";
```

Fertig. Jede Bewerbung geht als Mail an `bewerbung@physiovital-frankfurt.de` (Empfänger und Absender stehen oben in der PHP-Datei und lassen sich dort ändern). Keine Registrierung, keine Verifizierung, kein Drittanbieter, und weil die Mail vom eigenen Server der Domain kommt, ist die Zustellbarkeit gut.

**Wichtig:** Die PHP-Datei gehört auf den Praxis-Server, NICHT auf GitHub Pages (GitHub führt kein PHP aus). Sie muss nicht mit ins Repository.

**Alternative ohne FTP-Zugriff:** Auf [web3forms.com](https://web3forms.com) die Empfänger-Adresse eintragen und den zugeschickten Access Key in `index.html` bei `WEB3FORMS_KEY` einfügen. Haken: Der Bestätigungslink geht an das Bewerbungs-Postfach, jemand mit Zugriff darauf muss ihn einmal anklicken.

Solange beides leer ist, landen Testbewerbungen nur in der Browser-Konsole (F12 → Konsole).

## 3. Auf Vercel veröffentlichen (mit Subdomain)

### Schritt A: GitHub

1. Neues Repository auf github.com anlegen (z. B. `physio-funnel`), public oder privat, beides geht mit Vercel.
2. Alle Dateien dieses Ordners hochladen ("Add file" → "Upload files", Ordnerinhalt reinziehen). `send-bewerbung.php` kannst Du weglassen, die ist nur für den WordPress-Weg.

### Schritt B: Vercel

1. Auf [vercel.com](https://vercel.com) mit dem GitHub-Account anmelden.
2. **Add New → Project** → das Repository importieren → **Deploy** (keine Einstellungen nötig).
3. Die Seite ist sofort live unter `https://physio-funnel.vercel.app`.

### Schritt C: Mailversand aktivieren (einmalig, ca. 10 Minuten)

Der Funnel schickt Bewerbungen an die eingebaute Funktion `/api/bewerbung`. Damit die mailen darf:

1. Kostenlosen Account auf [resend.com](https://resend.com) anlegen (bis 3.000 Mails/Monat gratis).
2. Dort unter **Domains** Deine eigene Domain `rwm-media.de` hinzufügen und die angezeigten DNS-Einträge bei Deinem Domain-Anbieter setzen. Dadurch brauchst Du keinerlei Zugriff auf die Praxis-Mails.
3. Unter **API Keys** einen Key erstellen.
4. Im Vercel-Projekt unter **Settings → Environment Variables** drei Variablen anlegen und danach einmal neu deployen:

| Name | Wert |
|---|---|
| `RESEND_API_KEY` | Dein Resend-Key |
| `MAIL_ABSENDER` | `funnel@rwm-media.de` |
| `MAIL_EMPFAENGER` | `bewerbung@physiovital-frankfurt.de` |

Jede Bewerbung landet dann automatisch im Praxis-Postfach, mit Reply-To auf den Bewerber.

### Schritt D: Subdomain verbinden

1. Im Vercel-Projekt: **Settings → Domains** → `jobs.physiovital-frankfurt.de` eintragen.
2. Beim DNS-Anbieter der Praxis-Domain einen CNAME-Eintrag setzen: `jobs` → `cname.vercel-dns.com`. Das ist EIN Eintrag im DNS-Menü des Domain-Anbieters (z. B. IONOS, Strato, All-Inkl), WordPress wird dafür nicht angefasst.
3. SSL-Zertifikat stellt Vercel automatisch aus, nach wenigen Minuten ist die Seite unter der Subdomain live.

**Kein Zugriff aufs DNS der Praxis?** Dann geht als Zwischenlösung auch die vercel.app-Adresse für die Anzeigen, oder eine Subdomain Deiner eigenen Domain (z. B. `physio-jobs.rwm-media.de`), die Du selbst per CNAME verbindest.

## 4. Vor dem Launch prüfen

- [x] Bilder in `assets/` abgelegt, Platzhalter weg
- [ ] Environment-Variablen in Vercel gesetzt und Testbewerbung per Mail angekommen
- [ ] Funnel einmal komplett am Handy durchgeklickt
- [ ] Impressum- und Datenschutz-Links zeigen auf die richtigen Seiten (aktuell: physiovital-frankfurt.de)
- [ ] Tracking-Pixel (Meta) einbauen, falls Anzeigen laufen: vor `</head>` einfügen
