# APTrades Deployment Video Script
**Title:** Deploy APTrades on Namecheap in 5 Minutes
**Duration:** ~5 minutes
**Style:** Screen recording with voiceover

---

## SCENE 1 — Intro (10 seconds)

**Visual:** APTrades logo animation on dark gradient background

**Voiceover:**
"Deploying APTrades on Namecheap takes just five minutes. Here's how."

---

## SCENE 2 — Create Database (40 seconds)

**Visual:** Screen recording — cPanel → MySQL Databases

**Voiceover:**
"First, log into cPanel. Go to MySQL Databases. Create a new database — call it investgada. Then create a database user with a strong password. Add the user to the database with ALL PRIVILEGES. Save your database name, username, and password — you'll need them in a moment."

**On-screen text:**
```
Database: username_investgada
Username: username_investgada
Password: ********
Host: localhost
```

---

## SCENE 3 — Set PHP Version (25 seconds)

**Visual:** Screen recording — cPanel → Select PHP Version

**Voiceover:**
"Go to Select PHP Version. Set it to 8.2 or 8.3. Click the Extensions tab and make sure pdo_mysql, mbstring, gd, zip, curl, and xml are all checked. Save."

**On-screen text:** PHP 8.2+ Required

---

## SCENE 4 — Upload Files (50 seconds)

**Visual:** Screen recording — cPanel File Manager → Upload → Extract

**Voiceover:**
"Now go to File Manager, open public_html. Download the ZIP from GitHub, upload it here, and extract. Make sure the files are directly in public_html — not in a subfolder. You should see app, bootstrap, config, public, resources, routes, and the rest."

**On-screen text:** public_html/ (not public_html/Investgada/)

---

## SCENE 5 — Install Composer (40 seconds)

**Visual:** Screen recording — cPanel Terminal

**Voiceover:**
"If your plan has Terminal, open it and run: composer install --no-dev. This downloads the PHP dependencies. It takes about a minute. If you don't have Terminal, install Composer on your computer, run composer install locally, then upload the vendor folder."

**On-screen text:**
```
cd ~/public_html
composer install --no-dev --optimize-autoloader
```

---

## SCENE 6 — Prepare .env & .htaccess (40 seconds)

**Visual:** Screen recording — File Manager → Edit .env

**Voiceover:**
"Copy .env.example and rename it to .env. Edit it and set your domain URL and database credentials. Then make sure your root .htaccess has the rewrite rule to route traffic through the public folder."

**On-screen text:**
```
APP_URL=https://yourdomain.com
DB_DATABASE=username_investgada
DB_USERNAME=username_investgada
DB_PASSWORD=********
```

---

## SCENE 7 — Run the Installer (60 seconds)

**Visual:** Screen recording — Browser → yourdomain.com/install

**Voiceover:**
"Now open your browser and go to yourdomain.com/install. The setup wizard appears. Step one checks your server requirements — everything should be green. Step two, enter your database credentials and click Test Connection. Step three, create your admin account with name, email, and password. Click Install Now."

**On-screen text:** yourdomain.com/install

---

## SCENE 8 — Installation Running (30 seconds)

**Visual:** Screen recording — Installer progress screen with green checkmarks

**Voiceover:**
"The installer runs everything automatically — app key, migrations, storage symlink, permissions, admin account, default settings, investment packages, deposit addresses. Each step gets a green check. When it's done, click Login."

**On-screen text:**
```
✓ Application Key Generated
✓ Database Tables Created
✓ Admin Account Created
✓ Installation Complete!
```

---

## SCENE 9 — Admin Panel (20 seconds)

**Visual:** Screen recording — Admin dashboard with purple gradient theme

**Voiceover:**
"You're in. Configure your platform — set your logo, investment packages, crypto addresses, trading pairs, and MLM ranks. Everything is in the admin settings."

---

## SCENE 10 — Add Cron Job (25 seconds)

**Visual:** Screen recording — cPanel → Cron Jobs

**Voiceover:**
"Last step — set up the cron job. Go to cPanel, Cron Jobs, set it to run every minute, and add this command pointing to your public_html folder. This powers scheduled tasks like payouts and rank updates."

**On-screen text:**
```
cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
```

---

## SCENE 11 — Mobile PWA Demo (20 seconds)

**Visual:** Phone screen recording — Opening site on iPhone → Add to Home Screen → App opens fullscreen

**Voiceover:**
"And it works as an app too. On iPhone or Android, open the site, tap Add to Home Screen, and APTrades installs as a native app with its own icon — no browser address bar."

---

## SCENE 12 — Outro (10 seconds)

**Visual:** APTrades logo + GitHub link

**Voiceover:**
"That's it. Full deployment guide is in the repo at NAMECHEAP.md. Star the repo if it helped."

**On-screen text:**
```
github.com/gadachat/Investgada
NAMECHEAP.md
```

---

## Production Notes

- **Total runtime:** ~5 minutes 30 seconds
- **Recording tool:** OBS Studio or Loom
- **Resolution:** 1920x1080 (desktop scenes) + phone screen mirror (PWA scene)
- **Background music:** Soft electronic, low volume
- **Font:** Inter or system font, white on dark background
- **Accent color:** #7c3aed (purple gradient) for highlights and text overlays

## Recording Checklist

- [ ] cPanel access ready
- [ ] Database created
- [ ] PHP version set to 8.2+
- [ ] Files uploaded to public_html
- [ ] Composer installed
- [ ] .env file configured
- [ ] .htaccess rewrite rule in place
- [ ] Browser open for installer walkthrough
- [ ] Phone ready for PWA demo
- [ ] Microphone tested
