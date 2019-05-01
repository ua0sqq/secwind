<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

    $set['title'] = 'Авторы';
    include incDir.'head.php';

    ?>
    Автор: DESURE <br />
    Авторы модификации: FoXxS (http://wap-cat.ru), Tadochi (Patsifist)<br />
    <a href='/admin/'><div class="menu_razd">Админка</div></a>
    <?php
    include incDir.'foot.php';