#!/bin/bash

echo "=================================="
echo "Testing Split Architecture Locally"
echo "=================================="
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}Step 1: Starting Backend API (Port 8001)${NC}"
echo "Starting backend server at http://localhost:8001"
cd /Users/hopegainlimited/Downloads/buypower-backend-api
php artisan serve --port=8001 &
BACKEND_PID=$!
echo "Backend PID: $BACKEND_PID"
sleep 3

echo ""
echo -e "${BLUE}Step 2: Testing Backend API${NC}"
sleep 2
BACKEND_HEALTH=$(curl -s http://localhost:8001/health)
if [[ $BACKEND_HEALTH == *"healthy"* ]]; then
    echo -e "${GREEN}✓ Backend API is running${NC}"
    echo "Response: $BACKEND_HEALTH"
else
    echo -e "${RED}✗ Backend API failed to start${NC}"
    kill $BACKEND_PID 2>/dev/null
    exit 1
fi

echo ""
echo -e "${BLUE}Step 3: Starting Frontend (Port 8000)${NC}"
echo "Starting frontend server at http://localhost:8000"
cd /Users/hopegainlimited/Downloads/buypower
php artisan serve --port=8000 &
FRONTEND_PID=$!
echo "Frontend PID: $FRONTEND_PID"
sleep 3

echo ""
echo -e "${BLUE}Step 4: Testing Frontend${NC}"
FRONTEND_HEALTH=$(curl -s http://localhost:8000)
if [[ $? -eq 0 ]]; then
    echo -e "${GREEN}✓ Frontend is running${NC}"
else
    echo -e "${RED}✗ Frontend failed to start${NC}"
    kill $BACKEND_PID $FRONTEND_PID 2>/dev/null
    exit 1
fi

echo ""
echo -e "${GREEN}=================================="
echo "✓ Both servers are running!"
echo "==================================${NC}"
echo ""
echo "Access points:"
echo -e "  Frontend: ${BLUE}http://localhost:8000${NC}"
echo -e "  Backend API: ${BLUE}http://localhost:8001${NC}"
echo -e "  Backend Health: ${BLUE}http://localhost:8001/health${NC}"
echo -e "  Backend API Health: ${BLUE}http://localhost:8001/api/health${NC}"
echo ""
echo -e "${YELLOW}Testing Instructions:${NC}"
echo "1. Open http://localhost:8000 in your browser"
echo "2. Log in to the application"
echo "3. Try uploading a CSV or accessing bulk operations"
echo "4. Check browser console for any API errors"
echo "5. Verify requests go to http://localhost:8001/api/*"
echo ""
echo -e "${YELLOW}To stop servers:${NC}"
echo "  Press Ctrl+C or run: kill $BACKEND_PID $FRONTEND_PID"
echo ""
echo "Logs will appear below..."
echo "=================================="
echo ""

# Keep script running and show that servers are active
trap "echo '';echo 'Stopping servers...'; kill $BACKEND_PID $FRONTEND_PID 2>/dev/null; exit" INT TERM

# Keep script alive
wait
