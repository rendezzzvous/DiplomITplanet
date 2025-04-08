<section class="top">
<div class="container">
	<div class="top__inner">
		<div class="top__image">
		</div>
		<div class="top__content">
			<div class="top__date">
				 <? $APPLICATION->IncludeFile("/include/top-date.php") ?>
			</div>
			<div class="h1 top__header">
				 <? $APPLICATION->IncludeFile("/include/top-header.php") ?>
			</div>
			<div class="top__text">
				 <? $APPLICATION->IncludeFile("/include/top-text.php") ?>
			</div>
 <button class="btn" data-modal="register">УЧАСТВОВАТЬ</button>
			<div class="top__info">
				 <? $APPLICATION->IncludeFile("/include/top-info.php") ?>
			</div>
		</div>
	</div>
</div>
 </section> <section class="part">
<div class="container">
	<div class="h1 part__header">
		 как участвовать
	</div>
	<div class="part__row">
		<div class="part__item">
			 <? $APPLICATION->IncludeFile("/include/part-1.php") ?>
		</div>
		<div class="part__item">
			 <? $APPLICATION->IncludeFile("/include/part-2.php") ?>
		</div>
		<div class="part__item">
			 <? $APPLICATION->IncludeFile("/include/part-3.php") ?>
		</div>
	</div>
	<div class="part__buttons">
 <button class="btn" data-modal="register">ЗАРЕГИСТРИРОВАТЬ ЧЕК</button>
	</div>
</div>
 </section>
<div class="middle-wrapper">
 <section class="prize">
	<div class="container">
		<div class="h1 prize__header">
			 Подарки
		</div>
		<div class="prize__wrapper">
			<div class="prize__row">
				<div class="prize__col">
					<div class="prize__item-wrapper">
						<div class="prize__item">
 <img src="/local/templates/lebo/images/prize2.png" class="prize__item-image">
							<p class="prize__item-name">
								 НАБОР <br>
								 КОФЕ LEBO
							</p>
						</div>
					</div>
				</div>
				<div class="prize__col">
					<div class="prize__item-wrapper">
						<div class="prize__item">
 <img width="160" src="/local/templates/lebo/images/AppleAirPods.png" height="160" class="prize__item-image">
							<div class="prize__item-name">
								 Наушники <br>
								 Apple Air Pods 4
							</div>
						</div>
					</div>
				</div>
				<div class="prize__col">
					<div class="prize__item-wrapper">
						<div class="prize__item">
 <img width="160" src="/local/templates/lebo/images/ApllewatchSE2024.png" height="160" class="prize__item-image">
							<div class="prize__item-name">
								 Часы <br>
								 Apple Watch SE
							</div>
						</div>
					</div>
				</div>
				<div class="prize__col">
					<div class="prize__item-wrapper">
						<div class="prize__item">
 <img width="160" src="/local/templates/lebo/images/Appleiphone16pro.png" height="160" class="prize__item-image">
							<div class="prize__item-name">
								 Смартфон <br>
								 Apple iPhone 16 Pro
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
 </section> <section class="register" id="sec-register">
	<div class="container">
		<div class="register__inner">
			 <? $APPLICATION->IncludeFile("/include/register-check.php", ['scanner_id' => 'scanner-1']) ?>
		</div>
	</div>
 </section> <section class="users" id="sec-users">
	<div class="container">
		<div class="h1 users__header">
			 УЖЕ участвуют в акции
		</div>
		 <!--<div class="users__search">
                        <input class="form-control" type="text" name="s1" placeholder="Поиск по номеру телефона">
                        <input class="form-control" type="text" name="s2" placeholder="Поиск по email">
                    </div>-->
		<div class="users__table-wrapper">
			<div class="users__table-inner">
				 <?$APPLICATION->IncludeComponent(
	"bav:users",
	".default",
Array()
);?>
			</div>
		</div>
	</div>
 </section>
</div>
 <br>
