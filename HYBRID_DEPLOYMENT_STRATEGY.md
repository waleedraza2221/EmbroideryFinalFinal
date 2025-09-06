# Hybrid Deployment Strategy: Shared Hosting + VPS API

This approach separates your main Laravel application (on shared hosting) from the resource-intensive embroidery converter (on VPS), providing better performance and cost efficiency.

## 🎯 **Architecture Overview**

```
┌─────────────────────────┐    ┌─────────────────────────┐
│   Namecheap Shared      │    │     VPS Server          │
│   Hosting               │    │                         │
│   ┌─────────────────┐   │    │  ┌─────────────────┐    │
│   │ Laravel App     │   │ ── │  │ Embroidery API  │    │
│   │ - User Mgmt     │   │    │  │ - File Upload   │    │
│   │ - Orders        │   │    │  │ - Conversion    │    │
│   │ - Payments      │   │    │  │ - Download      │    │
│   │ - Dashboard     │   │    │  │ - libembroidery │    │
│   └─────────────────┘   │    │  └─────────────────┘    │
└─────────────────────────┘    └─────────────────────────┘
```

## ✅ **Benefits of This Approach**

### **Cost Effective:**
- ✅ Shared hosting: $3-10/month
- ✅ VPS only for API: $5-15/month
- ✅ Total: ~$10-25/month vs $50+ for full VPS hosting

### **Performance Optimized:**
- ✅ Main app on optimized shared hosting
- ✅ Heavy processing isolated on VPS
- ✅ Better resource allocation
- ✅ Scalable embroidery processing

### **Maintenance Simplified:**
- ✅ Less server management overhead
- ✅ Namecheap handles shared hosting updates
- ✅ Only maintain VPS for API functionality

## 🚀 **Implementation Plan**

### **Part 1: Shared Hosting Setup (Main Laravel App)**

#### **1.1 Namecheap Shared Hosting Features:**
- ✅ **cPanel** included
- ✅ **PHP 8.2** support
- ✅ **MySQL/PostgreSQL** databases
- ✅ **SSL certificates** (Let's Encrypt)
- ✅ **Email hosting** included
- ✅ **File Manager** for easy uploads

#### **1.2 Deploy Main Laravel Application:**
```bash
# On your local machine, prepare the app
composer install --optimize-autoloader --no-dev
php artisan config:clear
php artisan cache:clear

# Create deployment ZIP (exclude embroidery converter files)
zip -r laravel-app.zip . -x \
  "*.git*" \
  "node_modules/*" \
  ".env" \
  "vendor/*" \
  "app/Http/Controllers/Api/EmbroideryConverterController.php" \
  "resources/views/static/services/format-converter.blade.php"
```

#### **1.3 Upload via cPanel:**
1. **Login to shared hosting cPanel**
2. **File Manager** → **public_html**
3. **Upload** `laravel-app.zip`
4. **Extract** and configure

### **Part 2: VPS API Server Setup (Embroidery Converter)**

#### **2.1 Minimal VPS Setup:**
```bash
# Update system
dnf update -y
dnf install -y epel-release

# Install minimal requirements
dnf install -y nginx php8.2-fpm php8.2-cli php8.2-json \
  build-essential cmake git curl

# Install libembroidery
cd /tmp
git clone https://github.com/Embroidermodder/libembroidery.git
cd libembroidery
mkdir build && cd build
cmake .. && make && make install
ldconfig
```

#### **2.2 Create Standalone API:**
```php
<?php
// /var/www/embroidery-api/index.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://yourdomain.com');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($request) {
    case '/convert':
        if ($method === 'POST') {
            handleConversion();
        }
        break;
    case '/download':
        if ($method === 'GET') {
            handleDownload();
        }
        break;
    case '/status':
        if ($method === 'GET') {
            echo json_encode(['status' => 'API is running', 'timestamp' => time()]);
        }
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}

function handleConversion() {
    if (!isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded']);
        return;
    }

    $uploadedFile = $_FILES['file'];
    $outputFormat = $_POST['output_format'] ?? 'dst';
    
    // Validate file
    if ($uploadedFile['size'] > 50 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'File too large']);
        return;
    }

    // Generate unique filename
    $conversionId = uniqid();
    $inputPath = "/tmp/embroidery_input_{$conversionId}." . pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
    $outputPath = "/tmp/embroidery_output_{$conversionId}.{$outputFormat}";

    // Move uploaded file
    if (!move_uploaded_file($uploadedFile['tmp_name'], $inputPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save uploaded file']);
        return;
    }

    // Convert using libembroidery
    $command = "libembroidery-convert " . escapeshellarg($inputPath) . " " . escapeshellarg($outputPath);
    $output = shell_exec($command . " 2>&1");

    if (!file_exists($outputPath)) {
        unlink($inputPath);
        http_response_code(500);
        echo json_encode(['error' => 'Conversion failed', 'details' => $output]);
        return;
    }

    // Clean up input file
    unlink($inputPath);

    // Return success response
    echo json_encode([
        'success' => true,
        'conversion_id' => $conversionId,
        'output_format' => $outputFormat,
        'download_url' => "/download?id={$conversionId}&format={$outputFormat}",
        'file_size' => filesize($outputPath)
    ]);
}

function handleDownload() {
    $conversionId = $_GET['id'] ?? '';
    $format = $_GET['format'] ?? 'dst';
    
    $filePath = "/tmp/embroidery_output_{$conversionId}.{$format}";
    
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo json_encode(['error' => 'File not found']);
        return;
    }

    // Send file
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="converted.' . $format . '"');
    header('Content-Length: ' . filesize($filePath));
    
    readfile($filePath);
    
    // Clean up file after download
    unlink($filePath);
}
?>
```

#### **2.3 Nginx Configuration for API:**
```nginx
# /etc/nginx/sites-available/embroidery-api
server {
    listen 80;
    server_name api.yourdomain.com;
    root /var/www/embroidery-api;
    index index.php;

    # CORS headers
    add_header Access-Control-Allow-Origin "https://yourdomain.com" always;
    add_header Access-Control-Allow-Methods "GET, POST, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Content-Type, Authorization" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Large file uploads for embroidery files
    client_max_body_size 50M;
}
```

### **Part 3: Integration Between Shared Hosting and VPS API**

#### **3.1 Update Laravel App (Shared Hosting)**

Create an API service class:
```php
<?php
// app/Services/EmbroideryApiService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbroideryApiService
{
    private $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = config('services.embroidery_api.url', 'https://api.yourdomain.com');
    }

    public function convertFile($file, $outputFormat)
    {
        try {
            $response = Http::attach('file', file_get_contents($file), $file->getClientOriginalName())
                ->post($this->apiBaseUrl . '/convert', [
                    'output_format' => $outputFormat
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Embroidery API conversion failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return ['error' => 'Conversion service unavailable'];
        } catch (\Exception $e) {
            Log::error('Embroidery API connection failed', [
                'error' => $e->getMessage()
            ]);

            return ['error' => 'Unable to connect to conversion service'];
        }
    }

    public function getDownloadUrl($conversionId, $format)
    {
        return $this->apiBaseUrl . '/download?id=' . $conversionId . '&format=' . $format;
    }

    public function checkApiStatus()
    {
        try {
            $response = Http::get($this->apiBaseUrl . '/status');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

#### **3.2 Update Format Converter Controller:**
```php
<?php
// app/Http/Controllers/FormatConverterController.php
namespace App\Http\Controllers;

use App\Services\EmbroideryApiService;
use Illuminate\Http\Request;

class FormatConverterController extends Controller
{
    private $embroideryApi;

    public function __construct(EmbroideryApiService $embroideryApi)
    {
        $this->embroideryApi = $embroideryApi;
    }

    public function index()
    {
        $apiStatus = $this->embroideryApi->checkApiStatus();
        return view('static.services.format-converter', compact('apiStatus'));
    }

    public function convert(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB
            'output_format' => 'required|in:dst,pes,jef,exp,vp3,xxx,pcs,hus,sew,pec,vip,csd'
        ]);

        $result = $this->embroideryApi->convertFile(
            $request->file('file'),
            $request->output_format
        );

        return response()->json($result);
    }
}
```

#### **3.3 Configuration (Shared Hosting):**
```php
// config/services.php
'embroidery_api' => [
    'url' => env('EMBROIDERY_API_URL', 'https://api.yourdomain.com'),
],
```

```env
# .env on shared hosting
EMBROIDERY_API_URL=https://api.yourdomain.com
```

## 🔧 **Deployment Steps**

### **Step 1: Setup VPS API Server**
```bash
# SSH into your VPS
ssh root@your-vps-ip

