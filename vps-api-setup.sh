#!/bin/bash

# Minimal VPS Setup for Embroidery API Server
# Run this on your VPS to create a standalone embroidery conversion API

set -e

echo "🚀 Setting up VPS as Embroidery API Server..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root"
    exit 1
fi

# Update system
print_status "Updating system packages..."
dnf update -y
dnf install -y epel-release

# Install minimal requirements
print_status "Installing web server and PHP..."
dnf install -y nginx php-fpm php-cli php-json \
    build-essential cmake git curl unzip

# Install libembroidery
print_status "Installing libembroidery..."
cd /tmp
rm -rf libembroidery
git clone https://github.com/Embroidermodder/libembroidery.git
cd libembroidery
mkdir build && cd build
cmake ..
make
make install
echo "/usr/local/lib" > /etc/ld.so.conf.d/libembroidery.conf
ldconfig

# Verify libembroidery
if command -v libembroidery-convert &> /dev/null; then
    print_status "Libembroidery installed successfully"
else
    print_error "Libembroidery installation failed"
    exit 1
fi

# Create API directory
print_status "Creating API application..."
mkdir -p /var/www/embroidery-api
chown nginx:nginx /var/www/embroidery-api

# Create the API file
cat > /var/www/embroidery-api/index.php << 'EOF'
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Configure this properly for production
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple API key authentication (configure this!)
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey !== 'your-secret-api-key-change-this') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($request) {
    case '/convert':
        if ($method === 'POST') {
            handleConversion();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
    case '/download':
        if ($method === 'GET') {
            handleDownload();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
    case '/status':
        if ($method === 'GET') {
            echo json_encode([
                'status' => 'API is running',
                'timestamp' => time(),
                'libembroidery' => command_exists('libembroidery-convert')
            ]);
        }
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}

function command_exists($command) {
    $return = shell_exec("which $command");
    return !empty($return);
}

function handleConversion() {
    try {
        if (!isset($_FILES['file'])) {
            throw new Exception('No file uploaded');
        }

        $uploadedFile = $_FILES['file'];
        $outputFormat = $_POST['output_format'] ?? 'dst';
        
        // Validate file size (50MB limit)
        if ($uploadedFile['size'] > 50 * 1024 * 1024) {
            throw new Exception('File too large (max 50MB)');
        }

        // Validate output format
        $allowedFormats = ['dst', 'pes', 'jef', 'exp', 'vp3', 'xxx', 'pcs', 'hus', 'sew', 'pec', 'vip', 'csd'];
        if (!in_array($outputFormat, $allowedFormats)) {
            throw new Exception('Unsupported output format');
        }

        // Generate unique filename
        $conversionId = uniqid('emb_', true);
        $inputExt = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
        $inputPath = "/tmp/embroidery_input_{$conversionId}.{$inputExt}";
        $outputPath = "/tmp/embroidery_output_{$conversionId}.{$outputFormat}";

        // Move uploaded file
        if (!move_uploaded_file($uploadedFile['tmp_name'], $inputPath)) {
            throw new Exception('Failed to save uploaded file');
        }

        // Convert using libembroidery
        $command = "libembroidery-convert " . escapeshellarg($inputPath) . " " . escapeshellarg($outputPath) . " 2>&1";
        $output = shell_exec($command);

        if (!file_exists($outputPath)) {
            unlink($inputPath);
            throw new Exception('Conversion failed: ' . $output);
        }

        // Clean up input file
        unlink($inputPath);

        // Return success response
        echo json_encode([
            'success' => true,
            'conversion_id' => $conversionId,
            'output_format' => $outputFormat,
            'download_url' => "/download?id={$conversionId}&format={$outputFormat}",
            'file_size' => filesize($outputPath),
            'original_filename' => $uploadedFile['name']
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleDownload() {
    try {
        $conversionId = $_GET['id'] ?? '';
        $format = $_GET['format'] ?? 'dst';
        
        if (empty($conversionId)) {
            throw new Exception('Missing conversion ID');
        }

        $filePath = "/tmp/embroidery_output_{$conversionId}.{$format}";
        
        if (!file_exists($filePath)) {
            throw new Exception('File not found or expired');
        }

        // Send file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="converted.' . $format . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        
        // Clean up file after download
        unlink($filePath);

    } catch (Exception $e) {
        http_response_code(404);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
EOF

# Set permissions
chown nginx:nginx /var/www/embroidery-api/index.php
chmod 644 /var/www/embroidery-api/index.php

# Configure Nginx
print_status "Configuring Nginx..."
cat > /etc/nginx/sites-available/embroidery-api << 'EOF'
server {
    listen 80;
    server_name _;
    root /var/www/embroidery-api;
    index index.php;

    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm/www.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Large file uploads for embroidery files
    client_max_body_size 50M;

    # Hide sensitive files
    location ~ /\. {
        deny all;
    }
}
EOF

# Enable site
ln -sf /etc/nginx/sites-available/embroidery-api /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Configure PHP-FPM
print_status "Configuring PHP-FPM..."
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 50M/' /etc/php.ini
sed -i 's/post_max_size = .*/post_max_size = 50M/' /etc/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php.ini
sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php.ini

# Start services
print_status "Starting services..."
systemctl enable nginx php-fpm
systemctl start nginx php-fpm

# Configure firewall
print_status "Configuring firewall..."
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --reload

# Create cleanup script
print_status "Creating cleanup script..."
cat > /usr/local/bin/embroidery-cleanup << 'EOF'
#!/bin/bash
# Clean up old temporary files (older than 24 hours)
find /tmp -name "embroidery_*" -type f -mtime +1 -delete
EOF

chmod +x /usr/local/bin/embroidery-cleanup

# Add to crontab
echo "0 2 * * * /usr/local/bin/embroidery-cleanup" | crontab -

print_status "Installation completed!"
echo ""
echo "🎉 Your Embroidery API Server is ready!"
echo ""
echo "📋 API Endpoints:"
echo "   Status:   http://your-vps-ip/status"
echo "   Convert:  POST http://your-vps-ip/convert"
echo "   Download: GET http://your-vps-ip/download?id=xxx&format=xxx"
echo ""
echo "🔑 API Authentication:"
echo "   Header: X-API-Key: your-secret-api-key-change-this"
echo ""
print_warning "⚠️  IMPORTANT SECURITY STEPS:"
echo "1. Change the API key in /var/www/embroidery-api/index.php"
echo "2. Configure CORS origins for your domain"
echo "3. Set up SSL certificate (Let's Encrypt recommended)"
echo "4. Configure proper domain name instead of IP"
echo ""
echo "🧪 Test the API:"
echo "curl -H 'X-API-Key: your-secret-api-key-change-this' http://your-vps-ip/status"
echo ""
echo "✅ Ready to integrate with your Laravel app on shared hosting!"
EOF
