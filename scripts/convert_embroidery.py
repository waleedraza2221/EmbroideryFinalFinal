#!/usr/bin/env python3
"""
Embroidery Format Converter using PyEmbroidery
Designed for shared hosting environments
"""

import sys
import os
import argparse
import logging
from pathlib import Path

try:
    import pyembroidery
except ImportError:
    print("Error: pyembroidery not installed. Install with: pip3 install pyembroidery", file=sys.stderr)
    sys.exit(1)

def setup_logging():
    """Setup logging for the conversion process"""
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s - %(levelname)s - %(message)s',
        handlers=[
            logging.StreamHandler(sys.stdout)
        ]
    )

def validate_format(format_name):
    """Validate if the format is supported by PyEmbroidery"""
    supported_formats = [
        'dst', 'pes', 'jef', 'exp', 'vp3', 'xxx', 'pcs', 
        'hus', 'sew', 'pec', 'vip', 'csd', 'u01'
    ]
    return format_name.lower() in supported_formats

def convert_embroidery_file(input_path, output_path):
    """
    Convert embroidery file from input format to output format
    
    Args:
        input_path (str): Path to input embroidery file
        output_path (str): Path where converted file should be saved
        
    Returns:
        bool: True if conversion successful, False otherwise
    """
    try:
        # Validate input file exists
        if not os.path.exists(input_path):
            logging.error(f"Input file does not exist: {input_path}")
            return False
            
        # Get file extensions
        input_ext = Path(input_path).suffix.lower().lstrip('.')
        output_ext = Path(output_path).suffix.lower().lstrip('.')
        
        # Validate formats
        if not validate_format(input_ext):
            logging.error(f"Unsupported input format: {input_ext}")
            return False
            
        if not validate_format(output_ext):
            logging.error(f"Unsupported output format: {output_ext}")
            return False
            
        logging.info(f"Converting {input_ext.upper()} to {output_ext.upper()}")
        logging.info(f"Input: {input_path}")
        logging.info(f"Output: {output_path}")
        
        # Read the embroidery pattern
        logging.info("Reading input file...")
        pattern = pyembroidery.read(input_path)
        
        if pattern is None:
            logging.error("Failed to read input file - file may be corrupted or invalid")
            return False
            
        # Get pattern info
        stitch_count = len(pattern.stitches) if hasattr(pattern, 'stitches') else 0
        logging.info(f"Pattern loaded successfully - {stitch_count} stitches")
        
        # Ensure output directory exists
        output_dir = os.path.dirname(output_path)
        if output_dir and not os.path.exists(output_dir):
            os.makedirs(output_dir, exist_ok=True)
            
        # Write the pattern in the new format
        logging.info("Converting and writing output file...")
        pyembroidery.write(pattern, output_path)
        
        # Verify output file was created
        if not os.path.exists(output_path):
            logging.error("Output file was not created")
            return False
            
        output_size = os.path.getsize(output_path)
        logging.info(f"Conversion completed successfully - Output size: {output_size} bytes")
        
        return True
        
    except Exception as e:
        logging.error(f"Conversion failed with error: {str(e)}")
        return False

def main():
    """Main function to handle command line arguments and perform conversion"""
    parser = argparse.ArgumentParser(
        description='Convert embroidery files between different formats using PyEmbroidery'
    )
    parser.add_argument('input', help='Input embroidery file path')
    parser.add_argument('output', help='Output embroidery file path')
    parser.add_argument('-v', '--verbose', action='store_true', help='Enable verbose logging')
    
    args = parser.parse_args()
    
    # Setup logging
    setup_logging()
    
    if args.verbose:
        logging.getLogger().setLevel(logging.DEBUG)
        
    # Perform conversion
    success = convert_embroidery_file(args.input, args.output)
    
    if success:
        print("SUCCESS: Conversion completed")
        sys.exit(0)
    else:
        print("ERROR: Conversion failed", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    main()
