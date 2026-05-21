# Deployment Guide

## 1. Google OAuth Setup

1. Go to the [Google Cloud Console](https://console.cloud.google.com) and create an OAuth 2.0 Client ID and Secret.
2. Add **Google Drive API** to your project.
3. Set the following Redirect URIs on your OAuth client:
   - `https://developers.google.com/oauthplayground`
   - `https://google.com`
4. Publish the OAuth consent screen.
5. Go to [OAuth Playground](https://developers.google.com/oauthplayground) to generate a fully-refreshed token.

---

## 2. Configure Environment Variables

1. Get your Google Drive folder ID from the URL:
https://drive.google.com/drive/folders/{YOUR_FOLDER_ID}

2. Navigate to your project folder and open `.env`:
```bash
   cd {folder}
   nano .env
```

3. Add the following:
```env
   GOOGLE_DRIVE_FOLDER_ID=
   BACKUP_DRIVE_FOLDER_ID=
```

---

## 3. NGINX & SSL Setup

Test and reload NGINX:
```bash
sudo nginx -t                  # Must output "ok"
sudo systemctl reload nginx
```

Install Certbot and generate an SSL certificate:
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Test auto-renewal:
```bash
sudo certbot renew --dry-run
```

---

## 4. Docker — First-Time Setup

```bash
docker compose up -d --build
```

Then run these one by one:

```bash
# Generate app key
docker compose exec app php artisan key:generate
# If the above fails, add --force:
# docker compose exec app php artisan key:generate --force

# Run database migrations
docker compose exec app php artisan migrate --force

# Seed the database (first time only)
docker compose exec app php artisan db:seed

# Cache config and routes for production
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

---

## 5. Stopping Docker

```bash
docker compose down
```

> ⚠️ **Never use `-v`** — it will wipe the database.

---

## 6. Deploying Updates

```bash
cd /apps/logistics
git pull
docker compose up -d --build app
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

**If there was a rebase:**
```bash
git fetch origin
git reset --hard origin/master
```

---

## 7. Accessing the Database

```bash
docker compose exec postgres psql -U username -d database_name
```
