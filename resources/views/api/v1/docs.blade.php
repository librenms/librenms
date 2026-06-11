<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LibreNMS API v1 — Reference</title>
    <link rel="stylesheet" href="{{ $cdnUrl }}/swagger-ui.css">
    <style>
        body { margin: 0; }
    </style>
</head>
<body>
<div id="swagger-ui"></div>
<script src="{{ $cdnUrl }}/swagger-ui-bundle.js" crossorigin></script>
<script src="{{ $cdnUrl }}/swagger-ui-standalone-preset.js" crossorigin></script>
<script>
    window.addEventListener('load', () => {
        window.ui = SwaggerUIBundle({
            url: @json($specUrl),
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset,
            ],
            layout: 'BaseLayout',
        });
    });
</script>
</body>
</html>
