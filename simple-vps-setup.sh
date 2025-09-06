#!/bin/bash

# Simplified VPS Setup Script for 162.0.236.226
# AlmaLinux 8 cPanel - Alternative approach using pre-built tools

set -e

echo "🚀 Setting up Embroidery API Server on 162.0.236.226 (Simplified)..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
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

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root"
    exit 1
fi

print_info "Setting up minimal API server..."

# Update system
print_status "Updating system packages..."
dnf update -y
dnf install -y epel-release

# Install web server and PHP
print_status "Installing web server and PHP..."
dnf install -y nginx php-fpm php-cli php-json python3 python3-pip curl unzip

# Install Python embroidery library as alternative
print_status "Installing Python embroidery library..."
pip3 install pyembroidery

# Verify Python embroidery
python3 -c "import pyembroidery; print('PyEmbroidery installed successfully')" || {
    print_error "PyEmbroidery installation failed"
    exit 1
}

print_status "PyEmbroidery installed successfully"

# Create API directory
print_status "Creating API application..."
mkdir -p /var/www/embroidery-api
chown nginx:nginx /var/www/embroidery-api

# Create Python conversion script
print_status "Creating Python converter..."
cat > /var/www/embroidery-api/convert.py << 'EOF'
#!/usr/bin/env python3
import sys
import pyembroidery
import os
import argparse

def convert_file(input_file, output_file):
    try:
        # Read the input file
        pattern = pyembroidery.read(input_file)
        
        # Write to output format
        pyembroidery.write(pattern, output_file)
        
        print(f"Successfully converted {input_file} to {output_file}")
        return True
    except Exception as e:
        print(f"Conversion failed: {str(e)}", file=sys.stderr)
        return False

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Convert embroidery files')
    parser.add_argument('input', help='Input file path')
    parser.add_argument('output', help='Output file path')
    
    args = parser.parse_args()
    
    if convert_file(args.input, args.output):
        sys.exit(0)
    else:
        sys.exit(1)
EOF

chmod +x /var/www/embroidery-api/convert.py

# Create the API file with Python backend
print_status "Creating API with Python backend..."
cat > /var/www/embroidery-api/index.php << 'EOF'
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// API Authentication
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey !== '9097332919794dea83dd2de22191ec913a1b8f44') {
    http_response_code(401);
    echo json_encode([
        'error' => 'Unauthorized',
        'message' => 'Invalid API key',
        'server_ip' => '162.0.236.226'
    ]);
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
            $pyembroidery_check = shell_exec('python3 -c "import pyembroidery; print(\'OK\')" 2>/dev/null');
            echo json_encode([
                'status' => 'API is running',
                'server_ip' => '162.0.236.226',
                'timestamp' => time(),
                'pyembroidery' => trim($pyembroidery_check) === 'OK',
                'php_version' => PHP_VERSION,
                'api_version' => '1.0.0',
                'converter' => 'PyEmbroidery'
            ]);
        }
        break;
    default:
        http_response_code(404);
        echo json_encode([
            'error' => 'Endpoint not found',
            'available_endpoints' => ['/status', '/convert', '/download']
        ]);
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
        if (!in_array(strtolower($outputFormat), $allowedFormats)) {
            throw new Exception('Unsupported output format: ' . $outputFormat);
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

        // Convert using Python script
        $converterScript = '/var/www/embroidery-api/convert.py';
        $command = "timeout 300 python3 " . escapeshellarg($converterScript) . " " . 
                   escapeshellarg($inputPath) . " " . escapeshellarg($outputPath) . " 2>&1";
        
        $output = shell_exec($command);
        $exitCode = 0;
        
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
            'download_url' => "http://162.0.236.226/download?id={$conversionId}&format={$outputFormat}",
            'file_size' => filesize($outputPath),
            'original_filename' => $uploadedFile['name'],
            'server_ip' => '162.0.236.226',
            'processed_at' => date('Y-m-d H:i:s'),
            'converter' => 'PyEmbroidery'
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'error' => $e->getMessage(),
            'server_ip' => '162.0.236.226'
        ]);
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
        header('Content-Disposition: attachment; filename="converted_' . date('Y-m-d_H-i-s') . '.' . $format . '"');
        header('Content-Length: ' . filesize($filePath));
        header('X-Server-IP: 162.0.236.226');
        
        readfile($filePath);
        
        // Clean up file after download
        unlink($filePath);

    } catch (Exception $e) {
        http_response_code(404);
        echo json_encode([
            'error' => $e->getMessage(),
            'server_ip' => '162.0.236.226'
        ]);
    }
}
?>
EOF

# Set permissions
chown nginx:nginx /var/www/embroidery-api/index.php
chmod 644 /var/www/embroidery-api/index.php

# Configure Nginx
print_status "Configuring Nginx..."
cat > /etc/nginx/conf.d/embroidery-api.conf << 'EOF'
server {
    listen 80 default_server;
    server_name 162.0.236.226 _;
    root /var/www/embroidery-api;
    index index.php;

    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Server-IP "162.0.236.226";

    # Logging
    access_log /var/log/nginx/embroidery-api-access.log;
    error_log /var/log/nginx/embroidery-api-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm/www.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Large file uploads
    client_max_body_size 50M;

    # Hide sensitive files
    location ~ /\. {
        deny all;
    }
}
EOF

# Remove default nginx config
rm -f /etc/nginx/conf.d/default.conf

# Configure PHP-FPM
print_status "Configuring PHP..."
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 50M/' /etc/php.ini
sed -i 's/post_max_size = .*/post_max_size = 50M/' /etc/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php.ini
sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php.ini

# Start services
print_status "Starting services..."
systemctl enable nginx php-fpm
systemctl restart nginx php-fpm

# Configure firewall
print_status "Configuring firewall..."
firewall-cmd --permanent --add-service=http
firewall-cmd --reload

# Create cleanup script
cat > /usr/local/bin/embroidery-cleanup << 'EOF'
#!/bin/bash
find /tmp -name "embroidery_*" -type f -mtime +1 -delete 2>/dev/null || true
echo "$(date): Cleaned up old embroidery files" >> /var/log/embroidery-cleanup.log
EOF

chmod +x /usr/local/bin/embroidery-cleanup
(crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/embroidery-cleanup") | crontab -

# Test the installation
print_status "Testing API..."
sleep 3

API_TEST=$(curl -s -H "X-API-Key: 9097332919794dea83dd2de22191ec913a1b8f44" http://localhost/status 2>/dev/null || echo "failed")

if [[ "$API_TEST" == *"API is running"* ]]; then
    print_status "API test successful!"
    echo "API Response: $API_TEST"
else
    print_warning "API test failed. Response: $API_TEST"
fi

print_status "Installation completed!"
echo ""
echo "🎉 Embroidery API Server Ready on 162.0.236.226!"
echo ""
echo "📋 Configuration:"
echo "   Server:       162.0.236.226"
echo "   API Key:      9097332919794dea83dd2de22191ec913a1b8f44"
echo "   Converter:    PyEmbroidery (Python-based)"
echo "   Status URL:   http://162.0.236.226/status"
echo ""
echo "🧪 Test Command:"
echo "   curl -H 'X-API-Key: 9097332919794dea83dd2de22191ec913a1b8f44' http://162.0.236.226/status"
echo ""
echo "✅ Ready for integration with your Laravel app!"
