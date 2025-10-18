<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        /* Prevent scrollbar jump when content changes */
        html {
            overflow-y: scroll !important; /* Always show vertical scrollbar */
        }
        
        /* Force scrollbar to always be visible */
        body {
            overflow-y: scroll !important;
        }
    </style>
</head>

<body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>

    <header id="header">
        <?php
        if ( $this->title!='Error' ) {
            NavBar::begin([
                'options' => [
                    'class' => 'navbar-expand-md navbar-light fixed-top', 
                    'style' => 'background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'
                ]
            ]);
            
            // Add custom logo
            echo '<a class="navbar-brand mq-logo" href="' . Yii::$app->homeUrl . '">
                <div class="mq-logo-icon"></div>
                <span class="mq-logo-text">Quiz</span>
            </a>';
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav'],
                'items' => [
                    ['label' => 'Quizzes', 'url' => ['/quiz']],
                    // ['label' => 'Questions', 'url' => ['/question?show=-1']],
                    ['label' => 'Questions', 'url' => ['/question/index-raw']],
                    ['label' => 'Student View', 'url' => ['/submission/create']],
                    // ['label' => 'Progress', 'url' => ['/submission']],
                    Yii::$app->user->isGuest
                        ? ['label' => 'Login', 'url' => ['/site/login']]
                        : '<li class="nav-item">'
                        . Html::beginForm(['/site/logout'])
                        . Html::submitButton(
                            'Logout (' . Yii::$app->user->identity->username . ')',
                            ['class' => 'nav-link btn btn-link logout', 'style' => 'color:grey;']
                        )
                        . Html::endForm()
                        . '</li>'
                ]
            ]);
            NavBar::end();
        }
        ?>
    </header>

    <main id="main" class="flex-shrink-0" role="main" style="margin-top: 20px;">
        <div class="container">
            <?php if (!empty($this->params['breadcrumbs'])) : ?>
                <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
            <?php endif ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>

    <footer id="footer" class="mt-auto py-3 bg-light">
        <div class="container">
            <div class="row text-muted">
                <div class="col-md-6 text-center text-md-start">&copy; ROCvA <?= date('Y') ?></div>
                <div class="col-md-6 text-center text-md-end"><?= Yii::powered() ?></div>
            </div>
        </div>
    </footer>

    <script>
    // Auto-highlight current page in navigation
    $(document).ready(function() {
        var currentPath = window.location.pathname;
        
        $('.navbar-nav .nav-link').each(function() {
            var linkPath = $(this).attr('href');
            if (linkPath && currentPath.includes(linkPath.replace(/^\//, ''))) {
                $(this).addClass('active');
            }
        });
        
        // Special handling for specific routes
        if (currentPath.includes('/question/index-raw')) {
            $('.navbar-nav .nav-link[href*="question"]').addClass('active');
        }
        if (currentPath.includes('/quiz')) {
            $('.navbar-nav .nav-link[href*="quiz"]').addClass('active');
        }
        if (currentPath.includes('/submission/create')) {
            $('.navbar-nav .nav-link[href*="submission/create"]').addClass('active');
        }
    });
    </script>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>