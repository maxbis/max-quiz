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

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }

        .modal-dialog {
            position: fixed;
            top: 35%;
            left: 40%;
            background: #fff;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 0 auto;
            margin-bottom: 10px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>

</head>

<body>
    <!-- This is the busy overlay, show when Startig quiz... -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-dialog">
            <div class="loader"></div>
            <p>Please wait... </p>
        </div>
    </div>

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
                                    placeholder="First Name"  maxlength="30" required>
                            </div>
                            <div class="form-group">
                                <label for="achternaam">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                    placeholder="Last Name"  maxlength="30" required>
                            </div>
                            <div class="form-group">
                                <label for="klas">Class</label>
                                <input type="text" class="form-control" id="class" name="class" placeholder="Class"
                                    maxlength="3" required>
                            </div>
                            <div class="form-group">
                                <label for="wachtwoord">Quiz Code</label>
                                <input type="text" class="form-control" id="password" name="password"
                                    placeholder="Quiz Code"  maxlength="30" required>
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
            document.getElementById('modalOverlay').style.display = 'block';
            // document.body.classList.add('spinner-cursor');
            // document.getElementById('form').submit();
            setTimeout(function () {
                document.getElementById('form').submit();
            }, 800);
        });
    </script>

</body>

</html>