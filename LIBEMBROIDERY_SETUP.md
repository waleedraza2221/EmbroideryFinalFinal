# Libembroidery Installation Guide

This document provides instructions for installing libembroidery on different operating systems to enable embroidery format conversion functionality.

## What is Libembroidery?

Libembroidery is a cross-platform library for reading, writing, and converting embroidery files. It supports a wide range of embroidery machine formats including DST, PES, JEF, EXP, VP3, and many others.

## Installation Instructions

### Windows

#### Option 1: Download Pre-compiled Binary
1. Download the latest Windows release from: https://github.com/Embroidermodder/libembroidery/releases
2. Extract the files to `C:\Program Files\libembroidery\`
3. Add the installation directory to your system PATH
4. Verify installation by running `libembroidery-convert --help` in Command Prompt

#### Option 2: Build from Source
1. Install Visual Studio with C++ development tools
2. Install Git: https://git-scm.com/download/win
3. Clone the repository:
   ```bash
   git clone https://github.com/Embroidermodder/libembroidery.git
   cd libembroidery
   ```
4. Build using CMake:
   ```bash
   mkdir build
   cd build
   cmake ..
   cmake --build . --config Release
   ```
5. Copy the executable to a directory in your PATH

### Linux (Ubuntu/Debian)

#### Install Dependencies
```bash
sudo apt update
sudo apt install build-essential cmake git
```

#### Build from Source
```bash
git clone https://github.com/Embroidermodder/libembroidery.git
cd libembroidery
mkdir build
cd build
cmake ..
make
sudo make install
```

#### Verify Installation
```bash
libembroidery-convert --help
```

### macOS

#### Using Homebrew
```bash
brew install cmake
git clone https://github.com/Embroidermodder/libembroidery.git
cd libembroidery
mkdir build
cd build
cmake ..
make
sudo make install
```

## Usage in Laravel Application

Once libembroidery is installed, the Laravel application will automatically detect it and use it for format conversions. The application checks these locations:

- `/usr/local/bin/libembroidery-convert`
- `/usr/bin/libembroidery-convert`
- `C:\Program Files\libembroidery\libembroidery-convert.exe`
- Any location in your system PATH

## Supported Formats

The following embroidery formats are supported:

### Machine Formats
- **DST** - Tajima embroidery format
- **PES** - Brother embroidery format
- **JEF** - Janome embroidery format
- **EXP** - Melco embroidery format
- **VP3** - Husqvarna Viking embroidery format
- **XXX** - Singer embroidery format

### Industrial Formats
- **PCS** - Pfaff embroidery format
- **HUS** - Husqvarna embroidery format
- **SEW** - Janome embroidery format
- **PEC** - Brother embroidery format
- **VIP** - Pfaff embroidery format
- **CSD** - Singer embroidery format

## Testing Installation

To test if libembroidery is working correctly with your Laravel application:

1. Visit `/services/format-converter` in your application
2. Upload a sample embroidery file (DST, PES, etc.)
3. Select a different output format
4. Click "Convert"
5. If successful, you should be able to download the converted file

## Troubleshooting

### Common Issues

1. **"Libembroidery not found" error**
   - Ensure libembroidery is installed and in your PATH
   - Check file permissions
   - Verify the executable name is correct

2. **Conversion fails silently**
   - Check Laravel logs in `storage/logs/laravel.log`
   - Ensure input file is a valid embroidery format
   - Check file permissions in the storage directory

3. **Permission denied errors**
   - Ensure the web server has read/write access to `storage/app/temp/embroidery/`
   - Check that libembroidery executable has proper permissions

### Log Files

Check the following for debugging:
- Laravel logs: `storage/logs/laravel.log`
- Web server error logs
- System logs for process execution errors

## Performance Considerations

1. **File Size Limits**: The application supports files up to 50MB
2. **Temporary Files**: Converted files are automatically cleaned up after 24 hours
3. **Concurrent Conversions**: The application can handle multiple simultaneous conversions

## Security Notes

1. All uploaded files are stored temporarily and automatically deleted
2. File type validation prevents upload of non-embroidery files
3. Unique file naming prevents conflicts between users
4. Files are processed in an isolated temporary directory

For more information about libembroidery, visit: https://github.com/Embroidermodder/libembroidery
