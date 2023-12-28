<?php
use yii\helpers\Url;

$score = round( $submission['no_correct']*100/$submission['no_questions'], 0);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Certificate of Appreciation</title>
<style>
  body, html {
    height: 100%;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f7f7f7;
  }
  .certificate-container {
    position: relative;
    text-align: center;
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
</style>
</head>
<body>
<div class="certificate-container">
  <img src="<?=Url::to('@web/img/certificate.jpg')?>" alt="Certificate of Appreciation" class="certificate-image">
  <div class="name"><?=$submission['first_name']." ".$submission['last_name'] ?></div>
  <div class="score">Score <b><?=$score?>%</b></div>
  <div class="date">28 december 2023</div>
  <div class="signature">PHP L1 (score: <?=$submission['no_correct']?>/<?=$submission['no_questions']?>)</div>
</div>
</body>
</html>
