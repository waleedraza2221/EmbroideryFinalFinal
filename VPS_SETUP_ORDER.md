# VPS Setup Order - Important Steps

## ⚠️ **Correct Order of Operations**

You're getting the "public_html: No such file or directory" error because we need to set up cPanel accounts first. Here's the correct sequence:

## 🚀 **Step-by-Step VPS Setup**

### **Step 1: Server Preparation (You've Done This)**
```bash
# These commands were run as root - ✅ COMPLETED
dnf update -y
dnf install -y epel-release
# ... other server setup commands
```

### **Step 2: Set Up WHM/cPanel Accounts (DO THIS NEXT)**

#### **Access WHM (Web Host Manager)**
```
URL: https://your-vps-ip:2087
Username: root
Password: your-root-password
```

#### **Create a cPanel Account**
1. **Login to WHM** at `https://your-vps-ip:2087`
2. **Go to**: Account Functions → Create a New Account
3. **Fill in**:
   - Domain: `yourdomain.com`
   - Username: `embuser` (or your choice)
   - Password: strong password
   - Package: Default or custom
4. **Click**: Create

### **Step 3: Access cPanel**
```
URL: https://your-vps-ip:2083
Username: embuser (the account you created)
Password: the password you set
```

### **Step 4: Now You'll Have public_html**
After creating the cPanel account, the directory structure will be:
```
/home/embuser/public_html/  ← This will now exist
```

## 🔧 **Current Status Check**

Run these commands to see your current setup:

```bash
# Check if WHM/cPanel is running
systemctl status cpanel

# List home directories
ls -la /home/

# Check if any cPanel accounts exist
ls -la /var/cpanel/users/
```

## 📁 **Directory Structure After cPanel Account Creation**

Once you create a cPanel account, you'll have:
```
/home/username/
├── public_html/          ← Your website files go here
├── mail/
├── etc/
├── logs/
├── public_ftp/
└── tmp/
```

## 🎯 **What to Do Right Now**

### **1. Access WHM to Create cPanel Account**
```bash
# Open your browser and go to:
https://your-vps-ip:2087

# Login with:
Username: root
Password: your-vps-root-password
```

### **2. Create Your First cPanel Account**
- Domain: Your domain name
- Username: Choose a username (e.g., "embuser")
- Password: Strong password
- Click "Create"

### **3. Then Access cPanel**
```bash
# After account creation, access cPanel at:
https://your-vps-ip:2083

# Login with the account credentials you just created
```

### **4. Upload Your Laravel Application**
Once you have cPanel access:
- Go to **File Manager**
- Navigate to `public_html`
- Upload your Laravel project ZIP file
- Extract it

## 🔍 **Troubleshooting WHM/cPanel Access**

### **If WHM doesn't load:**
```bash
# Check if cPanel services are running
systemctl status cpanel
systemctl status apache2 httpd

# Start cPanel if needed
systemctl start cpanel

# Check firewall
firewall-cmd --list-ports
# Should show ports 2082, 2083, 2087, 2096
```

### **If you forgot root password:**
```bash
# Reset root password (if you have console access)
passwd root
```

## 📋 **Complete Workflow Summary**

1. ✅ **Server Setup** (You completed this)
2. 🔄 **WHM Access** (Do this now)
3. 🔄 **Create cPanel Account** (Next step)
4. 🔄 **Upload Laravel App** (After cPanel account)
5. 🔄 **Configure Database** (Supabase setup)
6. 🔄 **Test Application** (Final verification)

## 🆘 **If WHM/cPanel Isn't Installed**

If you can't access WHM, cPanel might not be installed. Check with:
```bash
# Check if cPanel is installed
ls -la /usr/local/cpanel/

# If not installed, you may need to contact Namecheap
# VPS Pulsar should come with cPanel pre-installed
```

The key issue is that `public_html` only exists after you create a cPanel account through WHM. Once you do that, the directory structure will be created automatically! 🎉
