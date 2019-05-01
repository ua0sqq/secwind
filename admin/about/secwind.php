<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

    $set['title'] = 'Что такое SecWind?';
    include incDir.'head.php';

    ?>
    <div class="menu_razd">SecWind - это инновационный wap движок. Автор Tadochi (Patsifist)</div>
    <div class="post">SecWind быстрый, удобный инновационный wap движок. В отличии от других cms, SecWind позволяет вам не влезая в код, ftp, и phpmyadmin устанавливать, удалять модули. <br />
	А также SecWind предоставляет удобную админку, включающие такие разделы как: Безопасность, Модули, MySQL менеджер, с кучей утилит внутри</div>
    <div class="post">SecWind некоммерческий проект. Мы были бы рады любой финансовой поддержке. <br />WebMoney R368331730977</div>
	<?='<a href="http://'.Core::SecWind('support').'">Сайт поддержки</a>'?>
    <a href='/admin/'><div class="menu_razd">Админка</div></a>
    <?php
    include incDir.'foot.php';