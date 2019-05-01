<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

    $set['title'] = 'Соглашение';
    include incDir.'head.php';

    echo file_get_contents(H . 'engine/files/data/agreement.txt');

    ?>
    <a href='/admin/'><div class="menu_razd">Админка</div></a>
    <?php
    include incDir.'foot.php';