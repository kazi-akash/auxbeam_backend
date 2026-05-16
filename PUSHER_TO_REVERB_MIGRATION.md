# Pusher to Laravel Reverb Migration Guide
## Auxbeam E-Commerce Platform

**Migration Date:** May 8, 2026  
**Status:** ✅ REVERB ALREADY CONFIGURED

---

## Executive Summary

Good news! **Laravel Reverb is already installed and configured** in your application. The system is currently set to use Reverb (`BROADCAST_DRIVER=reverb`), but Pusher credentials are still present in the configuration. This guide will help you complete the migration by:

1. Removing Pusher dependencies
2. Updating frontend configuration
3. Testing Reverb functionality
4. Cleaning up Pusher references

---

## Current Status Analysis

### ✅ What's Already Done

1. **Laravel Reverb Installed**
   - Package: `laravel/reverb` v1.10.1
   - Configuration file: `config/reverb.php` ✅
   - Environment variables: Set ✅

2. **Broadcast Driver Set to Reverb**
   ```env
   BROADCAST_DRIVER=reverb
   ```

3. **Reverb Configuration**
   ```env
   REVERB_APP_ID=533026
   REVERB_APP_KEY=e7rrvaeu6gqi9pfaiy7l
   REVERB_APP_SECRET=tkysyp8cc6rkjrxoaq0f
   REVERB_HOST="localhost"
   REVERB_PORT=8080
   REVERB_SCHEME=http
   ```

4. **Frontend Echo Configuration**
   - File: `resources/js/echo.js`
   - Already configured for Reverb ✅

### ⚠️ What Needs Cleanup

1. **Pusher Package** - Still installed in composer.json
2. **Pusher Credentials** - Still in .env file
3. **Frontend Example** - Still references Pusher
4. **Documentation** - Still mentions Pusher

---

## Migration Steps

### Step 1: Remove Pusher Package

**Current composer.json:**
```json
"pusher/pusher-php-server": "^7.2"
```

**Action Required:**
```bash
composer remove pusher/pusher-php-server
```

### Step 2: Clean Up Environment Variables

**Remove these lines from .env:**
```env
PUSHER_APP_ID=2126256
PUSHER_APP_KEY=a0b93b5b3a7936dfac19
PUSHER_APP_SECRET=635607736d756d2555e8
PUSHER_APP_CLUSTER=ap2
```

**Remove these Vite variables:**
```env
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

**Keep these Reverb variables:**
```env
BROADCAST_DRIVER=reverb

REVERB_APP_ID=533026
REVERB_APP_KEY=e7rrvaeu6gqi9pfaiy7l
REVERB_APP_SECRET=tkysyp8cc6rkjrxoaq0f
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Step 3: Update Broadcasting Configuration

**File: `config/broadcasting.php`**

Add Reverb connection (if not already present):

```php
'connections' => [
    
    'reverb' => [
        'driver' => 'reverb',
        'key' => env('REVERB_APP_KEY'),
        'secret' => env('REVERB_APP_SECRET'),
        'app_id' => env('REVERB_APP_ID'),
        'options' => [
            'host' => env('REVERB_HOST', 'localhost'),
            'port' => env('REVERB_PORT', 8080),
            'scheme' => env('REVERB_SCHEME', 'http'),
            'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
        ],
    ],

    // Keep pusher for backward compatibility (optional)
    'pusher' => [
        // ... existing config
    ],
],
```

### Step 4: Update Frontend Configuration

**Current: `resources/js/echo.js` (Already Correct!)**
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

**Note:** Even though it imports Pusher, it's using Reverb broadcaster. This is correct because Laravel Echo uses Pusher's protocol.

### Step 5: Update Frontend Example File

**File: `frontend-notification-example.js`**

Replace Pusher configuration with Reverb:

```javascript
// OLD (Pusher)
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.REACT_APP_PUSHER_KEY,
    cluster: process.env.REACT_APP_PUSHER_CLUSTER,
    forceTLS: true,
    authEndpoint: 'http://127.0.0.1:8000/broadcasting/auth',
});

// NEW (Reverb)
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: process.env.REACT_APP_REVERB_APP_KEY,
    wsHost: process.env.REACT_APP_REVERB_HOST || 'localhost',
    wsPort: process.env.REACT_APP_REVERB_PORT || 8080,
    wssPort: process.env.REACT_APP_REVERB_PORT || 443,
    forceTLS: (process.env.REACT_APP_REVERB_SCHEME || 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: 'http://127.0.0.1:8000/broadcasting/auth',
});
```

