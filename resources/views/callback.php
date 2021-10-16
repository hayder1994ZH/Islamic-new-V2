<html>
<head>
  <meta charset="utf-8">
  <title>Callback</title>
  <script>
    window.opener.postMessage({ token: "{{ $token }}", name: "{{ $name }}" }, "YOUR DOMAIN");
    window.close();
  </script>
</head>
<body>
</body>
</html>