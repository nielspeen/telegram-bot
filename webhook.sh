#!/bin/bash

# Function to stop ngrok and clear APP_WEBHOOK_URL
stop_ngrok() {
    echo "Stopping ngrok..."
    pkill -f 'ngrok'  # Kill any running ngrok instance
    sed -i '' '/^APP_WEBHOOK_URL/d' .env  # Remove APP_WEBHOOK_URL from .env
    echo "APP_WEBHOOK_URL has been removed from .env"
    exit 0
}

# Check if the first argument is 'stop', and if so, stop ngrok
if [ "$1" == "stop" ]; then
    stop_ngrok
fi

# Check if ngrok is already running by querying the API
NGROK_RUNNING=$(curl --silent http://127.0.0.1:4040/api/tunnels | jq '.tunnels | length')

if [ "$NGROK_RUNNING" -gt 0 ] 2>/dev/null; then
    echo "Ngrok is already running. Retrieving existing URL..."
    NGROK_URL=$(curl --silent http://127.0.0.1:4040/api/tunnels | jq -r '.tunnels[0].public_url')
else
    echo "Starting ngrok via valet share..."
    valet share > /dev/null 2>&1 &
    
    echo "Waiting for ngrok to start..."

    # Loop until ngrok returns a valid URL (polling the ngrok API)
    while true; do
        # Fetch the ngrok URL by querying the ngrok API
        NGROK_URL=$(curl --silent http://127.0.0.1:4040/api/tunnels | jq -r '.tunnels[0].public_url')
        
        # Check if we successfully got the ngrok URL
        if [ -n "$NGROK_URL" ]; then
            echo "Ngrok URL found: $NGROK_URL"
            break
        fi

        # If the URL isn't ready yet, wait a bit and retry
        echo "Waiting for ngrok URL..."
        sleep 2
    done
fi

# Check if APP_WEBHOOK_URL exists in .env, if not, add it
if grep -q "^APP_WEBHOOK_URL=" .env; then
    echo "Updating existing APP_WEBHOOK_URL in .env"
    sed -i '' "s|^APP_WEBHOOK_URL=.*|APP_WEBHOOK_URL=${NGROK_URL}|" .env
else
    echo "Adding APP_WEBHOOK_URL to .env"
    echo "APP_WEBHOOK_URL=${NGROK_URL}" >> .env
fi

# Confirm update
echo ".env file updated with APP_WEBHOOK_URL=${NGROK_URL}"

