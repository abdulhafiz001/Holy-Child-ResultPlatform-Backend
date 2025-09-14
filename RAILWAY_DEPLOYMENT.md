# Railway Deployment Guide

## Prerequisites
1. Railway account (sign up at railway.app)
2. GitHub repository with your code
3. MySQL database (Railway provides this)

## Deployment Steps

### 1. Connect to Railway
1. Go to [railway.app](https://railway.app)
2. Sign in with GitHub
3. Click "New Project"
4. Select "Deploy from GitHub repo"
5. Choose your TGCRA-RESULT-PLATFORM-BACKEND repository

### 2. Add MySQL Database
1. In your Railway project dashboard
2. Click "New" → "Database" → "MySQL"
3. Railway will automatically create a MySQL database
4. Copy the connection details (you'll need these for environment variables)

### 3. Configure Environment Variables
In your Railway project settings, add these environment variables:

```
APP_NAME=TGCRA Result Platform
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-app-name.railway.app

DB_CONNECTION=mysql
DB_HOST=your-mysql-host
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=your-mysql-password

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

SANCTUM_STATEFUL_DOMAINS=your-app-name.railway.app
```

### 4. Generate App Key
Run this command locally to generate your APP_KEY:
```bash
php artisan key:generate --show
```

### 5. Deploy
Railway will automatically deploy when you push to your main branch.

### 6. Run Migrations and Seeders
After deployment, you can run these commands in Railway's console:
```bash
php artisan migrate
php artisan db:seed --class=AdminSeeder
```

## Admin Credentials
- Username: `admin`
- Password: `password` (change this immediately!)

## Production Checklist
- [ ] Change admin password
- [ ] Set up custom domain (optional)
- [ ] Configure email settings
- [ ] Set up monitoring
- [ ] Backup database regularly

## Troubleshooting
- Check Railway logs in the dashboard
- Ensure all environment variables are set
- Verify database connection
- Check that migrations ran successfully
