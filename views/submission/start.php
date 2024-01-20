<?php

use yii\helpers\Html;
use yii\helpers\Url;

$csrfTokenName = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->getCsrfToken();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form with Background Image</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .background-image {
            position: relative;
            background: linear-gradient(to left, rgba(255, 255, 255, 1) 40%, rgba(255, 255, 255, 0) 100%), url(<?= Url::to('@web/img/classroom.webp') ?>);
            background-size: cover;
            background-position: center;
            height: 100vh;
            /* Full height of the viewport */
        }

        .background-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.3);
            /* White background with 50% opacity */
        }

        .form-container {
            position: relative;
            z-index: 2;
            /* Ensures the form is above the background overlay */
            margin-top: 0px;
            /* Adjust as needed */
        }

        .spinner-cursor {
            cursor: progress;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2;
            cursor: progress;
            display: none;
        }
    </style>

</head>

<body>
    <div id="overlay" class="overlay"></div>

    <div class="background-image">
        <div class="container h-100">
            <div class="row h-100">
                <!-- Form on the right -->
                <div class="col-md-6 offset-md-6 d-flex align-items-center justify-content-center">
                    <div class="form-container">
                        <h2 class="mb-5">Start Quiz</h2>
                        <form action="<?= Url::to(['/submission/start']) ?>" id="form" method="POST">
                            <input type="hidden" name="<?= $csrfTokenName ?>" value="<?= $csrfToken ?>">
                            <input type="hidden" name="answer_order" value="">
                            <div class="form-group">
                                <label for="voornaam">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                    placeholder="Fist Name" required>
                            </div>
                            <div class="form-group">
                                <label for="achternaam">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                    placeholder="Last Name" required>
                            </div>
                            <div class="form-group">
                                <label for="klas">Class</label>
                                <input type="text" class="form-control" id="class" name="class" placeholder="Class"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="wachtwoord">Quiz Code</label>
                                <input type="text" class="form-control" id="password" name="password"
                                    placeholder="Quiz Code" required>
                            </div>
                            <button type="button" id="submitButton" class="btn btn-primary mt-3">Start</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script>
        document.getElementById('submitButton').addEventListener('click', function () {
            this.disabled = true;
            this.innerText = 'Starting...';
            document.getElementById('overlay').style.display = 'block';
            document.body.classList.add('spinner-cursor');
            // document.getElementById('form').submit();
            setTimeout(function () {
                document.getElementById('form').submit();
            }, 500);
        });
    </script>

</body>

</html>