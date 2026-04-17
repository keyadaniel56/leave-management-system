#!/bin/bash

set -e

echo "================================================"
echo "   Leave Management System - Setup Script"
echo "================================================"

# Install PHP dependencies
echo ""
echo "[1/6] Installing PHP dependencies..."
composer install --no-interaction --prefer-dist

# Copy .env if it doesn't exist
echo ""
echo "[2/6] Setting up environment file..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "  .env file created from .env.example"
else
    echo "  .env already exists, skipping."
fi

# Generate app key
echo ""
echo "[3/6] Generating application key..."
php artisan key:generate

# Run migrations
echo ""
echo "[4/6] Running database migrations..."
php artisan migrate --force

# Seed the database
echo ""
echo "[5/6] Seeding database (admin user + leave types)..."
php artisan db:seed --force

# Install and build frontend assets
echo ""
echo "[6/6] Building frontend assets..."
npm install --silent
npm run build

echo ""
echo "================================================"
echo "  Setup complete. Starting development server..."
echo "================================================"
echo ""
echo "  Admin login:"
echo "    Email:    admin@leave.com"
echo "    Password: password"
echo ""
echo "  API base URL: http://127.0.0.1:8000/api"
echo ""

# Start the server
php artisan serve
