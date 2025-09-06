# Your VPS Server Configuration

## 🖥️ **Server Details**
- **IP Address**: 162.0.236.226
- **Operating System**: AlmaLinux 8 cPanel (64-bit)
- **API Key**: 8ZBJK-AHMKU-KP06L
- **API Hash**: 9097332919794dea83dd2de22191ec913a1b8f44

## 🚀 **Quick Deployment Commands**

### **Step 1: Connect to Your VPS**
```bash
ssh root@162.0.236.226
```

### **Step 2: Run the Embroidery API Setup**
```bash
# Download and run the setup script
curl -O https://raw.githubusercontent.com/waleedraza2221/EmbroideryFinalFinal/main/vps-api-setup.sh
chmod +x vps-api-setup.sh
./vps-api-setup.sh
```

### **Step 3: Configure API Security**
```bash
# Edit the API file to set your custom API key
nano /var/www/embroidery-api/index.php

# Change this line:
# if ($apiKey !== 'your-secret-api-key-change-this') {
# To:
# if ($apiKey !== '9097332919794dea83dd2de22191ec913a1b8f44') {
```

### **Step 4: Test Your API**
```bash
# Test the API status
curl -H "X-API-Key: 9097332919794dea83dd2de22191ec913a1b8f44" http://162.0.236.226/status
```

## 🌐 **DNS Configuration Needed**

### **Set up these DNS records for your domain:**
```
api.yourdomain.com     A    162.0.236.226
yourdomain.com         A    [your-shared-hosting-ip]
www.yourdomain.com     A    [your-shared-hosting-ip]
```

## 🔧 **Laravel Configuration for Shared Hosting**

### **Update your .env file on shared hosting:**
```env
# Embroidery API Configuration
EMBROIDERY_API_URL=http://162.0.236.226
EMBROIDERY_API_KEY=9097332919794dea83dd2de22191ec913a1b8f44
EMBROIDERY_API_TIMEOUT=300
```

### **For production with domain name:**
```env
# After setting up DNS
EMBROIDERY_API_URL=https://api.yourdomain.com
EMBROIDERY_API_KEY=9097332919794dea83dd2de22191ec913a1b8f44
```

## 📋 **Complete Setup Checklist**

### **VPS Setup:**
- [ ] SSH into 162.0.236.226
- [ ] Run vps-api-setup.sh script
- [ ] Configure API key (9097332919794dea83dd2de22191ec913a1b8f44)
- [ ] Test API endpoints
- [ ] Set up SSL certificate (optional but recommended)

### **Shared Hosting Setup:**
- [ ] Order Namecheap shared hosting
- [ ] Upload Laravel application
- [ ] Configure .env with VPS API details
- [ ] Test embroidery converter integration
- [ ] Set up domain DNS records

## 🧪 **API Testing Commands**

### **Test API Status:**
```bash
curl -H "X-API-Key: 9097332919794dea83dd2de22191ec913a1b8f44" http://162.0.236.226/status
```

### **Test File Conversion (after setup):**
```bash
curl -X POST \
  -H "X-API-Key: 9097332919794dea83dd2de22191ec913a1b8f44" \
  -F "file=@test.dst" \
  -F "output_format=pes" \
  http://162.0.236.226/convert
```

## 🔒 **Security Configuration**

### **Firewall Setup:**
```bash
# Allow HTTP and HTTPS traffic
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --reload
```

### **SSL Certificate Setup (Recommended):**
```bash
# Install Let's Encrypt
dnf install -y certbot python3-certbot-nginx

# Get SSL certificate (after DNS is configured)
certbot --nginx -d api.yourdomain.com
```

## 📊 **Architecture Overview**

```
┌─────────────────────────────────┐    ┌─────────────────────────────────┐
│     Namecheap Shared Hosting    │    │         VPS: 162.0.236.226      │
│                                 │    │                                 │
│  ┌─────────────────────────────┐│    │  ┌─────────────────────────────┐ │
│  │        Laravel App          ││    │  │     Embroidery API          │ │
│  │  • yourdomain.com           ││◄──►│  │  • api.yourdomain.com       │ │
│  │  • User Management          ││    │  │  • libembroidery            │ │
│  │  • Orders & Payments        ││    │  │  • File Conversion          │ │
│  │  • Dashboard                ││    │  │  • API Key Auth             │ │
│  └─────────────────────────────┘│    │  └─────────────────────────────┘ │
└─────────────────────────────────┘    └─────────────────────────────────┘
```

## 🎯 **Next Steps**

1. **SSH into your VPS** at 162.0.236.226
2. **Run the setup script** to install the embroidery API
3. **Configure the API key** with your hash
4. **Test the API** to ensure it's working
5. **Set up shared hosting** and configure the Laravel app
6. **Configure DNS** to point to both servers

Your embroidery management system will be ready for production! 🎉
