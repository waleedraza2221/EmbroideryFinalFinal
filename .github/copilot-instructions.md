<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

# Laravel Customer Management System - Copilot Instructions

This is a Laravel 12 application with the following characteristics:

## Project Structure
- **Framework**: Laravel 12 with PHP 8.2+
- **Database**: PostgreSQL via Supabase
- **Frontend**: Blade templates with Tailwind CSS
- **Authentication**: Laravel's built-in authentication

## Key Features
- User registration and login system
- Admin panel for customer management
- Profile editing functionality
- Full form validation
- Supabase database integration

## Code Standards
- Follow Laravel conventions and best practices
- Use proper form validation with Request classes
- Implement secure authentication patterns
- Use Blade templating with component structure
- Apply Tailwind CSS for styling

## Database Schema
- Users table includes: id, name, email, phone, password, timestamps
- Phone numbers must be unique and validated
- All forms require CSRF protection

## Validation Requirements
- Name: required, 2-255 characters
- Email: required, valid format, unique
- Phone: required, unique, valid format
- Password: required, minimum 8 characters, confirmed

## File Organization
- Controllers in appropriate subdirectories (Auth/, Admin/)
- Form requests for validation logic
- Blade views organized by feature
- Routes grouped by functionality and protection level

When suggesting code changes or new features, ensure they align with these patterns and maintain consistency with the existing codebase structure.
