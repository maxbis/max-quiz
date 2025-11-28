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
    <title>Start Quiz</title>
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

        input:not(:placeholder-shown):invalid {
            border-bottom: 2px solid #e3342f;
        }

        input:valid {
            border-bottom: 2px solid #38c172;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        #submitButton {
            visibility: hidden;
        }

        form:valid #submitButton {
            visibility: visible;
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
                <div class="col-md-6 offset-md-5 d-flex align-items-center justify-content-center">
                    <div class="form-container" style="color:darkblue;">
                        <h2 class="mb-4 col-sm-10">Start Quiz</h2>
                        <form action="<?= Url::to(['/submission/start']) ?>" id="form" method="POST">
                            <input type="hidden" name="<?= $csrfTokenName ?>" value="<?= $csrfToken ?>">
                            <input type="hidden" name="answer_order" value="">

                            <div class="form-group col-sm-10">
                                <label for="voornaam">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                    placeholder="First Name" minlength="2" maxlength="30" required pattern=".{2,30}"
                                    title="Please enter between 2 and 30 characters.">
                            </div>
                            <div class="form-group col-sm-10">
                                <label for="achternaam">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                    placeholder="Last Name" minlength="2" maxlength="30" required>
                            </div>

                            <div class="form-group col-sm-10">
                                <label for="student_nr">Student Number</label>
                                <input type="text" class="form-control" id="student_nr" name="student_nr"
                                    placeholder="Student Number" minlength="4" maxlength="8" required
                                    pattern="[0-9]+" 
                                    inputmode="numeric"
                                    title="Please enter only numbers (no letters allowed)">
                            </div>
                            <div class="form-group col-sm-10">
                                <label for="klas">Class</label>
                                <input type="text" class="form-control" id="class" name="class" placeholder="Class"
                                    minlength="1" maxlength="3" required>
                            </div>


                            <div class="form-group col-sm-10 ">
                                <label for="wachtwoord">Quiz Code</label>
                                <input type="text" class="form-control text-uppercase" id="password" name="password"
                                    placeholder="Quiz Code" minlength="3" maxlength="30" required>
                            </div>

                            <button type="button" id="submitButton"
                                class="btn btn-primary ml-3 mt-3 px-4">Start</button>
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
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        // When the page loads, fill the form with cookie data if it exists.
        document.addEventListener('DOMContentLoaded', () => {
            const studentInfoCookie = getCookie('studentInfo');
            if (studentInfoCookie) {
                try {
                    const studentInfo = JSON.parse(studentInfoCookie);
                    const fields = {
                        'first_name': 'firstName',
                        'last_name': 'lastName',
                        'student_nr': 'studentNr',
                        'class': 'class'
                    };

                    for (const id in fields) {
                        const element = document.getElementById(id);
                        if (element && studentInfo[fields[id]]) {
                            element.value = studentInfo[fields[id]];
                        }
                    }
                } catch (e) {
                    console.error('Error parsing studentInfo cookie:', e);
                    setCookie('studentInfo', '', -1); // Clear corrupted cookie
                }
            }
        });

        // Validate student number to only allow numbers
        document.getElementById('student_nr').addEventListener('input', function(e) {
            // Remove any non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        document.getElementById('submitButton').addEventListener('click', function() {
            // Validate student number before submission
            const studentNrInput = document.getElementById('student_nr');
            const studentNr = studentNrInput.value.trim();
            
            // Check if student number contains only numbers
            if (!/^[0-9]+$/.test(studentNr)) {
                alert('Student Number must contain only numbers (no letters allowed)');
                studentNrInput.focus();
                return false;
            }

            // Store form data in a cookie for 4 months
            const studentInfo = {
                firstName: document.getElementById('first_name').value,
                lastName: document.getElementById('last_name').value,
                studentNr: studentNr,
                class: document.getElementById('class').value,
            };
            setCookie('studentInfo', JSON.stringify(studentInfo), 120);

            // Continue with form submission
            this.disabled = true;
            this.innerText = 'Starting...';
            document.getElementById('modalOverlay').style.display = 'block';
            setTimeout(function() {
                document.getElementById('form').submit();
            }, 800);
        });
    </script>

</body>

</html>