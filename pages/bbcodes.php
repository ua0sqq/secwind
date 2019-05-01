<?php
	include '../engine/includes/start.php';

	$set['title'] = 'Бб-коды';

	include incDir . 'head.php';

	?>
	    Курсивный текст: [i]Текст[/i]<br />Результат: <em>Текст</em><br /><br />

        Подчеркнутый текст: [u]Текст[/u]<br />Результат: <span style="text-decoration:underline">Текст</span><br /><br />

		Жирный текст: [b]Текст[/b]<br />Результат: <b>Текст</b><br /><br />

        Зачеркнутый текст: [del]Текст[/del]<br />Результат: <span style="text-decoration:line-through">Текст</span><br /><br />

        Цитата: [quote]Текст[/quote]<br />Результат: <div class="quote">Текст</div><br />

        Цвет текста: [color=цвет]Текст[/color]<br />Результат: <span style="color:red">Текст</span><br /><br />

        Ссылка: [url=адрес]Текст[/url]<br />Результат: <a href="/" title="Текст">Текст</a><br /><br />
		<a href="smiles.php" class="link">Смайлы</a>
	<?php
	include incDir . 'foot.php';