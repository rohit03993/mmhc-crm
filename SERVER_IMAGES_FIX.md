# Carousel / Site images not showing on server

If the **Achievements & Media Coverage** section shows captions but **images are blank**, do this on the server.

## 1. Create storage link (required)

From the Laravel app root (e.g. `/home/themmhc/htdocs/themmhc.com`):

```bash
php artisan storage:link
```

Check that the link exists:

```bash
ls -la public/storage
```

You should see: `storage -> ../storage/app/public`

Without this link, `/storage/achievement-media/...` returns 404.

---

## 2. Ensure image files exist

Uploaded images are in `storage/app/public/achievement-media/`. This folder is **not** in git.

**Option A – Seed demo images on server:**

```bash
php artisan db:seed --class=AchievementMediaSeeder --force
```

**Option B – Add images via admin:**  
Login → **Achievements & Media** → upload images.

Check files:

```bash
ls -la storage/app/public/achievement-media/
```

---

## 3. Permissions

Web server user must read `storage` and `public`:

```bash
chown -R <web-user>:<web-user> storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

Replace `<web-user>` with the site user (e.g. `themmhc` on CloudPanel).

---

## 4. .env on server

Ensure `APP_URL` matches the live domain (e.g. `https://themmhc.com`) so image URLs are correct.

---

**Summary:** Run `php artisan storage:link`, then ensure files exist (seeder or admin upload). Fix permissions if needed.
