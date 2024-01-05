<?php

use yii\helpers\Url;
use yii\helpers\Html;

$score = round($submission['no_correct'] * 100 / $submission['no_questions'], 0);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificate of Appreciation</title>
  <style>
    body {
      background-color: #f7f7f7;
    }

    .main-block {
      height: 100%;
      margin: 20px;;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #f7f7f7;
    }

    .certificate-container {
      display: block;
      position: relative;
      text-align: center;
      margin-top: 80px;
      margin-bottom: 80px;
    }

    .certificate-image {
      width: 100%;
      height: auto;
    }

    .name {
      position: absolute;
      top: 60%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: #000;
      font-family: 'Dancing Script', cursive;
      font-size: 60px;
    }

    .date {
      position: absolute;
      bottom: 13%;
      left: 11%;
      font-size: 18px;
      font-family: Arial, sans-serif;
      color: #707070;
    }

    .score {
      position: absolute;
      top: 70%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 30px;
      font-family: 'Dancing Script';
      color: #000;
    }

    .signature {
      position: absolute;
      bottom: 13%;
      left: 73%;
      font-size: 18px;
      font-family: Arial, sans-serif;
      color: #707070;
    }

    .delayed-content {
      opacity: 0;
      /* Start with zero opacity to hide the content */
      transition: opacity 3s ease-in-out;
    }

    @media (max-width: 800px) {
      .main-block {
        margin: 0;
      }

      .name {
        font-size: 30px;
      }

      .score {
        font-size: 24px;
      }

      .signature,
      .date {
        font-size: 12px;
      }
    }

    @media (max-width: 400px) {
      .name {
        font-size: 20px;
      }

      .score {
        font-size: 18px;
      }

      .signature,
      .date {
        font-size: 10px;
      }
    }
  </style>


  <style>
    .myButton {
      display: inline-block;
      outline: none;
      cursor: pointer;
      padding: 0 16px;
      background-color: #fff;
      border-radius: 0.25rem;
      border: 1px solid #dddbda;
      color: #0070d2;
      font-size: 13px;
      line-height: 30px;
      font-weight: 400;
      text-align: center;
      text-decoration: none;
      font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
    }

    .myButton:hover {
      background-color: lightblue;
      color: black;
    }

    .centered {
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #f7f7f7;
    }
  </style>

</head>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Function to apply the fade-in effect with a specified transition duration and delay
    function applyFadeInEffect(element, duration, delay) {
      setTimeout(function() {
        element.style.transition = `opacity ${duration}s ease-in-out`;
        element.style.opacity = "1";
      }, delay);
    }

    const delayedElements = document.querySelectorAll(".delayed-content");
    delayedElements.forEach(function(element) {
      const delay = parseFloat(element.getAttribute("data-delay")); // Read the data-delay attribute
      applyFadeInEffect(element, 1, delay); // Apply the fade-in effect with a default duration of 0.5 seconds
    });
  });
</script>

<body>
  <div class="main-block">
    <div class="certificate-container dynamic-size">
      <img src="<?= Url::to('@web/img/certificate.jpg') ?>" alt="Certificate of Appreciation" class="delayed-content certificate-image" data-delay="0">
      <div class="delayed-content name" data-delay="400"><?= $submission['first_name'] . " " . $submission['last_name'] ?></div>
      <div class="delayed-content score delayed-content" data-delay="1600">Score
        <b><span class="delayed-content" data-delay="2400"><?= $score ?>%<span></b>
      </div>
      <div class="delayed-content date" data-delay="800">28 december 2023</div>
      <div class="delayed-content signature" data-delay="1200">PHP L1 (score: <?= $submission['no_correct'] ?>/<?= $submission['no_questions'] ?>)</div>
    </div>
  </div>
  <?php if ($submission['quiz_review']) { ?>
    <div class="centered delayed-content" data-delay="2600">
      <?= Html::a('Results', ['/site/results', 'token' => $submission['token']], ['class' => 'myButton']); ?>
    </div>
  <?php } ?>
</body>

</html>