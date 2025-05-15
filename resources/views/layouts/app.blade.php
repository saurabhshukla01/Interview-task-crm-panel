<!DOCTYPE html>
<html>
<head>
    <title>My Laravel App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

    <div class="container mt-4">
        @yield('content')
    </div>

    <!-- ✅ Load jQuery FIRST -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ✅ Then load custom scripts -->
    @yield('scripts')
</body>
</html>
