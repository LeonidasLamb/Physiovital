// Vercel Serverless Function: Bewerbung per E-Mail versenden
//
// Versand läuft über Resend (resend.com, kostenlos bis 3.000 Mails/Monat).
// Einrichtung siehe README, Abschnitt "Auf Vercel veröffentlichen".
//
// Benötigte Environment-Variablen im Vercel-Projekt:
//   RESEND_API_KEY   API-Key von resend.com
//   MAIL_ABSENDER    z. B. funnel@rwm-media.de (Domain muss bei Resend verifiziert sein)
//   MAIL_EMPFAENGER  z. B. bewerbung@physiovital-frankfurt.de

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ ok: false });
  }

  const d = req.body || {};

  // Einfacher Spam-Schutz: nur Anfragen aus dem Funnel annehmen
  if (d.quelle !== 'physio-funnel') {
    return res.status(403).json({ ok: false });
  }

  const clean = (v) => String(v || '').replace(/[\r\n<>]/g, ' ').trim().slice(0, 300);
  const name = clean(d.name);
  const email = clean(d.email);
  const tel = clean(d.tel);

  // E-Mail ist optional, Name und Telefonnummer sind Pflicht
  if (!name || tel.replace(/\D/g, '').length < 6 || (email && !email.includes('@'))) {
    return res.status(422).json({ ok: false });
  }

  const text = [
    'Neue Bewerbung über den Recruiting-Funnel',
    '==========================================',
    '',
    `Name:               ${name}`,
    `E-Mail:             ${email || '(nicht angegeben)'}`,
    `Telefon:            ${tel}`,
    '',
    `Größter Störfaktor: ${clean(d.schmerz)}`,
    `Erfahrung:          ${clean(d.erfahrung)}`,
    `Arbeitszeit:        ${clean(d.arbeitszeit)}`,
    '',
    `Eingegangen: ${new Date().toLocaleString('de-DE', { timeZone: 'Europe/Berlin' })} Uhr`,
    'Tipp: Innerhalb von 24 Stunden zurückrufen, das wurde auf der Seite versprochen.'
  ].join('\n');

  try {
    const antwort = await fetch('https://api.resend.com/emails', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${process.env.RESEND_API_KEY}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        from: `PhysioVital Funnel <${process.env.MAIL_ABSENDER}>`,
        to: [process.env.MAIL_EMPFAENGER],
        ...(email ? { reply_to: email } : {}),
        subject: `Neue Bewerbung: ${name}`,
        text
      })
    });

    if (!antwort.ok) {
      console.error('Resend-Fehler:', await antwort.text());
      return res.status(502).json({ ok: false });
    }

    return res.status(200).json({ ok: true });
  } catch (fehler) {
    console.error('Versand-Fehler:', fehler);
    return res.status(500).json({ ok: false });
  }
}