---

## Running Laravel Reverb

### Start Reverb Server

**Development:**
```bash
php artisan reverb:start
```

**With Debug Output:**
```bash
php artisan reverb:start --debug
```

**Production (with Supervisor):**
```ini
[program:reverb]
command=php /path/to/artisan reverb:start
directory=/path/to/project
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/logs/reverb.log
```

### Check Reverb Status

```bash
# Check if Reverb is running
php artisan reverb:restart

# View Reverb connections
# Access: http://localhost:8080
```

---

## Testing the Migration

### 1. Start Reverb Server

```bash
php artisan reverb:start --debug
```

You should see:
```
Starting server on 0.0.0.0:8080...
Server started successfully.
```

### 2. Test Broadcasting

**Using Artisan Command:**
```bash
php artisan tinker

# In Tinker:
$user = App\Models\User::first();
$notification = App\Models\Notification::create([
    'user_id' => $user->id,
    'title' => 'Test Notification',
    'message' => 'Testing Reverb',
    'notification_type' => 'info',
    'status' => 'unread'
]);

event(new App\Events\NotificationEvent($notification));
```

### 3. Test from Frontend

**JavaScript Console:**
```javascript
// Subscribe to private channel
Echo.private('user.1')
    .listen('NotificationEvent', (e) => {
        console.log('Notification received:', e);
    });
```

### 4. Verify Connection

**Check Reverb Debug Output:**
```
[2026-05-08 10:30:45] Connection established: connection-id-123
[2026-05-08 10:30:46] Subscribed to channel: private-user.1
[2026-05-08 10:30:50] Message sent to channel: private-user.1
```

---

## Comparison: Pusher vs Reverb

| Feature | Pusher | Laravel Reverb |
|---------|--------|----------------|
| **Cost** | Paid (after free tier) | Free (self-hosted) |
| **Setup** | External service | Built into Laravel |
| **Performance** | Cloud-based | Local/VPS |
| **Scalability** | Automatic | Manual (Redis scaling) |
| **Control** | Limited | Full control |
| **Latency** | Variable | Lower (local) |
| **Privacy** | Data on Pusher servers | Data on your servers |
| **Configuration** | API keys | Local config |
| **Debugging** | Pusher dashboard | Local logs |
| **Protocol** | Pusher protocol | Pusher-compatible |

---

## Advantages of Reverb

### 1. **Cost Savings**
- No monthly fees
- No connection limits
- No message limits
- Free forever

### 2. **Performance**
- Lower latency (local server)
- No external API calls
- Direct WebSocket connections
- Faster message delivery

### 3. **Privacy & Security**
- All data stays on your servers
- No third-party data sharing
- Full control over security
- GDPR compliant by default

### 4. **Developer Experience**
- Built into Laravel
- Same API as Pusher
- Easy debugging
- Local development friendly

### 5. **Scalability**
- Redis scaling support
- Horizontal scaling possible
- No vendor lock-in
- Full control over infrastructure

---

## Production Deployment

### 1. Update Environment Variables

**Production .env:**
```env
BROADCAST_DRIVER=reverb

REVERB_APP_ID=your-production-id
REVERB_APP_KEY=your-production-key
REVERB_APP_SECRET=your-production-secret
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https

# For scaling (optional)
REVERB_SCALING_ENABLED=true
REDIS_HOST=your-redis-host
REDIS_PORT=6379
```

### 2. Configure Supervisor

**File: `/etc/supervisor/conf.d/reverb.conf`**
```ini
[program:reverb]
process_name=%(program_name)s
command=php /var/www/html/artisan reverb:start
directory=/var/www/html
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/reverb.log
stopwaitsecs=3600
```

**Start Supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

### 3. Configure Nginx (WebSocket Proxy)

**File: `/etc/nginx/sites-available/your-site`**
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    # ... other configuration

    # WebSocket proxy for Reverb
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
    }
}
```

**Reload Nginx:**
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 4. Configure Firewall

```bash
# Allow WebSocket port
sudo ufw allow 8080/tcp

# Or if using Nginx proxy, only allow local
sudo ufw deny 8080/tcp
```

---

## Troubleshooting

### Issue 1: Reverb Won't Start

**Error:** `Address already in use`

**Solution:**
```bash
# Find process using port 8080
lsof -i :8080

# Kill the process
kill -9 <PID>

