<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
        }

        .coming-soon {
            display: flex;
            height: 100vh;
        }

        .left-section {
            /* flex: 1; */
            background: rgba(0, 0, 0, 0.8);
            color: #fff;
            display: flex;
        }

        .left-content {
            width: 70%;
            margin: auto;
            text-align: center;
        }

        .left-section h1 {
            font-size: 4.5rem;
            font-weight: bold;
        }

        .left-section p {
            margin: 20px 0;
            font-size: 1rem;
            color: #ccc;
        }

        .notify-form {
            max-width: 400px;
            width: 100%;
        }

        .notify-form input {
            border-radius: 0.375rem 0 0 0.375rem;
        }

        .notify-form button {
            border-radius: 0 0.375rem 0.375rem 0;
            background-color: #134e4a;
            color: #fff;
            border: none;
        }

        .right-section {
            flex: 1;
            background: url("{{ asset('assets/images/coming-soon-bg.jpg') }}") center center/cover no-repeat;
            position: relative;
            overflow: hidden;

        }

        .right-section::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #03ffee99;
            mix-blend-mode: multiply;
            z-index: 1;
        }

        .social-icons {
            position: absolute;
            top: 50%;
            right: 30px;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 20px;
            z-index: 2;
            /* make sure icons stay above overlay */
        }

        .social-icons a {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            background: #134e4a;
            transform: scale(1.1);
        }
    </style>
</head>

<body>
    <div class="coming-soon">
        <!-- Left Section -->
        <div class="left-section">
            <div class="left-content">
                <h1>Our Website is <br> Coming Soon</h1>
                <p>We're working hard to finish the development of this site. Sign up below to receive updates and to
                    benotified when we launch!</p>
            </div>
        </div>

        <!-- Right Section -->
        <div class="right-section">
            <div class="social-icons">
                <a href="javascript:void(0)"><i class="fab fa-twitter"></i></a>
                <a href="javascript:void(0)"><i class="fab fa-facebook-f"></i></a>
                <a href="javascript:void(0)"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
