# Quick Deployment Guide for 162.0.236.226

## Step 1: Deploy API to Your VPS (NOW)

1. **Connect to your VPS:**
   ```bash
   ssh root@162.0.236.226
   ```

2. **Upload and run the setup script:**
   ```bash
   # Upload the script (using SCP from your Windows machine)
   scp your-vps-setup.sh root@162.0.236.226:/tmp/
   
   # Make it executable and run
   chmod +x /tmp/your-vps-setup.sh
   /tmp/your-vps-setup.sh
   ```

3. **Test the API immediately:**
   ```bash
   curl -H "X-API-Key: 9097332919794dea83dd2de22191ec913a1b8f44" http://162.0.236.226/status
   ```

## Step 2: Set Up Namecheap Shared Hosting

1. **Upload your Laravel app to Namecheap:**
   - Use your `.env.namecheap` configuration
   - Point your domain's document root to the `public` folder

2. **Configure DNS:**
   - Main domain → Namecheap shared hosting
   - `api.yourdomain.com` → 162.0.236.226 (A record)

## Step 3: Test Complete System

1. **Test from your Laravel app:**
   ```php
   // This should work once deployed
   $response = Http::withHeaders([
       'X-API-Key' => '9097332919794dea83dd2de22191ec913a1b8f44'
   ])->get('http://162.0.236.226/status');
   ```

## Quick Verification Checklist

- [ ] VPS API responds to status check
- [ ] libembroidery-convert command works
- [ ] Laravel app connects to VPS API
- [ ] File upload/conversion/download flow works
- [ ] DNS records properly configured

## Emergency Contacts & Info

- **VPS IP:** 162.0.236.226
- **API Key:** 9097332919794dea83dd2de22191ec913a1b8f44
- **SSH Access:** root@162.0.236.226
- **Server Type:** AlmaLinux 8 cPanel

## Next Steps After API Deployment

1. Deploy Laravel app to Namecheap shared hosting
2. Update DNS records
3. Test complete embroidery conversion workflow
4. Monitor server performance and logs

Your system is ready for production! 🚀
