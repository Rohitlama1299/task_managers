<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        /* Set the body to use flexbox, with the content taking up all space and the footer at the bottom */
        html, body {
            height: 100%; /* Ensure full viewport height */
            margin: 0;
            display: flex;
            flex-direction: column; /* Arrange the content vertically */
        }

        /* Content container will grow to take up remaining space */
        .content {
            flex: 1;
        }

        /* Footer style */
        .footer {
            background-color: #f8f9fa;
            padding: 1rem 0;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="content">
        
    </div>

    <footer class="footer bg-light py-3 mt-4">
        <div class="container text-center">
            <span>&copy; <?= date('Y') ?> Task Manager. All rights reserved.</span>
        </div>
    </footer>

</body>
</html>
