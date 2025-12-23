# Chat Widget Testing Guide

## Testing the Widget Locally

The chat widget needs to be served over HTTP (not file://) to work properly. Here are several ways to test it:

### Method 1: Using Laravel's Built-in Server (Recommended)
```bash
# From your Laravel project root
php artisan serve

# Then visit: http://localhost:8000/widget/test.html
```

### Method 2: Using Python HTTP Server
```bash
# Navigate to the widget directory
cd public/widget

# Python 3
python -m http.server 8080

# Then visit: http://localhost:8080/test.html
```

### Method 3: Using Node.js HTTP Server
```bash
# Install global http-server if you haven't
npm install -g http-server

# Navigate to the widget directory
cd public/widget

# Start server
http-server -p 8080

# Then visit: http://localhost:8080/test.html
```

### Method 4: Using PHP Built-in Server
```bash
# Navigate to the widget directory
cd public/widget

# Start PHP server
php -S localhost:8080

# Then visit: http://localhost:8080/test.html
```

## Integration Instructions for Clients

### Basic Integration
```html
<script src="https://yourdomain.com/widget/sdk.js"></script>
<script>
  ChatWidget.init({
    apiKey: 'your_api_key_here',
    primaryColor: '#10B981',
    position: 'bottom-right'
  });
</script>
```

### Advanced Configuration
```javascript
ChatWidget.init({
  apiKey: 'your_api_key_here',
  widgetUrl: 'https://yourdomain.com/widget', // Custom widget URL
  apiUrl: 'https://yourdomain.com/api/widget', // Custom API URL  
  primaryColor: '#10B981',
  position: 'bottom-right', // bottom-right, bottom-left, top-right, top-left
  debug: false // Set to true for debugging
});
```

## API Key Generation

Generate API keys for your clients:

```bash
php artisan generate:api-key --name="Client Name" --email="client@example.com"
```

## Troubleshooting

### Widget Not Loading
- Make sure you're accessing via HTTP/HTTPS, not file://
- Check browser console for errors
- Verify API key is correct
- Check that Laravel server is running

### Asset Loading Errors
- Ensure all files exist in `/public/widget/assets/`
- Check file permissions
- Verify server configuration allows access to static assets

### WebSocket Connection Issues  
- Make sure Laravel Reverb is running: `php artisan reverb:start`
- Check broadcasting configuration in Laravel
- Verify WebSocket ports are open

## Files Structure
```
public/widget/
├── sdk.js              # Main SDK script for integration
├── assets/
│   ├── widget.js       # Vue component for the chat widget
│   └── widget.css      # Widget styles
├── test.html           # Test page for development
└── README.md          # This file
```