# Or change port in .env
REVERB_PORT=8081
```

### Issue 2: Frontend Can't Connect

**Error:** `WebSocket connection failed`

**Check:**
1. Reverb server is running: `ps aux | grep reverb`
2. Port is accessible: `telnet localhost 8080`
3. Firewall allows connections
4. Correct host/port in frontend config

**Solution:**
```bash
# Restart Reverb
php artisan reverb:restart

# Check logs
tail -f storage/logs/laravel.log
```

### Issue 3: Authentication Fails

**Error:** `401 Unauthorized`

**Check:**
1. Broadcasting routes are registered
2. Sanctum middleware is working
3. CORS is configured correctly

**Solution:**
```php
// routes/channels.php
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

### Issue 4: Messages Not Received

**Check:**
1. Event implements `ShouldBroadcast`
2. Channel name matches subscription
3. Reverb debug shows message sent
4. Frontend is subscribed to correct channel

**Debug:**
```bash
# Start Reverb with debug
php artisan reverb:start --debug

# Watch for:
# - Connection established
# - Channel subscribed
# - Message broadcast
```

---

## Rollback Plan

If you need to rollback to Pusher:

### 1. Reinstall Pusher

```bash
composer require pusher/pusher-php-server
```

### 2. Update .env

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your-pusher-id
PUSHER_APP_KEY=your-pusher-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_APP_CLUSTER=ap2
```

### 3. Update Frontend

```javascript
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.REACT_APP_PUSHER_KEY,
    cluster: process.env.REACT_APP_PUSHER_CLUSTER,
    forceTLS: true,
});
```

### 4. Stop Reverb

```bash
# Stop Reverb server
php artisan reverb:stop

# Or via Supervisor
sudo supervisorctl stop reverb
```

---

## Cleanup Checklist

### ✅ Backend Cleanup

- [ ] Remove Pusher package: `composer remove pusher/pusher-php-server`
- [ ] Remove Pusher env variables from `.env`
- [ ] Remove Pusher env variables from `.env.example`
- [ ] Update `config/broadcasting.php` (optional - can keep for reference)
- [ ] Clear config cache: `php artisan config:clear`

### ✅ Frontend Cleanup

- [ ] Update `frontend-notification-example.js` to use Reverb
- [ ] Remove Pusher references from documentation
- [ ] Update README.md
- [ ] Update SYSTEM_REQUIREMENTS.md

### ✅ Documentation Cleanup

- [ ] Update deployment guides
- [ ] Update API documentation
- [ ] Update environment setup guides
- [ ] Remove Pusher from service configuration docs

### ✅ Testing

- [ ] Test notification broadcasting
- [ ] Test private channels
- [ ] Test presence channels (if used)
- [ ] Test authentication
- [ ] Load test with multiple connections
- [ ] Test reconnection after disconnect

---

## Performance Monitoring

### Monitor Reverb Performance

```bash
# Check connections
php artisan reverb:connections

# Monitor logs
tail -f storage/logs/reverb.log

# Check memory usage
ps aux | grep reverb

# Monitor with htop
htop -p $(pgrep -f reverb)
```

### Metrics to Track

- **Connections:** Number of active WebSocket connections
- **Messages/sec:** Broadcast message throughput
- **Latency:** Time from broadcast to client receipt
- **Memory:** Reverb process memory usage
- **CPU:** Reverb process CPU usage
- **Errors:** Failed broadcasts or connections

---

## Conclusion

### ✅ Migration Status: READY

Your application is **already configured to use Laravel Reverb**. To complete the migration:

1. **Remove Pusher package** (optional, for cleanup)
2. **Clean up environment variables** (remove Pusher credentials)
3. **Update documentation** (replace Pusher references)
4. **Test thoroughly** (verify all notifications work)

### Benefits Achieved

- ✅ **$0/month** cost (vs Pusher's paid plans)
- ✅ **Lower latency** (local WebSocket server)
- ✅ **Full control** (self-hosted solution)
- ✅ **Better privacy** (data stays on your servers)
- ✅ **Easier debugging** (local logs and monitoring)

### Next Steps

1. Start Reverb server: `php artisan reverb:start --debug`
2. Test notifications from admin panel
3. Verify frontend receives messages
4. Deploy to production with Supervisor
5. Monitor performance and connections

---

**Migration Guide Created By:** Kiro AI Assistant  
**Date:** May 8, 2026  
**Status:** ✅ REVERB CONFIGURED & READY