# Run the minimal setup
dnf update -y && dnf install -y epel-release nginx php8.2-fpm
# ... install libembroidery and configure API
```

### **Step 2: Deploy to Namecheap Shared Hosting**
1. **Order shared hosting** from Namecheap
2. **Upload Laravel app** via cPanel
3. **Configure database** (MySQL or Supabase)
4. **Set environment variables**
5. **Point API_URL** to your VPS

### **Step 3: Configure DNS**
```
yourdomain.com → Shared hosting IP
api.yourdomain.com → VPS IP
```

## 📊 **Cost Comparison**

| Option | Cost/Month | Pros | Cons |
|--------|------------|------|------|
| **Full VPS** | $50-100 | Full control | Expensive, complex |
| **Shared Only** | $10-20 | Cheap, simple | No libembroidery |
| **Hybrid (Recommended)** | $15-35 | Best of both | Requires API setup |

## 🎯 **Benefits of Hybrid Approach**

### **For Main Application:**
- ✅ **Managed hosting** - No server maintenance
- ✅ **Built-in cPanel** - Easy file management
- ✅ **Included email** - Professional communication
- ✅ **SSL certificates** - Automatic Let's Encrypt
- ✅ **Regular backups** - Hosted by Namecheap

### **For Embroidery Converter:**
- ✅ **Dedicated resources** - No shared hosting limits
- ✅ **Custom software** - libembroidery installation
- ✅ **Scalable processing** - Handle large files
- ✅ **Isolated workload** - Won't affect main app

## 🔒 **Security Considerations**

### **API Security:**
```php
// Add API key authentication
if ($_SERVER['HTTP_X_API_KEY'] !== 'your-secret-api-key') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
```

### **CORS Configuration:**
- Restrict to your domain only
- Use HTTPS for all communications
- Implement rate limiting

## ✅ **Implementation Checklist**

### **VPS API Setup:**
- [ ] Install minimal server requirements
- [ ] Install libembroidery
- [ ] Create standalone PHP API
- [ ] Configure Nginx
- [ ] Test API endpoints
- [ ] Set up SSL certificate

### **Shared Hosting Setup:**
- [ ] Order Namecheap shared hosting
- [ ] Upload Laravel application
- [ ] Configure database (Supabase recommended)
- [ ] Update API service configuration
- [ ] Test integration
- [ ] Configure SSL certificate

This hybrid approach gives you the best of both worlds: a professionally managed main application with powerful embroidery processing capabilities! 🚀

Would you like me to help you implement any specific part of this hybrid architecture?
