<?php

use yii\helpers\Url;
use yii\helpers\Html;

function getRatingCategory($rating)
{
  if ($rating >= 90 && $rating <= 100) {
    return "Outstanding";
  } elseif ($rating >= 80 && $rating < 90) {
    return "Excellent";
  } elseif ($rating >= 70 && $rating < 80) {
    return "Very good";
  } elseif ($rating >= 55 && $rating < 60) {
    return "Average";
  } elseif ($rating >= 50 && $rating < 55) {
    return "below average";
  } elseif ($rating >= 45 && $rating < 50) {
    return "below average";
  } else {
    return "poor";
  }
}

if ($submission['no_questions']) {
  $score = round($submission['no_correct'] * 100 / $submission['no_questions'], 0);
  $rating = round(($submission['no_correct'] - $submission['no_questions'] * 0.2) * 10 / ($submission['no_questions'] * 0.8), 2);
  $ratingString = getRatingCategory(round($rating * 10));
} else {
  $score = 0;
  $rating = 0;
  $ratingString = "";
}


date_default_timezone_set('Europe/London');
$today = date("j F Y");

$start = new DateTime($submission['start_time']);
$end = new DateTime($submission['end_time']);
$diff = $start->diff($end);
$formattedTime = sprintf("%02d:%02d", $diff->i, $diff->s);

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
      margin: 20px;
      ;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #f7f7f7;
    }

    .certificate-container {
      display: block;
      position: relative;
      text-align: center;
      margin-top: 20px;
      margin-bottom: 80px;
    }

    .certificate-image {
      width: 90%;
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

    .score {
      position: absolute;
      top: 70%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 30px;
      font-family: 'Dancing Script';
      color: #000;
    }

    .signature-l {
      padding: 5px;
      min-width: 180px;
      position: absolute;
      text-align: left;
      bottom: 6%;
      left: 10%;
      font-size: 18px;
      font-family: Arial, sans-serif;
      color: #707070;
    }

    .signature-r {
      border-top: 1px solid #707070;
      padding: 5px;
      min-width: 180px;
      text-align: right;
      position: absolute;
      bottom: 6%;
      left: 72%;
      font-size: 12px;
      font-family: Arial, sans-serif;
      color: #707070;
    }

    .delayed-content {
      opacity: 0;
      /* Start with zero opacity to hide the content */
      transition: opacity 3s ease-in-out;
    }

    @media (max-width: 1000px) {
      .main-block {
        margin: 0;
      }

      .name {
        font-size: 30px;
      }

      .score {
        font-size: 24px;
      }

      .signature-l,
      .signature-r {

        font-size: 12px;
        bottom: 6%;
      }
      .signature-r {
        left:auto;
        right: 10%;
      }
    }

    @media (max-width: 600px) {
      .name {
        font-size: 20px;
      }

      .score {
        font-size: 18px;
      }

      .signature-l,
      .signature-r {
        font-size: 10px;
      }
      .signature-r {
        border-top: 0px;
        left: 2%;
      }
    }
    @media (max-width: 400px) {
      .signature-l,
      .signature-r {
        font-size: 8px;
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
      margin: 10px;
      min-width: 80px;

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
  document.addEventListener("DOMContentLoaded", function () {
    // Function to apply the fade-in effect with a specified transition duration and delay
    function applyFadeInEffect(element, duration, delay) {
      setTimeout(function () {
        element.style.transition = `opacity ${duration}s ease-in-out`;
        element.style.opacity = "1";
      }, delay);
    }

    const delayedElements = document.querySelectorAll(".delayed-content");
    delayedElements.forEach(function (element) {
      const delay = parseFloat(element.getAttribute("data-delay")); // Read the data-delay attribute
      applyFadeInEffect(element, 1, delay); // Apply the fade-in effect with a default duration of 0.5 seconds
    });
  });
</script>

<body>
  <div class="main-block">
    <div class="certificate-container dynamic-size">
      <img src="<?= Url::to('@web/img/certificate01.jpg') ?>" alt="Certificate of Appreciation"
        class="delayed-content certificate-image" data-delay="0">
      <div class="delayed-content name" data-delay="400">
        <?= $submission['first_name'] . " " . $submission['last_name'] ?>
      </div>
      <div class="delayed-content score delayed-content" data-delay="1600">Score
        <b><span class="delayed-content" data-delay="2400">
            <?= $score ?>%<span></b>
      </div>
      <div class="delayed-content signature-l" data-delay="800">
        <?= $today ?>
      </div>
      <div class="delayed-content signature-r" data-delay="1200">
        <?= $submission['quiz_name'] ?> (
        <?= $submission['no_correct'] ?>/
        <?= $submission['no_questions'] ?> in
        <?= $formattedTime ?>)
        <br>
        <?= $ratingString ?>(
        <?= $rating ?>)
      </div>

    </div>
  </div>

  <?php if ($submission['quiz_review']) { ?>
    <div class="centered delayed-content" data-delay="2600">
      <?= Html::a('Results', ['/site/results', 'token' => $submission['token']], ['class' => 'myButton']); ?>
    </div>
  <?php } ?>

  <div class="centered delayed-content" data-delay="2600">
    <?= Html::a('Clear', ['/submission/create', 'token' => $submission['token']], ['class' => 'myButton', 'title' => 'Start new quiz']); ?>
  </div>

</body>

</html>