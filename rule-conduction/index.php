<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Правила Акции ");
?>
    <section class="page">
        <div class="container">
            <div class="text-center">
                <a href="/" class="btn page__nav">на главную</a>

                <h1 class="page__header"><? $APPLICATION->ShowTitle(false); ?></h1>
            </div>
            <div class="content">
                <? $APPLICATION->IncludeFile("/include/rule-conduction.php") ?>
            </div>
        </div>
    </section>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>