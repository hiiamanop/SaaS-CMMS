#!/bin/bash

set -e

echo "========================================"
echo "B3 Stock Integrity — Docker Test Suite"
echo "========================================"
echo ""

# Check docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker not found. Please install Docker."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose not found. Please install Docker Compose."
    exit 1
fi

echo "✓ Docker & Docker Compose found"
echo ""

# Cleanup old containers/volumes
echo "🧹 Cleaning up old containers..."
docker-compose -f docker-compose.test.yml down -v 2>/dev/null || true

echo ""
echo "🏗️  Building & Starting containers..."
docker-compose -f docker-compose.test.yml up --build

echo ""
echo "✓ Test complete. Check output above for results."
echo ""
echo "Cleanup:"
echo "  docker-compose -f docker-compose.test.yml down -v"
echo ""
