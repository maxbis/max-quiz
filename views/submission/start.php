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
                background: linear-gradient(to left, rgba(255, 255, 255, 1) 40%, rgba(255, 255, 255, 0) 100%), url(<?=Url::to('@web/img/classroom.webp')?>);
                background-size: cover;
                background-position: center;
                height: 100vh; /* Full height of the viewport */
            }

            .background-image::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.3); /* White background with 50% opacity */
            }

            .form-container {
                position: relative;
                z-index: 2; /* Ensures the form is above the background overlay */
                margin-top: 0px; /* Adjust as needed */
            }
        </style>
    </head>

    <body>
        <div class="background-image">
            <div class="container h-100">
                <div class="row h-100">
                    <!-- Form on the right -->
                    <div class="col-md-6 offset-md-6 d-flex align-items-center justify-content-center">
                        <div class="form-container">
                            <h2 class="mb-5">Start Quiz</h2>
                            <form action="<?=Url::to(['submission/start']) ?>" method="POST">
                                <input type="hidden" name="<?= $csrfTokenName ?>" value="<?= $csrfToken ?>">
                                <div class="form-group">
                                    <label for="voornaam">Voornaam</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Voer voornaam in" required>
                                </div>
                                <div class="form-group">
                                    <label for="achternaam">Achternaam</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Voer achternaam in" required>
                                </div>
                                <div class="form-group">
                                    <label for="klas">Klas</label>
                                    <input type="text" class="form-control" id="class" name="class" placeholder="Voer klas in" required>
                                </div>
                                <div class="form-group">
                                    <label for="wachtwoord">Wachtwoord</label>
                                    <input type="text" class="form-control" id="password" name="password" placeholder="wachtwoord" required>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Start</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    </body>
</html>
